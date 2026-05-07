<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Mail\MissionAssignedMail;
use App\Models\Affectation;
use App\Models\ClientReclamation;
use App\Models\Mission;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Gate;
use Illuminate\Support\Facades\Mail;

class ReclamationController extends Controller
{
    public function assign(Request $request, ClientReclamation $reclamation)
    {
        Gate::authorize('manage-missions');

        abort_if($reclamation->mission_id !== null, 409, 'Réclamation déjà traitée.');

        $data = $request->validate([
            'technicien_id' => ['required', 'integer', 'exists:techniciens,id'],
            'type_mission' => ['required', 'string', 'max:120'],
            'priorite' => ['required', 'string', 'max:50'],
            'admin_note' => ['nullable', 'string', 'max:2000'],
            'due_at' => ['nullable', 'date_format:Y-m-d\\TH:i'],
        ]);

        $reclamation->load('client');

        $adminNote = trim((string) ($data['admin_note'] ?? ''));

        $missionDescription = trim(sprintf(
            "Réclamation compteur %s\n\n%s\n\n%s",
            $reclamation->compteur_reference,
            $reclamation->subject,
            $reclamation->description
        ));

        if ($adminNote !== '') {
            $missionDescription .= "\n\nNote admin:\n".$adminNote;
        }

        $dueAt = null;
        if (! empty($data['due_at'])) {
            $dueAt = Carbon::createFromFormat('Y-m-d\\TH:i', $data['due_at']);
        }

        $mission = Mission::query()->create([
            'reference_id' => $reclamation->reference_id,
            'client_id' => $reclamation->client_id,
            'created_by' => $request->user()->id,
            'type_mission' => trim((string) $data['type_mission']),
            'priorite' => trim((string) $data['priorite']),
            'description' => $missionDescription,
            'due_at' => $dueAt,
            'statut' => 'Créée',
            'sla_level' => $reclamation->client?->sla_level ?? 'Bronze',
        ]);

        $affectation = Affectation::query()->create([
            'mission_id' => $mission->id,
            'technicien_id' => (int) $data['technicien_id'],
            'assigned_by' => $request->user()->id,
            'assigned_at' => now(),
        ]);

        $affectation->load(['mission.referencePoint', 'technicien.user', 'assignedBy']);

        $technicienEmail = $affectation->technicien->user->email ?? null;
        if ($technicienEmail) {
            Mail::to($technicienEmail)->send(new MissionAssignedMail($affectation));
        }

        $mission->update(['statut' => 'Assignée']);

        $reclamation->update([
            'status' => 'Transmise',
            'mission_id' => $mission->id,
            'handled_by' => $request->user()->id,
            'handled_at' => now(),
        ]);

        return back()->with('status', 'Réclamation convertie en mission et affectée.');
    }
}