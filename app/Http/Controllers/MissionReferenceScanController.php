<?php

namespace App\Http\Controllers;

use App\Http\Requests\StoreMissionReferenceScanRequest;
use App\Models\Affectation;
use App\Models\Mission;
use App\Models\MissionReferenceScan;
use App\Models\Technicien;
use Carbon\Carbon;
use Illuminate\Http\JsonResponse;
use Illuminate\Support\Facades\Gate;

class MissionReferenceScanController extends Controller
{
    public function store(StoreMissionReferenceScanRequest $request, Mission $mission): JsonResponse
    {
        Gate::authorize('work-mission', $mission);

        $user = $request->user();
        $technicienId = Technicien::query()
            ->where('user_id', $user->id)
            ->value('id');

        abort_unless($technicienId, 403);

        $affectation = Affectation::query()
            ->where('mission_id', $mission->id)
            ->where('technicien_id', $technicienId)
            ->latest('assigned_at')
            ->first();

        abort_unless($affectation, 403);

        $data = $request->validated();

        $mission->loadMissing('referencePoint:id,reference,latitude,longitude');
        $expectedReference = trim((string) ($mission->referencePoint?->reference ?? ''));
        $providedReference = trim($data['qr_code']);
        $isMatch = $expectedReference !== '' && strcasecmp($expectedReference, $providedReference) === 0;

        $distanceMeters = $this->distanceToReferenceMeters(
            $mission->referencePoint?->latitude !== null ? (float) $mission->referencePoint->latitude : null,
            $mission->referencePoint?->longitude !== null ? (float) $mission->referencePoint->longitude : null,
            (float) $data['latitude'],
            (float) $data['longitude']
        );

        $scan = MissionReferenceScan::query()->create([
            'mission_id' => $mission->id,
            'reference_point_id' => $mission->reference_id,
            'technicien_id' => (int) $technicienId,
            'reference_code' => $providedReference,
            'compteur_type' => $data['compteur_type'],
            'latitude' => (float) $data['latitude'],
            'longitude' => (float) $data['longitude'],
            'accuracy_m' => $data['accuracy_m'] ?? null,
            'distance_m' => $distanceMeters,
            'is_match' => $isMatch,
            'notes' => $data['notes'] ?? null,
            'scanned_at' => Carbon::now(),
        ]);

        $scan->load(['technicien.user:id,name', 'referencePoint:id,reference,adresse']);

        return response()->json([
            'message' => $isMatch
                ? 'Lecture du compteur enregistrée.'
                : 'Lecture enregistrée, mais la référence ne correspond pas.',
            'valid' => $isMatch,
            'expected_reference' => $expectedReference,
            'scan' => [
                'id' => $scan->id,
                'reference_code' => $scan->reference_code,
                'compteur_type' => $scan->compteur_type,
                'latitude' => $scan->latitude,
                'longitude' => $scan->longitude,
                'accuracy_m' => $scan->accuracy_m,
                'distance_m' => $scan->distance_m,
                'is_match' => $scan->is_match,
                'scanned_at' => optional($scan->scanned_at)->toIso8601String(),
            ],
        ], 201);
    }

    private function distanceToReferenceMeters(?float $referenceLatitude, ?float $referenceLongitude, float $latitude, float $longitude): ?float
    {
        if ($referenceLatitude === null || $referenceLongitude === null) {
            return null;
        }

        $earthRadius = 6371000;
        $latFrom = deg2rad($referenceLatitude);
        $lonFrom = deg2rad($referenceLongitude);
        $latTo = deg2rad($latitude);
        $lonTo = deg2rad($longitude);

        $latDelta = $latTo - $latFrom;
        $lonDelta = $lonTo - $lonFrom;

        $angle = 2 * asin(sqrt(
            pow(sin($latDelta / 2), 2) +
            cos($latFrom) * cos($latTo) * pow(sin($lonDelta / 2), 2)
        ));

        return $earthRadius * $angle;
    }
}