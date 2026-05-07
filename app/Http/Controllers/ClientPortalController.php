<?php

namespace App\Http\Controllers;

use App\Models\Client;
use App\Models\ClientFeedback;
use App\Models\ClientReclamation;
use App\Models\ReferencePoint;
use Illuminate\Http\Request;

class ClientPortalController extends Controller
{
    private const CLIENT_ROLE = 'Client';

    public function index(Request $request)
    {
        $user = $request->user();
        abort_unless($this->isClient($user?->role), 403);

        $client = $this->resolveClient($user);

        $missions = $client->missions()
            ->with([
                'referencePoint:id,reference,adresse,gouvernorat,delegation',
                'currentAffectation.technicien.user',
                'clientFeedback.submittedBy:id,name',
            ])
            ->latest()
            ->limit(12)
            ->get();

        $reclamations = $client->reclamations()
            ->with([
                'referencePoint:id,reference,adresse',
                'mission:id,statut',
                'handledBy:id,name',
            ])
            ->latest()
            ->limit(12)
            ->get();

        $stats = [
            'total' => $missions->count(),
            'completed' => $missions->where('statut', 'Terminée')->count(),
            'in_progress' => $missions->where('statut', 'En cours')->count(),
            'blocked' => $missions->where('statut', 'Bloquée')->count(),
            'avg_rating' => round((float) ($missions->filter(fn ($mission) => $mission->clientFeedback)->avg(fn ($mission) => $mission->clientFeedback->rating) ?? 0), 1),
            'reclamations_total' => $reclamations->count(),
            'reclamations_open' => $reclamations->whereIn('status', ['Nouveau', 'En attente'])->count(),
        ];

        return view('client.portal', compact('client', 'missions', 'reclamations', 'stats'));
    }

    public function storeFeedback(Request $request)
    {
        $user = $request->user();
        abort_unless($this->isClient($user?->role), 403);

        $data = $request->validate([
            'mission_id' => ['required', 'exists:missions,id'],
            'rating' => ['required', 'integer', 'min:1', 'max:5'],
            'comment' => ['nullable', 'string', 'max:2000'],
        ]);

        $client = $this->resolveClient($user);

        $feedback = ClientFeedback::query()->updateOrCreate(
            [
                'client_id' => $client->id,
                'mission_id' => (int) $data['mission_id'],
            ],
            [
                'submitted_by' => $user->id,
                'rating' => (int) $data['rating'],
                'comment' => $data['comment'] ?? null,
            ]
        );

        return redirect()
            ->route('client.portal')
            ->with('status', 'Avis client enregistré.');
    }

    public function storeReclamation(Request $request)
    {
        $user = $request->user();
        abort_unless($this->isClient($user?->role), 403);

        $data = $request->validate([
            'compteur_reference' => ['required', 'string', 'max:255', 'exists:reference_points,reference'],
            'subject' => ['required', 'string', 'max:255'],
            'description' => ['required', 'string', 'max:5000'],
        ]);

        $client = $this->resolveClient($user);
        $referencePoint = ReferencePoint::query()
            ->where('reference', $data['compteur_reference'])
            ->firstOrFail();

        ClientReclamation::query()->create([
            'client_id' => $client->id,
            'reference_id' => $referencePoint->id,
            'compteur_reference' => $data['compteur_reference'],
            'subject' => $data['subject'],
            'description' => $data['description'],
            'status' => 'Nouveau',
        ]);

        return redirect()
            ->route('client.portal')
            ->with('status', 'Réclamation envoyée au service client.');
    }

    private function resolveClient($user): Client
    {
        return Client::query()->firstOrCreate(
            ['user_id' => $user->id],
            [
                'name' => $user->name,
                'email' => $user->email,
                'active' => true,
            ]
        );
    }

    private function isClient(?string $role): bool
    {
        return strcasecmp(trim((string) $role), self::CLIENT_ROLE) === 0;
    }
}
