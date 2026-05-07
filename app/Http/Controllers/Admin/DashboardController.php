<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Affectation;
use App\Models\AuditLog;
use App\Models\ClientReclamation;
use App\Models\Mission;
use App\Models\Technicien;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Gate;

class DashboardController extends Controller
{
    public function index(Request $request)
    {
        Gate::authorize('access-admin');

        $counts = [
            'users' => User::query()->count(),
            'techniciens' => Technicien::query()->count(),
            'missions' => Mission::query()->count(),
            'missions_unassigned' => Mission::query()->whereDoesntHave('affectations')->count(),
            'reclamations' => ClientReclamation::query()->count(),
            'reclamations_open' => ClientReclamation::query()->whereIn('status', ['Nouveau', 'En attente'])->count(),
        ];

        $unassignedMissions = Mission::query()
            ->with(['referencePoint:id,reference,adresse', 'creator:id,name'])
            ->whereDoesntHave('affectations')
            ->orderByDesc('created_at')
            ->limit(10)
            ->get();

        $reclamations = ClientReclamation::query()
            ->with(['client:id,name,email', 'referencePoint:id,reference,adresse', 'mission:id,statut', 'handledBy:id,name'])
            ->latest()
            ->limit(10)
            ->get();

        $techniciens = Technicien::query()
            ->with('user:id,name')
            ->orderBy('nom')
            ->orderBy('prenom')
            ->get();

        return view('admin.dashboard', compact('counts', 'unassignedMissions', 'reclamations', 'techniciens')); 
    }

    public function analysis(Request $request)
    {
        Gate::authorize('access-admin');

        $counts = [
            'users' => User::query()->count(),
            'techniciens' => Technicien::query()->count(),
            'missions' => Mission::query()->count(),
            'missions_unassigned' => Mission::query()->whereDoesntHave('affectations')->count(),
            'missions_assigned' => Mission::query()->whereHas('affectations')->count(),
            'missions_completed' => Mission::query()->where('statut', 'Terminée')->count(),
            'missions_in_progress' => Mission::query()->where('statut', 'En cours')->count(),
            'missions_blocked' => Mission::query()->where('statut', 'Bloquée')->count(),
        ];

        $overdueMissions = Mission::query()
            ->whereNotNull('due_at')
            ->where('due_at', '<', now())
            ->whereNotIn('statut', ['Terminée', 'Annulée'])
            ->count();

        $completionRate = $counts['missions'] > 0
            ? round(($counts['missions_completed'] / $counts['missions']) * 100, 1)
            : 0.0;

        $assignmentRate = $counts['missions'] > 0
            ? round(($counts['missions_assigned'] / $counts['missions']) * 100, 1)
            : 0.0;

        $recentMissions = Mission::query()
            ->with(['referencePoint:id,reference,adresse', 'creator:id,name', 'currentAffectation.technicien.user'])
            ->latest()
            ->limit(10)
            ->get();

        $recentAuditLogs = AuditLog::query()
            ->where('auditable_type', 'Mission')
            ->with(['user:id,name'])
            ->latest()
            ->limit(10)
            ->get();

        return view('admin.analysis', compact(
            'counts',
            'overdueMissions',
            'completionRate',
            'assignmentRate',
            'recentMissions',
            'recentAuditLogs'
        ));
    }
}
