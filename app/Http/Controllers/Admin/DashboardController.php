<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
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
}
