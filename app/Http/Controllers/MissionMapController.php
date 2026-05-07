<?php

namespace App\Http\Controllers;

use App\Models\Mission;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Gate;

class MissionMapController extends Controller
{
    public function index(Request $request)
    {
        Gate::authorize('manage-missions');

        $status = $request->string('status')->toString();

        $missions = Mission::query()
            ->with(['referencePoint:id,reference,latitude,longitude,adresse', 'client:id,name,company'])
            ->when($status !== '', fn ($query) => $query->where('statut', $status))
            ->whereHas('referencePoint')
            ->latest()
            ->limit(200)
            ->get();

        $statusOptions = Mission::query()
            ->select('statut')
            ->distinct()
            ->orderBy('statut')
            ->pluck('statut');

        return view('missions.map', compact('missions', 'status', 'statusOptions'));
    }
}
