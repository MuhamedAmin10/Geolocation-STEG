<?php

namespace App\Http\Controllers;

use App\Http\Requests\StoreMissionRequest;
use App\Http\Requests\UpdateMissionRequest;
use App\Http\Requests\UpdateMissionWorkRequest;
use App\Models\Affectation;
use App\Models\Mission;
use App\Models\ReferencePoint;
use App\Models\Technicien;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Gate;

class MissionController extends Controller
{
    public function index(Request $request)
    {
        $user = $request->user();

        $referenceId = $request->integer('reference_id');

        $query = Mission::query()
            ->with([
                'referencePoint:id,reference,adresse,gouvernorat,delegation',
                'creator:id,name,email',
                'currentAffectation.technicien.user',
            ])
            ->orderByDesc('created_at');

        if ($user->role === 'Technicien') {
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

        $missions = $query->paginate(15);

        return view('missions.index', compact('missions', 'referenceId'));
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

        if ($technicienId) {
            Affectation::query()->create([
                'mission_id' => $mission->id,
                'technicien_id' => (int) $technicienId,
                'assigned_by' => $request->user()->id,
                'assigned_at' => now(),
            ]);
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

        return view('missions.show', compact('mission'));
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

        $data = $request->validated();
        $technicienId = $data['technicien_id'] ?? null;
        unset($data['technicien_id']);

        $mission->fill($data);
        $mission->save();

        if ($technicienId) {
            $mission->load('currentAffectation');
            $currentTechnicienId = $mission->currentAffectation?->technicien_id;

            if ((int) $technicienId !== (int) $currentTechnicienId) {
                Affectation::query()->create([
                    'mission_id' => $mission->id,
                    'technicien_id' => (int) $technicienId,
                    'assigned_by' => $request->user()->id,
                    'assigned_at' => now(),
                ]);
            }
        }

        return redirect()
            ->route('missions.show', $mission)
            ->with('status', 'Mission mise à jour.');
    }

    public function destroy(Request $request, Mission $mission)
    {
        Gate::authorize('manage-missions');

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
