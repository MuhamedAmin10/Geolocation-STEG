<?php

namespace App\Http\Controllers;

use App\Http\Requests\UpdateReferencePointRequest;
use App\Models\ReferencePoint;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Gate;

class ReferencePointController extends Controller
{
    public function index()
    {
        return view('references.search');
    }

    public function edit(Request $request, ReferencePoint $referencePoint)
    {
        Gate::authorize('manage-references');

        return view('reference_points.edit', compact('referencePoint'));
    }

    public function update(UpdateReferencePointRequest $request, ReferencePoint $referencePoint)
    {
        Gate::authorize('manage-references');

        $data = $request->validated();
        $data['updated_by'] = $request->user()->id;

        $referencePoint->fill($data);
        $referencePoint->save();

        return redirect()
            ->route('reference.search')
            ->with('status', 'Référence mise à jour.');
    }

    public function showByReference(string $reference)
    {
        $ref = ReferencePoint::query()->where('reference', $reference)->first();

        if (!$ref) {
            return response()->json(['message' => 'Référence introuvable'], 404);
        }

        return response()->json([
            'id' => $ref->id,
            'reference' => $ref->reference,
            'latitude' => (float) $ref->latitude,
            'longitude' => (float) $ref->longitude,
            'adresse' => $ref->adresse,
            'gouvernorat' => $ref->gouvernorat,
            'delegation' => $ref->delegation,
            'precision_m' => $ref->precision_m,
            'statut' => $ref->statut,
        ]);
    }

    public function destroy(Request $request, ReferencePoint $referencePoint)
    {
        Gate::authorize('manage-references');

        $referencePoint->delete();

        return redirect()
            ->route('reference.search')
            ->with('status', 'Référence archivée.');
    }
}