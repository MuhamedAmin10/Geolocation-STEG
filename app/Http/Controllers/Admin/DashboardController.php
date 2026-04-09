<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Affectation;
use App\Models\AuditLog;
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
        ];

        $unassignedMissions = Mission::query()
            ->with(['referencePoint:id,reference,adresse', 'creator:id,name'])
            ->whereDoesntHave('affectations')
            ->orderByDesc('created_at')
            ->limit(10)
            ->get();

        $techniciens = Technicien::query()
            ->with('user:id,name')
            ->orderBy('nom')
            ->orderBy('prenom')
            ->get();

        return view('admin.dashboard', compact('counts', 'unassignedMissions', 'techniciens'));
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

        $statusBreakdown = Mission::query()
            ->selectRaw('statut, COUNT(*) as total')
            ->groupBy('statut')
            ->pluck('total', 'statut');

        $priorityBreakdown = Mission::query()
            ->selectRaw('priorite, COUNT(*) as total')
            ->groupBy('priorite')
            ->pluck('total', 'priorite');

        $topTechniciens = Affectation::query()
            ->selectRaw('technicien_id, COUNT(*) as total')
            ->with(['technicien:id,nom,prenom,user_id', 'technicien.user:id,name'])
            ->groupBy('technicien_id')
            ->orderByDesc('total')
            ->limit(8)
            ->get();

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
            'statusBreakdown',
            'priorityBreakdown',
            'topTechniciens',
            'recentMissions',
            'recentAuditLogs'
        ));
    }
}
