<?php

namespace App\Http\Controllers;

use Barryvdh\DomPDF\Facade\Pdf;
use App\Http\Requests\StoreMissionRequest;
use App\Http\Requests\UpdateMissionRequest;
use App\Http\Requests\UpdateMissionWorkRequest;
use App\Mail\MissionAssignedMail;
use App\Models\Affectation;
use App\Models\Mission;
use App\Models\ReferencePoint;
use App\Models\Technicien;
use App\Services\AuditLogService;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Gate;
use Illuminate\Support\Facades\Mail;

class MissionController extends Controller
{
    public function __construct(private AuditLogService $auditLog)
    {
    }
    public function analysisExportPdf(Request $request)
    {
        $user = $request->user();

        if ($user->role !== 'Technicien') {
            abort(403);
        }

        $technicienId = Technicien::query()
            ->where('user_id', $user->id)
            ->value('id');

        if (!$technicienId) {
            abort(404, 'Profil technicien introuvable.');
        }

        $validated = $request->validate([
            'period' => ['nullable', 'in:7d,30d,90d,all,custom'],
            'start_date' => ['nullable', 'required_if:period,custom', 'date'],
            'end_date' => ['nullable', 'required_if:period,custom', 'date', 'after_or_equal:start_date'],
        ]);

        $selectedPeriod = $validated['period'] ?? '30d';
        $rangeStart = null;
        $rangeEnd = null;

        if ($selectedPeriod === 'custom') {
            $rangeStart = Carbon::parse($validated['start_date'])->startOfDay();
            $rangeEnd = Carbon::parse($validated['end_date'])->endOfDay();
        } elseif ($selectedPeriod === '7d') {
            $rangeStart = now()->subDays(6)->startOfDay();
            $rangeEnd = now()->endOfDay();
        } elseif ($selectedPeriod === '30d') {
            $rangeStart = now()->subDays(29)->startOfDay();
            $rangeEnd = now()->endOfDay();
        } elseif ($selectedPeriod === '90d') {
            $rangeStart = now()->subDays(89)->startOfDay();
            $rangeEnd = now()->endOfDay();
        }

        $scopedMissionsQuery = Mission::query()
            ->whereHas('affectations', function ($q) use ($technicienId) {
                $q->where('technicien_id', $technicienId);
            });

        if ($rangeStart && $rangeEnd) {
            $scopedMissionsQuery->whereBetween('created_at', [$rangeStart, $rangeEnd]);
        }

        $missions = (clone $scopedMissionsQuery)
            ->get(['id', 'statut', 'priorite', 'due_at', 'started_at', 'completed_at', 'created_at']);

        $total = $missions->count();
        $completed = $missions->where('statut', 'Terminée')->count();
        $inProgress = $missions->where('statut', 'En cours')->count();
        $blocked = $missions->where('statut', 'Bloquée')->count();
        $cancelled = $missions->where('statut', 'Annulée')->count();

        $validatedRate = $total > 0 ? round(($completed / $total) * 100, 1) : 0.0;
        $blockedRate = $total > 0 ? round(($blocked / $total) * 100, 1) : 0.0;

        $completedWithDueDate = $missions->filter(function ($mission) {
            return $mission->statut === 'Terminée' && $mission->due_at !== null && $mission->completed_at !== null;
        });

        $onTimeCompleted = $completedWithDueDate->filter(function ($mission) {
            return $mission->completed_at->lte($mission->due_at);
        })->count();

        $onTimeRate = $completedWithDueDate->count() > 0
            ? round(($onTimeCompleted / $completedWithDueDate->count()) * 100, 1)
            : 0.0;

        $avgResolutionHours = round(
            $missions
                ->filter(fn ($mission) => $mission->started_at && $mission->completed_at)
                ->map(fn ($mission) => $mission->started_at->diffInMinutes($mission->completed_at) / 60)
                ->avg() ?? 0,
            1
        );

        $highPriorityTotal = $missions->whereIn('priorite', ['Haute', 'Urgente'])->count();
        $highPriorityCompleted = $missions
            ->whereIn('priorite', ['Haute', 'Urgente'])
            ->where('statut', 'Terminée')
            ->count();

        $highPriorityResolutionRate = $highPriorityTotal > 0
            ? round(($highPriorityCompleted / $highPriorityTotal) * 100, 1)
            : 0.0;

        $currentMonthCompleted = $missions
            ->filter(fn ($mission) => $mission->statut === 'Terminée' && $mission->completed_at && $mission->completed_at->isCurrentMonth())
            ->count();

        $statusLabels = collect(['Terminée', 'En cours', 'Bloquée', 'Annulée', 'Autre']);
        $statusData = collect([
            $completed,
            $inProgress,
            $blocked,
            $cancelled,
            max($total - ($completed + $inProgress + $blocked + $cancelled), 0),
        ]);

        $recentMissions = (clone $scopedMissionsQuery)
            ->with(['referencePoint:id,reference,adresse'])
            ->latest()
            ->limit(12)
            ->get();

        $pdf = Pdf::loadView('missions.analysis-pdf', compact(
            'total',
            'completed',
            'inProgress',
            'blocked',
            'cancelled',
            'validatedRate',
            'blockedRate',
            'onTimeRate',
            'avgResolutionHours',
            'highPriorityResolutionRate',
            'currentMonthCompleted',
            'recentMissions',
            'statusLabels',
            'statusData',
            'selectedPeriod',
            'rangeStart',
            'rangeEnd'
        ))->setPaper('a4', 'portrait');

        return $pdf->download('analyse-travail-' . now()->format('Ymd_His') . '.pdf');
    }

    public function index(Request $request)
    {
        $user = $request->user();

        $referenceId = $request->integer('reference_id');
        $onlyMine = $request->boolean('mine') || $user->role === 'Technicien';

        $query = Mission::query()
            ->with([
                'referencePoint:id,reference,adresse,gouvernorat,delegation',
                'creator:id,name,email',
                'currentAffectation.technicien.user',
            ])
            ->orderByDesc('created_at');

        if ($onlyMine) {
            $technicienId = Technicien::query()
                ->where('user_id', $user->id)
                ->value('id');

            if ($technicienId) {
                $query->whereHas('affectations', function ($q) use ($technicienId) {
                    $q->where('technicien_id', $technicienId);
                });
            } else {
                $query->whereRaw('1 = 0');
            }
        }

        if ($referenceId) {
            $query->where('reference_id', $referenceId);
        }

        $missions = $query->paginate(15)->withQueryString();

        return view('missions.index', compact('missions', 'referenceId', 'onlyMine'));
    }

    public function analysis(Request $request)
    {
        $user = $request->user();

        if ($user->role !== 'Technicien') {
            abort(403);
        }

        $technicienId = Technicien::query()
            ->where('user_id', $user->id)
            ->value('id');

        if (!$technicienId) {
            abort(404, 'Profil technicien introuvable.');
        }

        $validated = $request->validate([
            'period' => ['nullable', 'in:7d,30d,90d,all,custom'],
            'start_date' => ['nullable', 'required_if:period,custom', 'date'],
            'end_date' => ['nullable', 'required_if:period,custom', 'date', 'after_or_equal:start_date'],
        ]);

        $selectedPeriod = $validated['period'] ?? '30d';
        $rangeStart = null;
        $rangeEnd = null;

        if ($selectedPeriod === 'custom') {
            $rangeStart = Carbon::parse($validated['start_date'])->startOfDay();
            $rangeEnd = Carbon::parse($validated['end_date'])->endOfDay();
        } elseif ($selectedPeriod === '7d') {
            $rangeStart = now()->subDays(6)->startOfDay();
            $rangeEnd = now()->endOfDay();
        } elseif ($selectedPeriod === '30d') {
            $rangeStart = now()->subDays(29)->startOfDay();
            $rangeEnd = now()->endOfDay();
        } elseif ($selectedPeriod === '90d') {
            $rangeStart = now()->subDays(89)->startOfDay();
            $rangeEnd = now()->endOfDay();
        }

        $scopedMissionsQuery = Mission::query()
            ->whereHas('affectations', function ($q) use ($technicienId) {
                $q->where('technicien_id', $technicienId);
            });

        if ($rangeStart && $rangeEnd) {
            $scopedMissionsQuery->whereBetween('created_at', [$rangeStart, $rangeEnd]);
        }

        // SQL-optimized aggregations for better performance
        $statusCounts = $scopedMissionsQuery
            ->clone()
            ->selectRaw('statut, COUNT(*) as count')
            ->groupBy('statut')
            ->pluck('count', 'statut');

        $total = $statusCounts->sum();
        $completed = $statusCounts->get('Terminée', 0);
        $inProgress = $statusCounts->get('En cours', 0);
        $blocked = $statusCounts->get('Bloquée', 0);
        $cancelled = $statusCounts->get('Annulée', 0);

        $validatedRate = $total > 0 ? round(($completed / $total) * 100, 1) : 0.0;
        $blockedRate = $total > 0 ? round(($blocked / $total) * 100, 1) : 0.0;

        // On-time completion rate using SQL where clause
        $completedWithDueDate = $scopedMissionsQuery
            ->clone()
            ->where('statut', 'Terminée')
            ->whereNotNull('due_at')
            ->whereNotNull('completed_at')
            ->count();

        $onTimeCompleted = $scopedMissionsQuery
            ->clone()
            ->where('statut', 'Terminée')
            ->whereNotNull('due_at')
            ->whereNotNull('completed_at')
            ->whereColumn('completed_at', '<=', 'due_at')
            ->count();

        $onTimeRate = $completedWithDueDate > 0
            ? round(($onTimeCompleted / $completedWithDueDate) * 100, 1)
            : 0.0;

        // Average resolution time using database-agnostic raw calculation
        $missions = $scopedMissionsQuery
            ->clone()
            ->whereNotNull('started_at')
            ->whereNotNull('completed_at')
            ->select(['started_at', 'completed_at'])
            ->get();

        $totalResolutionMinutes = 0;
        $resolutionCount = 0;
        foreach ($missions as $mission) {
            $totalResolutionMinutes += $mission->started_at->diffInMinutes($mission->completed_at);
            $resolutionCount++;
        }

        $avgResolutionHours = $resolutionCount > 0 
            ? round(($totalResolutionMinutes / $resolutionCount) / 60, 1)
            : 0.0;

        // High priority resolution rate
        $highPriorityTotal = $scopedMissionsQuery
            ->clone()
            ->whereIn('priorite', ['Haute', 'Urgente'])
            ->count();

        $highPriorityCompleted = $scopedMissionsQuery
            ->clone()
            ->whereIn('priorite', ['Haute', 'Urgente'])
            ->where('statut', 'Terminée')
            ->count();

        $highPriorityResolutionRate = $highPriorityTotal > 0
            ? round(($highPriorityCompleted / $highPriorityTotal) * 100, 1)
            : 0.0;

        // Current month completed count
        $currentMonthCompleted = $scopedMissionsQuery
            ->clone()
            ->where('statut', 'Terminée')
            ->whereNotNull('completed_at')
            ->whereMonth('completed_at', now()->month)
            ->whereYear('completed_at', now()->year)
            ->count();

        // Generate month range
        if ($rangeStart && $rangeEnd) {
            $months = collect();
            $cursor = $rangeStart->copy()->startOfMonth();
            $endMonth = $rangeEnd->copy()->startOfMonth();

            while ($cursor->lte($endMonth)) {
                $months->push($cursor->copy());
                $cursor->addMonth();
            }

            if ($months->isEmpty()) {
                $months->push(now()->startOfMonth());
            }

            $months = $months->take(-6)->values();
        } else {
            $months = collect(range(5, 0, -1))
                ->map(function ($offset) {
                    return Carbon::now()->subMonths($offset)->startOfMonth();
                })
                ->push(Carbon::now()->startOfMonth());
        }

        $monthlyLabels = $months->map(fn (Carbon $month) => $month->translatedFormat('M Y'))->values();

        // SQL-optimized monthly aggregations
        $monthlyCompleted = $months->map(function (Carbon $month) use ($scopedMissionsQuery) {
            return $scopedMissionsQuery
                ->clone()
                ->where('statut', 'Terminée')
                ->whereNotNull('completed_at')
                ->whereMonth('completed_at', $month->month)
                ->whereYear('completed_at', $month->year)
                ->count();
        })->values();

        $monthlyAssigned = $months->map(function (Carbon $month) use ($scopedMissionsQuery) {
            return $scopedMissionsQuery
                ->clone()
                ->whereMonth('created_at', $month->month)
                ->whereYear('created_at', $month->year)
                ->count();
        })->values();

        $statusLabels = collect(['Terminée', 'En cours', 'Bloquée', 'Annulée', 'Autre']);
        $statusData = collect([
            $completed,
            $inProgress,
            $blocked,
            $cancelled,
            max($total - ($completed + $inProgress + $blocked + $cancelled), 0),
        ]);

        $recentMissions = (clone $scopedMissionsQuery)
            ->with(['referencePoint:id,reference,adresse'])
            ->latest()
            ->limit(8)
            ->get();

        return view('missions.analysis', compact(
            'total',
            'completed',
            'inProgress',
            'blocked',
            'cancelled',
            'validatedRate',
            'blockedRate',
            'onTimeRate',
            'avgResolutionHours',
            'highPriorityResolutionRate',
            'currentMonthCompleted',
            'recentMissions',
            'monthlyLabels',
            'monthlyCompleted',
            'monthlyAssigned',
            'statusLabels',
            'statusData',
            'selectedPeriod',
            'rangeStart',
            'rangeEnd'
        ));
    }

    public function create(Request $request)
    {
        Gate::authorize('manage-missions');

        $prefillReferenceId = $request->integer('reference_id') ?: null;

        $referencePoints = ReferencePoint::query()
            ->orderBy('reference')
            ->get(['id', 'reference', 'adresse']);

        $techniciens = Technicien::query()
            ->with('user:id,name')
            ->orderBy('nom')
            ->orderBy('prenom')
            ->get();

        $mission = new Mission([
            'reference_id' => $prefillReferenceId,
        ]);

        return view('missions.create', compact('referencePoints', 'techniciens', 'mission', 'prefillReferenceId'));
    }

    public function store(StoreMissionRequest $request)
    {
        Gate::authorize('manage-missions');

        $data = $request->validated();
        $technicienId = $data['technicien_id'] ?? null;
        unset($data['technicien_id']);

        $data['created_by'] = $request->user()->id;

        $mission = Mission::query()->create($data);

        // Log mission creation
        $mission->load('referencePoint');
        $this->auditLog->logMissionCreated(
            $mission->id,
            $mission->referencePoint->reference ?? 'N/A',
            $request->user()
        );

        if ($technicienId) {
            $affectation = Affectation::query()->create([
                'mission_id' => $mission->id,
                'technicien_id' => (int) $technicienId,
                'assigned_by' => $request->user()->id,
                'assigned_at' => now(),
            ]);

            $affectation->load([
                'mission.referencePoint',
                'technicien.user',
                'assignedBy',
            ]);

            // Log mission assignment
            $this->auditLog->logMissionAssigned(
                $mission->id,
                $affectation->technicien->user->name ?? 'Unknown',
                $request->user()
            );

            $technicienEmail = $affectation->technicien->user->email ?? null;
            if ($technicienEmail) {
                Mail::to($technicienEmail)->send(new MissionAssignedMail($affectation));
            }
        }

        return redirect()
            ->route('missions.show', $mission)
            ->with('status', 'Mission créée.');
    }

    public function show(Request $request, Mission $mission)
    {
        Gate::authorize('view-mission', $mission);

        $mission->load([
            'referencePoint',
            'creator:id,name,email',
            'affectations.technicien.user',
        ]);

        $auditLogs = $this->auditLog->getMissionAuditLog($mission->id, 15);

        return view('missions.show', compact('mission', 'auditLogs'));
    }

    public function edit(Request $request, Mission $mission)
    {
        Gate::authorize('manage-missions');

        $mission->load(['referencePoint', 'currentAffectation.technicien.user']);

        $referencePoints = ReferencePoint::query()
            ->orderBy('reference')
            ->get(['id', 'reference', 'adresse']);

        $techniciens = Technicien::query()
            ->with('user:id,name')
            ->orderBy('nom')
            ->orderBy('prenom')
            ->get();

        return view('missions.edit', compact('mission', 'referencePoints', 'techniciens'));
    }

    public function update(UpdateMissionRequest $request, Mission $mission)
    {
        Gate::authorize('manage-missions');

        $oldData = $mission->getAttributes();
        
        $data = $request->validated();
        $technicienId = $data['technicien_id'] ?? null;
        unset($data['technicien_id']);

        $mission->fill($data);
        $mission->save();

        // Log mission update
        $this->auditLog->logMissionUpdated(
            $mission->id,
            $oldData,
            $mission->getAttributes(),
            $request->user()
        );

        if ($technicienId) {
            $mission->load('currentAffectation');
            $currentTechnicienId = $mission->currentAffectation?->technicien_id;

            if ((int) $technicienId !== (int) $currentTechnicienId) {
                $newAffectation = Affectation::query()->create([
                    'mission_id' => $mission->id,
                    'technicien_id' => (int) $technicienId,
                    'assigned_by' => $request->user()->id,
                    'assigned_at' => now(),
                ]);

                // Log new mission assignment
                $newAffectation->load('technicien.user');
                $this->auditLog->logMissionAssigned(
                    $mission->id,
                    $newAffectation->technicien->user->name ?? 'Unknown',
                    $request->user()
                );
            }
        }

        return redirect()
            ->route('missions.show', $mission)
            ->with('status', 'Mission mise à jour.');
    }

    public function destroy(Request $request, Mission $mission)
    {
        Gate::authorize('manage-missions');

        // Log mission deletion before deleting
        $this->auditLog->logMissionDeleted($mission->id, $request->user());

        $mission->delete();

        return redirect()
            ->route('missions.index')
            ->with('status', 'Mission supprimée.');
    }

    public function updateWork(UpdateMissionWorkRequest $request, Mission $mission)
    {
        Gate::authorize('work-mission', $mission);

        $user = $request->user();

        $technicienId = Technicien::query()
            ->where('user_id', $user->id)
            ->value('id');

        if (!$technicienId) {
            abort(403);
        }

        $oldStatus = $mission->statut;
        $data = $request->validated();

        $affectation = Affectation::query()
            ->where('mission_id', $mission->id)
            ->where('technicien_id', $technicienId)
            ->orderByDesc('assigned_at')
            ->first();

        if (!$affectation) {
            abort(403);
        }

        $mission->statut = $data['statut'];

        if ($data['statut'] === 'En cours' && $mission->started_at === null) {
            $mission->started_at = now();
        }

        if ($data['statut'] === 'Terminée') {
            if ($mission->started_at === null) {
                $mission->started_at = now();
            }
            $mission->completed_at = now();
        }

        $mission->save();

        // Log mission status change by technician
        $this->auditLog->logMissionStatusChanged(
            $mission->id,
            $oldStatus,
            $data['statut'],
            $user
        );

        $rapport = $data['rapport'] ?? null;
        if ($rapport !== null) {
            $affectation->rapport = $rapport;
            $affectation->save();
        }

        return redirect()
            ->route('missions.show', $mission)
            ->with('status', 'Mise à jour technicien enregistrée.');
    }
}
