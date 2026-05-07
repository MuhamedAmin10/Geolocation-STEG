<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Affectation;
use App\Models\Mission;
use App\Models\Technicien;
use Carbon\Carbon;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class TechnicianMissionApiController extends Controller
{
    public function active(Request $request): JsonResponse
    {
        $technicianId = $this->resolveTechnicianId($request);

        $missions = $this->baseQuery($technicianId)
            ->where('statut', '!=', 'Terminée')
            ->orderByDesc('due_at')
            ->orderByDesc('created_at')
            ->limit(50)
            ->get();

        return response()->json([
            'missions' => $missions->map(fn (Mission $mission) => $this->transformMission($mission))->values(),
        ]);
    }

    public function completed(Request $request): JsonResponse
    {
        $technicianId = $this->resolveTechnicianId($request);

        $missions = $this->baseQuery($technicianId)
            ->where('statut', 'Terminée')
            ->orderByDesc('completed_at')
            ->orderByDesc('created_at')
            ->limit(50)
            ->get();

        return response()->json([
            'missions' => $missions->map(fn (Mission $mission) => $this->transformMission($mission))->values(),
        ]);
    }

    public function show(Request $request, Mission $mission): JsonResponse
    {
        $technicianId = $this->resolveTechnicianId($request);

        $isAssigned = Affectation::query()
            ->where('mission_id', $mission->id)
            ->where('technicien_id', $technicianId)
            ->exists();

        abort_unless($isAssigned, 404);

        $mission->loadMissing([
            'referencePoint:id,reference,latitude,longitude,adresse,gouvernorat,delegation',
            'attachments:id,mission_id,file_path,file_name,file_type,file_size,created_at',
        ]);

        return response()->json([
            'mission' => $this->transformMission($mission),
        ]);
    }

    public function notifications(Request $request): JsonResponse
    {
        $technicianId = $this->resolveTechnicianId($request);
        $since = $request->query('since');

        $query = Affectation::query()
            ->with(['mission.referencePoint:id,reference,latitude,longitude,adresse'])
            ->where('technicien_id', $technicianId)
            ->orderByDesc('assigned_at')
            ->limit(30);

        if (is_string($since) && trim($since) !== '') {
            try {
                $query->where('assigned_at', '>', Carbon::parse($since));
            } catch (\Throwable $e) {
                // Ignore malformed "since" values and return latest notifications.
            }
        }

        $items = $query->get()->map(function (Affectation $affectation): array {
            $mission = $affectation->mission;
            $referencePoint = $mission?->referencePoint;

            return [
                'type' => 'mission_assigned',
                'assigned_at' => optional($affectation->assigned_at)->toIso8601String(),
                'mission_id' => $mission?->id,
                'title' => 'Nouvelle mission affectee',
                'message' => 'Mission #' . ($mission?->id ?? 'N/A') . ' - ' . ($referencePoint?->reference ?? 'Reference inconnue'),
                'reference' => $referencePoint?->reference,
                'location' => [
                    'latitude' => $referencePoint?->latitude !== null ? (float) $referencePoint->latitude : null,
                    'longitude' => $referencePoint?->longitude !== null ? (float) $referencePoint->longitude : null,
                    'address' => $referencePoint?->adresse,
                ],
            ];
        })->values();

        return response()->json([
            'notifications' => $items,
        ]);
    }

    private function resolveTechnicianId(Request $request): int
    {
        $user = $request->user();
        abort_unless($user && $user->role === 'Technicien', 403);

        $technicianId = Technicien::query()->where('user_id', $user->id)->value('id');
        abort_unless($technicianId, 404, 'Technician profile not found.');

        return (int) $technicianId;
    }

    private function baseQuery(int $technicianId)
    {
        return Mission::query()
            ->with([
                'referencePoint:id,reference,latitude,longitude,adresse,gouvernorat,delegation',
                'currentAffectation.technicien.user:id,name',
            ])
            ->whereHas('affectations', function ($q) use ($technicianId) {
                $q->where('technicien_id', $technicianId);
            });
    }

    private function transformMission(Mission $mission): array
    {
        $referencePoint = $mission->referencePoint;
        $latitude = $referencePoint?->latitude !== null ? (float) $referencePoint->latitude : null;
        $longitude = $referencePoint?->longitude !== null ? (float) $referencePoint->longitude : null;

        $mapsUrl = null;
        if ($latitude !== null && $longitude !== null) {
            $mapsUrl = 'https://www.google.com/maps/search/?api=1&query=' . $latitude . ',' . $longitude;
        }

        return [
            'id' => $mission->id,
            'reference' => $referencePoint?->reference,
            'type' => $mission->type_mission,
            'priority' => $mission->priorite,
            'status' => $mission->statut,
            'description' => $mission->description,
            'sla_level' => $mission->sla_level,
            'estimated_duration_minutes' => $mission->estimated_duration,
            'total_working_time_minutes' => $mission->total_working_time,
            'on_site_time_minutes' => $mission->on_site_time_minutes,
            'travel_time_minutes' => $mission->travel_time_minutes,
            'started_at' => optional($mission->started_at)->toIso8601String(),
            'completed_at' => optional($mission->completed_at)->toIso8601String(),
            'due_at' => optional($mission->due_at)->toIso8601String(),
            'location' => [
                'latitude' => $latitude,
                'longitude' => $longitude,
                'address' => $referencePoint?->adresse,
                'gouvernorat' => $referencePoint?->gouvernorat,
                'delegation' => $referencePoint?->delegation,
                'maps_url' => $mapsUrl,
            ],
        ];
    }
}
