<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Affectation;
use App\Models\Mission;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Gate;

class MissionAssignmentController extends Controller
{
    public function store(Request $request, Mission $mission)
    {
        Gate::authorize('manage-missions');

        $data = $request->validate([
            'technicien_id' => ['required', 'integer', 'exists:techniciens,id'],
        ]);

        Affectation::query()->create([
            'mission_id' => $mission->id,
            'technicien_id' => (int) $data['technicien_id'],
            'assigned_by' => $request->user()->id,
            'assigned_at' => now(),
        ]);

        if ($mission->statut === 'Créée') {
            $mission->statut = 'Assignée';
            $mission->save();
        }

        return back()->with('status', 'Mission affectée.');
    }
}
