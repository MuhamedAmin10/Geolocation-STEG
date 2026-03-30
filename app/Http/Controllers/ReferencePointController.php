<?php

namespace App\Http\Controllers;

use App\Http\Requests\UpdateReferencePointRequest;
use App\Models\ReferencePoint;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Gate;

class ReferencePointController extends Controller
{
    public function create()
    {
        Gate::authorize('manage-references');

        return view('reference_points.create');
    }

    public function store(Request $request)
    {
        Gate::authorize('manage-references');

        $data = $request->validate([
            'reference'  => ['required', 'string', 'max:255', 'unique:reference_points,reference'],
            'latitude'   => ['required', 'numeric', 'between:-90,90'],
            'longitude'  => ['required', 'numeric', 'between:-180,180'],
            'adresse'    => ['nullable', 'string'],
            'gouvernorat'=> ['nullable', 'string', 'max:255'],
            'delegation' => ['nullable', 'string', 'max:255'],
            'precision_m'=> ['nullable', 'integer', 'min:0'],
            'statut'     => ['required', 'string', 'in:à vérifier,validé,rejeté'],
        ]);

        $data['updated_by'] = $request->user()->id;

        ReferencePoint::create($data);

        return redirect()
            ->route('admin.dashboard')
            ->with('status', 'Référence créée avec succès.');
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
            ->route('dashboard')
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
            ->route('dashboard')
            ->with('status', 'Référence archivée.');
    }
}