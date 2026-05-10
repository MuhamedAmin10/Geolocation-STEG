<?php

namespace App\Http\Controllers;

use App\Http\Requests\StoreReferenceCollectionScanRequest;
use App\Models\ReferenceCollectionScan;
use App\Models\ReferencePoint;
use App\Models\Technicien;
use Carbon\Carbon;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class ReferenceCollectionController extends Controller
{
    public function index(Request $request)
    {
        $technicienId = $this->resolveTechnicienId($request);

        $recentScans = ReferenceCollectionScan::query()
            ->with('referencePoint:id,reference,adresse,meter_type')
            ->where('technicien_id', $technicienId)
            ->latest('scanned_at')
            ->limit(20)
            ->get();

        return view('references.collect', compact('recentScans'));
    }

    public function store(StoreReferenceCollectionScanRequest $request): JsonResponse
    {
        $technicienId = $this->resolveTechnicienId($request);

        $data = $request->validated();
        $referenceCode = trim((string) $data['reference_code']);

        $referencePoint = ReferencePoint::query()
            ->whereRaw('LOWER(reference) = ?', [mb_strtolower($referenceCode)])
            ->first();

        $wasCreated = false;
        if (!$referencePoint) {
            $referencePoint = ReferencePoint::query()->create([
                'reference' => $referenceCode,
                'meter_type' => $data['meter_type'],
                'latitude' => (float) $data['latitude'],
                'longitude' => (float) $data['longitude'],
                'adresse' => null,
                'gouvernorat' => null,
                'delegation' => null,
                'precision_m' => isset($data['accuracy_m']) ? (int) round((float) $data['accuracy_m']) : null,
                'statut' => 'à vérifier',
                'updated_by' => $request->user()->id,
            ]);
            $wasCreated = true;
        } else {
            $updates = [];
            if (!$referencePoint->meter_type) {
                $updates['meter_type'] = $data['meter_type'];
            }
            if ($referencePoint->precision_m === null && isset($data['accuracy_m'])) {
                $updates['precision_m'] = (int) round((float) $data['accuracy_m']);
            }
            if (!empty($updates)) {
                $updates['updated_by'] = $request->user()->id;
                $referencePoint->fill($updates)->save();
            }
        }

        $scan = ReferenceCollectionScan::query()->create([
            'reference_point_id' => $referencePoint->id,
            'technicien_id' => $technicienId,
            'reference_code' => $referenceCode,
            'meter_type' => $data['meter_type'],
            'latitude' => (float) $data['latitude'],
            'longitude' => (float) $data['longitude'],
            'accuracy_m' => $data['accuracy_m'] ?? null,
            'notes' => $data['notes'] ?? null,
            'was_created' => $wasCreated,
            'scanned_at' => Carbon::now(),
        ]);

        return response()->json([
            'message' => $wasCreated
                ? 'Nouvelle référence créée et enregistrée.'
                : 'Référence existante, scan enregistré.',
            'was_created' => $wasCreated,
            'reference' => [
                'id' => $referencePoint->id,
                'code' => $referencePoint->reference,
                'meter_type' => $referencePoint->meter_type,
                'latitude' => (float) $referencePoint->latitude,
                'longitude' => (float) $referencePoint->longitude,
            ],
            'scan' => [
                'id' => $scan->id,
                'scanned_at' => optional($scan->scanned_at)->toIso8601String(),
            ],
        ], 201);
    }

    private function resolveTechnicienId(Request $request): int
    {
        $role = strtolower(trim((string) ($request->user()?->role ?? '')));
        abort_unless($role === 'technicien', 403);

        $technicienId = Technicien::query()
            ->where('user_id', $request->user()->id)
            ->value('id');

        abort_unless($technicienId, 404, 'Profil technicien introuvable.');

        return (int) $technicienId;
    }
}