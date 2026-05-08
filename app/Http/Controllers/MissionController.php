<?php

namespace App\Http\Controllers;

use Barryvdh\DomPDF\Facade\Pdf;
use App\Http\Requests\StoreMissionRequest;
use App\Http\Requests\UpdateMissionRequest;
use App\Http\Requests\UpdateMissionWorkRequest;
use App\Mail\MissionAssignedMail;
use App\Models\Affectation;
use App\Models\Mission;
use App\Models\MissionAttachment;
use App\Models\MissionTimeLog;
use App\Models\NotificationPreference;
use App\Models\ReferencePoint;
use App\Models\Technicien;
use App\Services\AuditLogService;
use Carbon\Carbon;
use Illuminate\Support\Collection;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Gate;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Mail;

class MissionController extends Controller
{
    public function __construct(private AuditLogService $auditLog)
    {
    }
    public function analysisExportPdf(Request $request)
    {
        $analysis = $this->buildAnalysisData($request);
        $analysis['recentMissions'] = $analysis['recentMissions']->take(12)->values();

        $pdf = Pdf::loadView('missions.analysis-pdf', $analysis)->setPaper('a4', 'portrait');

        return $pdf->download('analyse-travail-' . now()->format('Ymd_His') . '.pdf');
    }

    public function index(Request $request)
    {
        $user = $request->user();

        $referenceId = $request->integer('reference_id');
        $onlyMine = $request->boolean('mine') || $user->role === 'Technicien';
        $search = trim((string) $request->string('search')->toString());

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

        if ($search !== '') {
            $query->where(function ($q) use ($search) {
                $q->where('type_mission', 'like', '%' . $search . '%')
                    ->orWhere('description', 'like', '%' . $search . '%')
                    ->orWhereHas('referencePoint', function ($refQ) use ($search) {
                        $refQ->where('reference', 'like', '%' . $search . '%')
                            ->orWhere('adresse', 'like', '%' . $search . '%');
                    });
            });
        }

        if ($user->role === 'Technicien') {
            $activeStatus = $request->string('active_status')->toString();
            $activePriority = $request->string('active_priority')->toString();
            $activeScope = $request->string('active_scope')->toString();
            $completedPeriod = $request->string('completed_period')->toString();

            $activeQuery = (clone $query)
                ->where('statut', '!=', 'Terminée')
                ->orderByDesc('due_at')
                ->orderByDesc('created_at');

            if ($activeStatus !== '' && $activeStatus !== 'all') {
                $activeQuery->where('statut', $activeStatus);
            }

            if ($activePriority !== '' && $activePriority !== 'all') {
                $activeQuery->where('priorite', $activePriority);
            }

            if ($activeScope === 'overdue') {
                $activeQuery->whereNotNull('due_at')->where('due_at', '<', now());
            } elseif ($activeScope === 'today') {
                $activeQuery->whereDate('due_at', now()->toDateString());
            } elseif ($activeScope === 'week') {
                $activeQuery->whereBetween('due_at', [now()->startOfWeek(), now()->endOfWeek()]);
            } elseif ($activeScope === 'no_due') {
                $activeQuery->whereNull('due_at');
            }

            $activeMissions = $activeQuery
                ->paginate(10, ['*'], 'active_page')
                ->withQueryString();

            $completedQuery = (clone $query)
                ->where('statut', 'Terminée')
                ->orderByDesc('completed_at')
                ->orderByDesc('created_at');

            if (in_array($completedPeriod, ['7d', '30d', '90d'], true)) {
                $days = (int) rtrim($completedPeriod, 'd');
                $completedQuery->where('completed_at', '>=', now()->subDays($days));
            }

            $completedMissions = $completedQuery
                ->paginate(10, ['*'], 'completed_page')
                ->withQueryString();

            return view('missions.index', compact(
                'activeMissions',
                'completedMissions',
                'referenceId',
                'onlyMine',
                'search',
                'activeStatus',
                'activePriority',
                'activeScope',
                'completedPeriod'
            ));
        }

        $missions = $query->paginate(15)->withQueryString();

        return view('missions.index', compact('missions', 'referenceId', 'onlyMine'));
    }

    public function exportTechnicianCsv(Request $request)
    {
        $user = $request->user();
        abort_unless($user?->role === 'Technicien', 403);

        $type = $request->string('type')->toString();
        if (!in_array($type, ['active', 'completed'], true)) {
            $type = 'active';
        }

        $technicienId = Technicien::query()
            ->where('user_id', $user->id)
            ->value('id');

        abort_unless($technicienId, 404, 'Profil technicien introuvable.');

        $search = trim((string) $request->string('search')->toString());
        $activeStatus = $request->string('active_status')->toString();
        $activePriority = $request->string('active_priority')->toString();
        $activeScope = $request->string('active_scope')->toString();
        $completedPeriod = $request->string('completed_period')->toString();

        $query = Mission::query()
            ->with(['referencePoint:id,reference,adresse'])
            ->whereHas('affectations', function ($q) use ($technicienId) {
                $q->where('technicien_id', $technicienId);
            });

        if ($search !== '') {
            $query->where(function ($q) use ($search) {
                $q->where('type_mission', 'like', '%' . $search . '%')
                    ->orWhere('description', 'like', '%' . $search . '%')
                    ->orWhereHas('referencePoint', function ($refQ) use ($search) {
                        $refQ->where('reference', 'like', '%' . $search . '%')
                            ->orWhere('adresse', 'like', '%' . $search . '%');
                    });
            });
        }

        if ($type === 'active') {
            $query->where('statut', '!=', 'Terminée');

            if ($activeStatus !== '' && $activeStatus !== 'all') {
                $query->where('statut', $activeStatus);
            }

            if ($activePriority !== '' && $activePriority !== 'all') {
                $query->where('priorite', $activePriority);
            }

            if ($activeScope === 'overdue') {
                $query->whereNotNull('due_at')->where('due_at', '<', now());
            } elseif ($activeScope === 'today') {
                $query->whereDate('due_at', now()->toDateString());
            } elseif ($activeScope === 'week') {
                $query->whereBetween('due_at', [now()->startOfWeek(), now()->endOfWeek()]);
            } elseif ($activeScope === 'no_due') {
                $query->whereNull('due_at');
            }

            $query->orderByDesc('due_at')->orderByDesc('created_at');
        } else {
            $query->where('statut', 'Terminée');

            if (in_array($completedPeriod, ['7d', '30d', '90d'], true)) {
                $days = (int) rtrim($completedPeriod, 'd');
                $query->where('completed_at', '>=', now()->subDays($days));
            }

            $query->orderByDesc('completed_at')->orderByDesc('created_at');
        }

        $missions = $query->get();

        $fileName = 'missions-' . $type . '-' . now()->format('Ymd_His') . '.csv';

        return response()->streamDownload(function () use ($missions) {
            $handle = fopen('php://output', 'w');
            fputcsv($handle, ['Mission ID', 'Reference', 'Type', 'Priority', 'Status', 'SLA', 'Due At', 'Completed At', 'Description']);

            foreach ($missions as $mission) {
                fputcsv($handle, [
                    $mission->id,
                    $mission->referencePoint?->reference,
                    $mission->type_mission,
                    $mission->priorite,
                    $mission->statut,
                    $mission->sla_level,
                    $mission->due_at?->format('Y-m-d H:i'),
                    $mission->completed_at?->format('Y-m-d H:i'),
                    $mission->description,
                ]);
            }

            fclose($handle);
        }, $fileName, [
            'Content-Type' => 'text/csv; charset=UTF-8',
        ]);
    }

    public function analysis(Request $request)
    {
        return view('missions.analysis', $this->buildAnalysisData($request));
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

        $mailWarning = null;

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
            $technicianUserId = $affectation->technicien->user->id ?? null;

            $shouldSendEmail = true;
            if ($technicianUserId) {
                $preferences = NotificationPreference::query()
                    ->where('user_id', $technicianUserId)
                    ->first();

                if ($preferences && (!$preferences->email || !$preferences->mission_assigned)) {
                    $shouldSendEmail = false;
                }
            }

            if ($technicienEmail && $shouldSendEmail) {
                try {
                    Mail::to($technicienEmail)->send(new MissionAssignedMail($affectation));
                } catch (\Throwable $exception) {
                    Log::warning('Mission assignment email failed to send.', [
                        'mission_id' => $mission->id,
                        'technician_id' => $affectation->technicien_id,
                        'email' => $technicienEmail,
                        'error' => $exception->getMessage(),
                    ]);

                    $mailWarning = 'Mission creee, mais l\'email de notification n\'a pas pu etre envoye.';
                }
            }
        }

        $redirect = redirect()
            ->route('missions.show', $mission)
            ->with('status', 'Mission créée.');

        if ($mailWarning !== null) {
            $redirect->with('warning', $mailWarning);
        }

        return $redirect;
    }

    public function show(Request $request, Mission $mission)
    {
        Gate::authorize('view-mission', $mission);

        $mission->load([
            'referencePoint',
            'creator:id,name,email',
            'affectations.technicien.user',
            'referenceScans.technicien.user',
            'timeLogs' => fn ($q) => $q->latest('logged_at')->limit(25),
            'attachments.uploader:id,name',
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

        if ($request->hasFile('photos')) {
            foreach ($request->file('photos') as $photo) {
                $storedPath = $photo->store('missions/' . $mission->id . '/photos', 'public');

                MissionAttachment::query()->create([
                    'mission_id' => $mission->id,
                    'affectation_id' => $affectation->id,
                    'uploaded_by' => $user->id,
                    'file_name' => $photo->getClientOriginalName(),
                    'file_path' => $storedPath,
                    'file_type' => $photo->getClientMimeType() ?: 'image/jpeg',
                    'file_size' => $photo->getSize() ?: 0,
                ]);
            }
        }

        return redirect()
            ->route('missions.show', $mission)
            ->with('status', 'Mise à jour technicien enregistrée.');
    }

    public function verifyQr(Request $request, Mission $mission)
    {
        Gate::authorize('work-mission', $mission);

        $data = $request->validate([
            'qr_code' => ['required', 'string', 'max:255'],
        ]);

        $mission->loadMissing('referencePoint:id,reference');
        $expected = trim((string) ($mission->referencePoint?->reference ?? ''));
        $provided = trim($data['qr_code']);

        return response()->json([
            'valid' => $expected !== '' && strcasecmp($expected, $provided) === 0,
            'expected_reference' => $expected,
            'provided_reference' => $provided,
        ]);
    }

    private function buildAnalysisData(Request $request): array
    {
        $user = $request->user();
        if (!in_array($user->role, ['Admin', 'Dispatcher', 'Technicien'], true)) {
            abort(403);
        }

        $validated = $request->validate([
            'period' => ['nullable', 'in:7d,30d,90d,all,custom'],
            'start_date' => ['nullable', 'required_if:period,custom', 'date'],
            'end_date' => ['nullable', 'required_if:period,custom', 'date', 'after_or_equal:start_date'],
        ]);

        [$selectedPeriod, $rangeStart, $rangeEnd] = $this->resolveRange($validated);

        $query = Mission::query()->with([
            'referencePoint:id,reference,adresse',
            'currentAffectation.technicien.user',
        ]);

        if ($user->role === 'Technicien') {
            $technicienId = Technicien::query()
                ->where('user_id', $user->id)
                ->value('id');

            if (!$technicienId) {
                abort(404, 'Profil technicien introuvable.');
            }

            $query->whereHas('affectations', function ($q) use ($technicienId) {
                $q->where('technicien_id', $technicienId);
            });
        }

        if ($rangeStart && $rangeEnd) {
            $query->whereBetween('created_at', [$rangeStart, $rangeEnd]);
        }

        $missions = $query->get();

        $total = $missions->count();
        $completed = $missions->where('statut', 'Terminée')->count();
        $inProgress = $missions->where('statut', 'En cours')->count();
        $blocked = $missions->where('statut', 'Bloquée')->count();
        $cancelled = $missions->where('statut', 'Annulée')->count();

        $validatedRate = $total > 0 ? round(($completed / $total) * 100, 1) : 0.0;
        $blockedRate = $total > 0 ? round(($blocked / $total) * 100, 1) : 0.0;

        $completedWithDueDate = $missions->filter(fn (Mission $mission) => $mission->statut === 'Terminée' && $mission->due_at && $mission->completed_at);
        $onTimeCompleted = $completedWithDueDate->filter(fn (Mission $mission) => $mission->completed_at->lte($mission->due_at))->count();
        $onTimeRate = $completedWithDueDate->isNotEmpty() ? round(($onTimeCompleted / $completedWithDueDate->count()) * 100, 1) : 0.0;

        $avgResolutionHours = round(($missions
            ->filter(fn (Mission $mission) => $mission->started_at && $mission->completed_at)
            ->map(fn (Mission $mission) => $mission->started_at->diffInMinutes($mission->completed_at) / 60)
            ->avg()) ?? 0, 1);

        $highPriorityTotal = $missions->whereIn('priorite', ['Haute', 'Urgente'])->count();
        $highPriorityCompleted = $missions->whereIn('priorite', ['Haute', 'Urgente'])->where('statut', 'Terminée')->count();
        $highPriorityResolutionRate = $highPriorityTotal > 0 ? round(($highPriorityCompleted / $highPriorityTotal) * 100, 1) : 0.0;

        $currentMonthCompleted = $missions
            ->filter(fn (Mission $mission) => $mission->statut === 'Terminée' && $mission->completed_at && $mission->completed_at->isCurrentMonth())
            ->count();

        $months = $this->resolveMonths($rangeStart, $rangeEnd);
        $monthlyLabels = $months->map(fn (Carbon $month) => $month->translatedFormat('M Y'))->values();
        $monthlyCompleted = $months->map(fn (Carbon $month) => $missions->where('statut', 'Terminée')->filter(fn (Mission $mission) => $mission->completed_at?->month === $month->month && $mission->completed_at?->year === $month->year)->count())->values();
        $monthlyAssigned = $months->map(fn (Carbon $month) => $missions->filter(fn (Mission $mission) => $mission->created_at?->month === $month->month && $mission->created_at?->year === $month->year)->count())->values();

        $statusLabels = collect(['Terminée', 'En cours', 'Bloquée', 'Annulée', 'Autre']);
        $statusData = collect([
            $completed,
            $inProgress,
            $blocked,
            $cancelled,
            max($total - ($completed + $inProgress + $blocked + $cancelled), 0),
        ]);

        $recentMissions = $missions->sortByDesc('created_at')->take(8)->values();

        $avgCompletionByTypeCollection = $missions
            ->where('statut', 'Terminée')
            ->groupBy('type_mission')
            ->map(function (Collection $group): float {
                $avg = $group->map(function (Mission $mission): int {
                    if ($mission->total_working_time) {
                        return (int) $mission->total_working_time;
                    }

                    if ($mission->started_at && $mission->completed_at) {
                        return $mission->started_at->diffInMinutes($mission->completed_at);
                    }

                    return 0;
                })->filter()->avg();

                return round($avg ?? 0, 1);
            });

        $avgCompletionByTypeLabels = $avgCompletionByTypeCollection->keys()->values();
        $avgCompletionByTypeData = $avgCompletionByTypeCollection->values();

        $productivityRanking = $missions
            ->where('statut', 'Terminée')
            ->groupBy(fn (Mission $mission) => $mission->currentAffectation?->technicien_id)
            ->filter(fn (Collection $group, $technicianId) => !empty($technicianId))
            ->map(function (Collection $group): array {
                $first = $group->first();
                $tech = $first?->currentAffectation?->technicien;
                $techName = trim(($tech?->prenom ?? '') . ' ' . ($tech?->nom ?? '')) ?: ($tech?->user?->name ?? 'N/A');
                $completedCount = $group->count();
                $totalMinutes = $group->sum(function (Mission $mission): int {
                    if ($mission->total_working_time) {
                        return (int) $mission->total_working_time;
                    }

                    if ($mission->started_at && $mission->completed_at) {
                        return $mission->started_at->diffInMinutes($mission->completed_at);
                    }

                    return 0;
                });

                $hours = max($totalMinutes / 60, 0.1);

                return [
                    'technician' => $techName,
                    'missions_completed' => $completedCount,
                    'hours' => round($hours, 2),
                    'missions_per_hour' => round($completedCount / $hours, 2),
                ];
            })
            ->sortByDesc('missions_per_hour')
            ->take(10)
            ->values();

        $missionIds = $missions->pluck('id')->values();
        $logQuery = MissionTimeLog::query()->whereIn('mission_id', $missionIds);
        if ($rangeStart && $rangeEnd) {
            $logQuery->whereBetween('logged_at', [$rangeStart, $rangeEnd]);
        }

        $logs = $logQuery->orderBy('logged_at')->get(['mission_id', 'action', 'logged_at']);
        $peakHoursCounts = $logs
            ->whereIn('action', ['start_work', 'resume'])
            ->groupBy(fn (MissionTimeLog $log) => Carbon::parse($log->logged_at)->format('H'))
            ->map->count();

        $peakHoursLabels = collect(range(0, 23))->map(fn (int $hour) => str_pad((string) $hour, 2, '0', STR_PAD_LEFT) . ':00');
        $peakHoursData = collect(range(0, 23))->map(fn (int $hour) => $peakHoursCounts->get(str_pad((string) $hour, 2, '0', STR_PAD_LEFT), 0));

        $idleMinutes = 0;
        $breakMinutes = 0;
        foreach ($logs->groupBy('mission_id') as $missionLogs) {
            [$missionIdle, $missionBreak] = $this->extractIdleAndBreakMinutes($missionLogs, $rangeEnd ?? now());
            $idleMinutes += $missionIdle;
            $breakMinutes += $missionBreak;
        }

        $workingMinutes = $missions->sum(fn (Mission $mission): int => (int) ($mission->on_site_time_minutes ?? $mission->total_working_time ?? 0));
        $denominator = $workingMinutes + $idleMinutes + $breakMinutes;
        $timeUtilizationRate = $denominator > 0 ? round(($workingMinutes / $denominator) * 100, 1) : 0.0;

        $completedWithEstimated = $missions->where('statut', 'Terminée')->filter(fn (Mission $mission) => (int) $mission->estimated_duration > 0);
        $overtimeMissions = $completedWithEstimated
            ->filter(fn (Mission $mission) => (int) ($mission->total_working_time ?? 0) > (int) round($mission->estimated_duration * 1.2))
            ->values();
        $overtimeRate = $completedWithEstimated->isNotEmpty() ? round(($overtimeMissions->count() / $completedWithEstimated->count()) * 100, 1) : 0.0;

        return [
            'total' => $total,
            'completed' => $completed,
            'inProgress' => $inProgress,
            'blocked' => $blocked,
            'cancelled' => $cancelled,
            'validatedRate' => $validatedRate,
            'blockedRate' => $blockedRate,
            'onTimeRate' => $onTimeRate,
            'avgResolutionHours' => $avgResolutionHours,
            'highPriorityResolutionRate' => $highPriorityResolutionRate,
            'currentMonthCompleted' => $currentMonthCompleted,
            'recentMissions' => $recentMissions,
            'monthlyLabels' => $monthlyLabels,
            'monthlyCompleted' => $monthlyCompleted,
            'monthlyAssigned' => $monthlyAssigned,
            'statusLabels' => $statusLabels,
            'statusData' => $statusData,
            'selectedPeriod' => $selectedPeriod,
            'rangeStart' => $rangeStart,
            'rangeEnd' => $rangeEnd,
            'avgCompletionByTypeLabels' => $avgCompletionByTypeLabels,
            'avgCompletionByTypeData' => $avgCompletionByTypeData,
            'productivityRanking' => $productivityRanking,
            'peakHoursLabels' => $peakHoursLabels,
            'peakHoursData' => $peakHoursData,
            'overtimeCount' => $overtimeMissions->count(),
            'overtimeRate' => $overtimeRate,
            'timeUtilizationRate' => $timeUtilizationRate,
            'workingMinutes' => $workingMinutes,
            'idleMinutes' => $idleMinutes,
            'breakMinutes' => $breakMinutes,
        ];
    }

    private function resolveRange(array $validated): array
    {
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

        return [$selectedPeriod, $rangeStart, $rangeEnd];
    }

    private function resolveMonths(?Carbon $rangeStart, ?Carbon $rangeEnd): Collection
    {
        if ($rangeStart && $rangeEnd) {
            $months = collect();
            $cursor = $rangeStart->copy()->startOfMonth();
            $endMonth = $rangeEnd->copy()->startOfMonth();

            while ($cursor->lte($endMonth)) {
                $months->push($cursor->copy());
                $cursor->addMonth();
            }

            return $months->take(-6)->values();
        }

        return collect(range(5, 0, -1))
            ->map(fn (int $offset) => Carbon::now()->subMonths($offset)->startOfMonth())
            ->push(Carbon::now()->startOfMonth());
    }

    private function extractIdleAndBreakMinutes(Collection $logs, Carbon $fallbackEnd): array
    {
        $pauseStart = null;
        $breakStart = null;
        $idleMinutes = 0;
        $breakMinutes = 0;

        foreach ($logs as $log) {
            $at = Carbon::parse($log->logged_at);

            if ($log->action === 'pause') {
                $pauseStart ??= $at;
            }

            if ($log->action === 'resume' && $pauseStart) {
                $idleMinutes += $pauseStart->diffInMinutes($at);
                $pauseStart = null;
            }

            if ($log->action === 'break_start') {
                $breakStart ??= $at;
            }

            if ($log->action === 'break_end' && $breakStart) {
                $breakMinutes += $breakStart->diffInMinutes($at);
                $breakStart = null;
            }
        }

        if ($pauseStart) {
            $idleMinutes += $pauseStart->diffInMinutes($fallbackEnd);
        }

        if ($breakStart) {
            $breakMinutes += $breakStart->diffInMinutes($fallbackEnd);
        }

        return [$idleMinutes, $breakMinutes];
    }
}
