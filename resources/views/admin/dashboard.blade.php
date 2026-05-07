<x-app-layout>
    <x-slot name="header">
        <div class="flex flex-col gap-4 lg:flex-row lg:items-center lg:justify-between">
            <div>
                <p class="text-xs font-semibold uppercase tracking-[0.24em] text-slate-500">Centre de pilotage</p>
                <h2 class="mt-1 text-2xl font-bold leading-tight text-slate-900">Dashboard administrateur</h2>
                <p class="mt-1 text-sm text-slate-600">Vue rapide de l'activite, des priorites et des actions immediates.</p>
            </div>

            <div class="flex items-center gap-2">
                <a href="{{ route('admin.analysis') }}" class="inline-flex items-center rounded-xl border border-slate-300 bg-white px-4 py-2 text-sm font-semibold text-slate-700 transition hover:bg-slate-50">Analyse admin</a>
                <a href="{{ route('missions.index') }}" class="inline-flex items-center rounded-xl bg-slate-900 px-4 py-2 text-sm font-semibold text-white transition hover:bg-slate-800">Voir les missions</a>
            </div>
        </div>
    </x-slot>

    <div class="py-10">
        <div class="mx-auto max-w-7xl space-y-6 sm:px-6 lg:px-8">
            @if (session('status'))
                <div class="rounded-xl border border-emerald-200 bg-emerald-50 px-4 py-3 text-sm font-medium text-emerald-800">
                    {{ session('status') }}
                </div>
            @endif

            @php
                $assignmentRate = $counts['missions'] > 0
                    ? round((($counts['missions'] - $counts['missions_unassigned']) / $counts['missions']) * 100)
                    : 0;
                $assignedMissions = max($counts['missions'] - $counts['missions_unassigned'], 0);
                $unassignedRate = $counts['missions'] > 0
                    ? round(($counts['missions_unassigned'] / $counts['missions']) * 100)
                    : 0;
                $alertTone = $counts['missions_unassigned'] > 5
                    ? 'critical'
                    : ($counts['missions_unassigned'] > 0 ? 'warning' : 'stable');
            @endphp

            <section class="rounded-2xl border p-4 sm:p-5 @if($alertTone === 'critical') border-rose-300 bg-rose-50 @elseif($alertTone === 'warning') border-amber-300 bg-amber-50 @else border-emerald-300 bg-emerald-50 @endif">
                <div class="flex flex-col gap-3 md:flex-row md:items-center md:justify-between">
                    <div class="flex items-start gap-3">
                        <span class="mt-0.5 inline-flex h-8 w-8 items-center justify-center rounded-full @if($alertTone === 'critical') bg-rose-100 text-rose-700 @elseif($alertTone === 'warning') bg-amber-100 text-amber-700 @else bg-emerald-100 text-emerald-700 @endif">
                            @if($alertTone === 'critical')
                                !
                            @elseif($alertTone === 'warning')
                                !
                            @else
                                +
                            @endif
                        </span>
                        <div>
                            <p class="text-xs font-semibold uppercase tracking-wider @if($alertTone === 'critical') text-rose-700 @elseif($alertTone === 'warning') text-amber-700 @else text-emerald-700 @endif">Priorite admin</p>
                            <p class="mt-1 text-sm font-semibold @if($alertTone === 'critical') text-rose-900 @elseif($alertTone === 'warning') text-amber-900 @else text-emerald-900 @endif">
                                @if($alertTone === 'critical')
                                    Charge critique: plusieurs missions attendent une affectation immediate.
                                @elseif($alertTone === 'warning')
                                    Attention: des missions restent non affectees et demandent une action rapide.
                                @else
                                    Situation stable: toutes les missions sont actuellement affectees.
                                @endif
                            </p>
                        </div>
                    </div>

                    <div class="flex flex-wrap items-center gap-2">
                        <a href="{{ route('missions.index') }}" class="inline-flex items-center rounded-lg border border-slate-300 bg-white px-3 py-2 text-xs font-semibold uppercase tracking-wider text-slate-700 transition hover:bg-slate-50">Revoir les missions</a>
                        <a href="{{ route('admin.analysis') }}" class="inline-flex items-center rounded-lg bg-slate-900 px-3 py-2 text-xs font-semibold uppercase tracking-wider text-white transition hover:bg-slate-800">Vue analyse</a>
                    </div>
                </div>
            </section>

            <section class="grid grid-cols-1 gap-4 sm:grid-cols-2 xl:grid-cols-5">
                <article class="rounded-2xl border border-slate-200 bg-white p-6 shadow-sm">
                    <div class="flex items-start justify-between gap-3">
                        <p class="text-xs font-semibold uppercase tracking-wider text-slate-500">Utilisateurs</p>
                        <span class="inline-flex h-9 w-9 items-center justify-center rounded-lg bg-slate-100 text-slate-700">
                            <svg class="h-4 w-4" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true"><path d="M16 21v-2a4 4 0 0 0-4-4H5a4 4 0 0 0-4 4v2"></path><circle cx="8.5" cy="7" r="4"></circle><path d="M20 8v6"></path><path d="M23 11h-6"></path></svg>
                        </span>
                    </div>
                    <p class="mt-3 text-3xl font-bold text-slate-900">{{ $counts['users'] }}</p>
                    <p class="mt-1 text-xs text-slate-500">Comptes actifs sur la plateforme.</p>
                </article>

                <article class="rounded-2xl border border-sky-200 bg-sky-50 p-6 shadow-sm">
                    <div class="flex items-start justify-between gap-3">
                        <p class="text-xs font-semibold uppercase tracking-wider text-sky-700">Techniciens</p>
                        <span class="inline-flex h-9 w-9 items-center justify-center rounded-lg bg-white/80 text-sky-700">
                            <svg class="h-4 w-4" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true"><path d="M14 19a6 6 0 0 0-12 0"></path><circle cx="8" cy="9" r="4"></circle><path d="M18 8v6"></path><path d="M21 11h-6"></path></svg>
                        </span>
                    </div>
                    <p class="mt-3 text-3xl font-bold text-sky-900">{{ $counts['techniciens'] }}</p>
                    <p class="mt-1 text-xs text-sky-700/80">Equipe mobilisable pour les interventions.</p>
                </article>

                <article class="rounded-2xl border border-slate-200 bg-white p-6 shadow-sm">
                    <div class="flex items-start justify-between gap-3">
                        <p class="text-xs font-semibold uppercase tracking-wider text-slate-500">Missions</p>
                        <span class="inline-flex h-9 w-9 items-center justify-center rounded-lg bg-slate-100 text-slate-700">
                            <svg class="h-4 w-4" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true"><rect x="4" y="4" width="16" height="16" rx="2"></rect><path d="M8 8h8"></path><path d="M8 12h8"></path><path d="M8 16h5"></path></svg>
                        </span>
                    </div>
                    <p class="mt-3 text-3xl font-bold text-slate-900">{{ $counts['missions'] }}</p>
                    <p class="mt-1 text-xs text-slate-500">Volume total des demandes enregistrees.</p>
                </article>

                <article class="rounded-2xl border border-amber-200 bg-amber-50 p-6 shadow-sm">
                    <div class="flex items-start justify-between gap-3">
                        <p class="text-xs font-semibold uppercase tracking-wider text-amber-700">Non affectees</p>
                        <span class="inline-flex h-9 w-9 items-center justify-center rounded-lg bg-white/80 text-amber-700">
                            <svg class="h-4 w-4" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true"><path d="M12 9v4"></path><path d="M12 17h.01"></path><path d="M10.29 3.86 1.82 18a2 2 0 0 0 1.72 3h16.92a2 2 0 0 0 1.72-3L13.71 3.86a2 2 0 0 0-3.42 0Z"></path></svg>
                        </span>
                    </div>
                    <p class="mt-3 text-3xl font-bold text-amber-900">{{ $counts['missions_unassigned'] }}</p>
                    <p class="mt-1 text-xs text-amber-800/80">Missions en attente d'attribution.</p>
                </article>

                <article class="rounded-2xl border border-rose-200 bg-rose-50 p-6 shadow-sm">
                    <div class="flex items-start justify-between gap-3">
                        <p class="text-xs font-semibold uppercase tracking-wider text-rose-700">Réclamations</p>
                        <span class="inline-flex h-9 w-9 items-center justify-center rounded-lg bg-white/80 text-rose-700">
                            <svg class="h-4 w-4" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true"><path d="M12 9v4"></path><path d="M12 17h.01"></path><path d="M10.29 3.86 1.82 18a2 2 0 0 0 1.72 3h16.92a2 2 0 0 0 1.72-3L13.71 3.86a2 2 0 0 0-3.42 0Z"></path></svg>
                        </span>
                    </div>
                    <p class="mt-3 text-3xl font-bold text-rose-900">{{ $counts['reclamations_open'] }}</p>
                    <p class="mt-1 text-xs text-rose-800/80">Réclamations en attente de traitement.</p>
                </article>
            </section>

            <section class="grid grid-cols-1 gap-6 xl:grid-cols-3">
                <article class="rounded-2xl border border-slate-200 bg-white p-6 shadow-sm xl:col-span-2">
                    <h3 class="text-lg font-semibold text-slate-900">Actions rapides</h3>
                    <p class="mt-1 text-sm text-slate-500">Acces direct aux operations les plus frequentes.</p>

                    <div class="mt-4 flex flex-wrap items-center gap-3">
                        @can('manage-missions')
                            <a href="{{ route('missions.create') }}" class="inline-flex items-center gap-2 rounded-xl bg-slate-900 px-4 py-2 text-sm font-semibold text-white transition hover:bg-slate-800">
                                <svg class="h-4 w-4" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true"><path d="M12 5v14"></path><path d="M5 12h14"></path></svg>
                                <span>Créer une mission</span>
                            </a>
                        @endcan
                        <a href="{{ route('missions.index') }}" class="inline-flex items-center gap-2 rounded-xl border border-slate-300 bg-white px-4 py-2 text-sm font-semibold text-slate-700 transition hover:bg-slate-50">
                            <svg class="h-4 w-4" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true"><rect x="3" y="4" width="18" height="16" rx="2"></rect><path d="M7 8h10"></path><path d="M7 12h10"></path><path d="M7 16h6"></path></svg>
                            <span>Missions</span>
                        </a>
                        <a href="{{ route('admin.techniciens.index') }}" class="inline-flex items-center gap-2 rounded-xl border border-slate-300 bg-white px-4 py-2 text-sm font-semibold text-slate-700 transition hover:bg-slate-50">
                            <svg class="h-4 w-4" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true"><circle cx="8" cy="8" r="3"></circle><circle cx="17" cy="8" r="3"></circle><path d="M2 20a6 6 0 0 1 12 0"></path><path d="M12 20a6 6 0 0 1 10 0"></path></svg>
                            <span>Gérer les techniciens</span>
                        </a>
                        @can('manage-references')
                            <a href="{{ route('reference-points.create') }}" class="inline-flex items-center gap-2 rounded-xl bg-emerald-600 px-4 py-2 text-sm font-semibold text-white transition hover:bg-emerald-700">
                                <svg class="h-4 w-4" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true"><path d="M12 2s7 4.5 7 10a7 7 0 0 1-14 0c0-5.5 7-10 7-10Z"></path><circle cx="12" cy="12" r="2.8"></circle></svg>
                                <span>Ajouter une référence</span>
                            </a>
                        @endcan
                    </div>

                    <div class="mt-5 grid grid-cols-1 gap-3 sm:grid-cols-2">
                        <div class="rounded-xl border border-slate-200 bg-slate-50 px-4 py-3">
                            <p class="text-xs font-semibold uppercase tracking-wider text-slate-500">Couverture d'affectation</p>
                            <p class="mt-2 text-2xl font-bold text-slate-900">{{ $assignmentRate }}%</p>
                            <p class="mt-1 text-xs text-slate-500">Part des missions deja affectees.</p>
                        </div>
                        <div class="rounded-xl border border-slate-200 bg-slate-50 px-4 py-3">
                            <p class="text-xs font-semibold uppercase tracking-wider text-slate-500">Charge moyenne</p>
                            <p class="mt-2 text-2xl font-bold text-slate-900">
                                {{ $counts['techniciens'] > 0 ? round($counts['missions'] / $counts['techniciens'], 1) : 0 }}
                            </p>
                            <p class="mt-1 text-xs text-slate-500">Missions par technicien.</p>
                        </div>
                    </div>
                </article>

                <article class="rounded-2xl border border-slate-200 bg-white p-6 shadow-sm">
                    <h3 class="text-lg font-semibold text-slate-900">Etat du systeme</h3>
                    <p class="mt-1 text-sm text-slate-500">Priorites immediates pour le pilotage.</p>

                    <div class="mt-4 space-y-3">
                        <div class="rounded-xl border border-amber-200 bg-amber-50 px-4 py-3">
                            <p class="text-xs font-semibold uppercase tracking-wider text-amber-700">A traiter maintenant</p>
                            <p class="mt-1 text-lg font-bold text-amber-900">{{ $counts['missions_unassigned'] }} missions</p>
                        </div>
                        <div class="rounded-xl border border-slate-200 bg-slate-50 px-4 py-3">
                            <p class="text-xs font-semibold uppercase tracking-wider text-slate-500">Techniciens disponibles</p>
                            <p class="mt-1 text-lg font-bold text-slate-900">{{ $counts['techniciens'] }}</p>
                        </div>
                        <div class="rounded-xl border border-slate-200 bg-white px-4 py-4">
                            <p class="text-xs font-semibold uppercase tracking-wider text-slate-500">Répartition missions</p>
                            <div class="mt-3 flex items-center gap-4">
                                <div class="h-16 w-16 rounded-full" style="background: conic-gradient(#0f766e {{ $assignmentRate }}%, #f59e0b 0);"></div>
                                <div class="flex-1 space-y-2">
                                    <div>
                                        <div class="mb-1 flex items-center justify-between text-[11px] font-semibold uppercase tracking-wider text-slate-500">
                                            <span>Affectées</span>
                                            <span>{{ $assignedMissions }}</span>
                                        </div>
                                        <div class="h-2 overflow-hidden rounded-full bg-slate-100">
                                            <div class="h-full rounded-full bg-emerald-600" style="width: {{ $assignmentRate }}%"></div>
                                        </div>
                                    </div>
                                    <div>
                                        <div class="mb-1 flex items-center justify-between text-[11px] font-semibold uppercase tracking-wider text-slate-500">
                                            <span>Non affectées</span>
                                            <span>{{ $counts['missions_unassigned'] }}</span>
                                        </div>
                                        <div class="h-2 overflow-hidden rounded-full bg-slate-100">
                                            <div class="h-full rounded-full bg-amber-500" style="width: {{ $unassignedRate }}%"></div>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                        <a href="{{ route('admin.analysis') }}" class="inline-flex w-full items-center justify-center rounded-xl bg-indigo-600 px-4 py-2 text-sm font-semibold text-white transition hover:bg-indigo-700">Ouvrir l'analyse complete</a>
                    </div>
                </article>
            </section>

            <section class="rounded-2xl border border-slate-200 bg-white p-6 shadow-sm">
                <div class="flex items-center justify-between">
                    <div>
                        <h3 class="text-lg font-semibold text-slate-900">Affecter une mission (non affectees)</h3>
                        <p class="mt-1 text-sm text-slate-500">Traiter rapidement les missions en attente d'attribution.</p>
                    </div>
                    <a class="text-sm font-semibold text-slate-600 transition hover:text-slate-900" href="{{ route('missions.index') }}">Voir tout</a>
                </div>

                <div class="mt-4 overflow-x-auto">
                    <table class="min-w-full divide-y divide-slate-200">
                        <thead class="bg-slate-50">
                            <tr>
                                <th class="px-4 py-3 text-left text-xs font-semibold uppercase tracking-wider text-slate-500">Mission</th>
                                <th class="px-4 py-3 text-left text-xs font-semibold uppercase tracking-wider text-slate-500">Référence</th>
                                <th class="px-4 py-3 text-left text-xs font-semibold uppercase tracking-wider text-slate-500">Créée par</th>
                                <th class="px-4 py-3 text-left text-xs font-semibold uppercase tracking-wider text-slate-500">Affectation</th>
                            </tr>
                        </thead>
                        <tbody class="divide-y divide-slate-100 bg-white">
                            @forelse ($unassignedMissions as $m)
                                <tr>
                                    <td class="px-4 py-3 whitespace-nowrap">
                                        <a class="font-medium text-indigo-600 transition hover:text-indigo-800" href="{{ route('missions.show', $m) }}">#{{ $m->id }}</a>
                                        <div class="text-xs text-slate-500">{{ $m->type_mission }} • {{ $m->priorite }}</div>
                                    </td>
                                    <td class="px-4 py-3 whitespace-nowrap text-sm text-slate-700">{{ $m->referencePoint?->reference ?? '—' }}</td>
                                    <td class="px-4 py-3 whitespace-nowrap text-sm text-slate-700">{{ $m->creator?->name ?? '—' }}</td>
                                    <td class="px-4 py-3">
                                        <form method="POST" action="{{ route('admin.missions.assign', $m) }}" class="flex items-center gap-2">
                                            @csrf
                                            <select name="technicien_id" class="rounded-lg border-slate-300 text-sm shadow-sm focus:border-indigo-500 focus:ring-indigo-500" required>
                                                <option value="" disabled selected>Choisir...</option>
                                                @foreach ($techniciens as $t)
                                                    <option value="{{ $t->id }}">{{ $t->prenom }} {{ $t->nom }}</option>
                                                @endforeach
                                            </select>
                                            <button type="submit" class="inline-flex items-center rounded-lg bg-slate-900 px-3 py-2 text-xs font-semibold uppercase tracking-wider text-white transition hover:bg-slate-800">Affecter</button>
                                        </form>
                                    </td>
                                </tr>
                            @empty
                                <tr>
                                    <td colspan="4" class="px-4 py-10 text-center">
                                        <p class="text-sm font-semibold text-slate-700">Aucune mission non affectee.</p>
                                        <p class="mt-1 text-xs text-slate-500">Bonne nouvelle: toutes les missions sont attribuees.</p>
                                        <a href="{{ route('missions.create') }}" class="mt-3 inline-flex items-center rounded-lg bg-emerald-600 px-3 py-2 text-xs font-semibold uppercase tracking-wider text-white transition hover:bg-emerald-700">Creer une nouvelle mission</a>
                                    </td>
                                </tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>
            </section>

            <section class="rounded-2xl border border-slate-200 bg-white p-6 shadow-sm">
                <div class="flex items-center justify-between">
                    <div>
                        <h3 class="text-lg font-semibold text-slate-900">Réclamations clients</h3>
                        <p class="mt-1 text-sm text-slate-500">Transformer une réclamation en mission et l’envoyer à un technicien.</p>
                    </div>
                </div>

                <div class="mt-4 overflow-x-auto">
                    <table class="min-w-full divide-y divide-slate-200">
                        <thead class="bg-slate-50">
                            <tr>
                                <th class="px-4 py-3 text-left text-xs font-semibold uppercase tracking-wider text-slate-500">Client</th>
                                <th class="px-4 py-3 text-left text-xs font-semibold uppercase tracking-wider text-slate-500">Référence compteur</th>
                                <th class="px-4 py-3 text-left text-xs font-semibold uppercase tracking-wider text-slate-500">Objet</th>
                                <th class="px-4 py-3 text-left text-xs font-semibold uppercase tracking-wider text-slate-500">Statut</th>
                                <th class="px-4 py-3 text-left text-xs font-semibold uppercase tracking-wider text-slate-500">Affectation</th>
                            </tr>
                        </thead>
                        <tbody class="divide-y divide-slate-100 bg-white">
                            @forelse ($reclamations as $reclamation)
                                <tr>
                                    <td class="px-4 py-3 whitespace-nowrap text-sm text-slate-700">
                                        <div class="font-medium text-slate-900">{{ $reclamation->client?->name ?? '—' }}</div>
                                        <div class="text-xs text-slate-500">{{ $reclamation->client?->email ?? '' }}</div>
                                    </td>
                                    <td class="px-4 py-3 whitespace-nowrap text-sm text-slate-700">{{ $reclamation->compteur_reference }}</td>
                                    <td class="px-4 py-3 text-sm text-slate-700">
                                        <div class="font-medium text-slate-900">{{ $reclamation->subject }}</div>
                                        <div class="text-xs text-slate-500">{{ \Illuminate\Support\Str::limit($reclamation->description, 90) }}</div>
                                    </td>
                                    <td class="px-4 py-3 whitespace-nowrap text-sm text-slate-700">
                                        <span class="inline-flex rounded-full px-3 py-1 text-xs font-semibold @if($reclamation->status === 'Transmise') bg-emerald-100 text-emerald-800 @elseif($reclamation->status === 'Nouveau') bg-amber-100 text-amber-800 @else bg-slate-100 text-slate-700 @endif">
                                            {{ $reclamation->status }}
                                        </span>
                                    </td>
                                    <td class="px-4 py-3">
                                        @if ($reclamation->mission_id)
                                            <span class="text-sm font-medium text-emerald-700">Mission #{{ $reclamation->mission_id }}</span>
                                        @else
                                            <div x-data="{ open: false }" class="inline-block">
                                                <button
                                                    type="button"
                                                    class="inline-flex items-center rounded-lg bg-rose-600 px-3 py-2 text-xs font-semibold uppercase tracking-wider text-white transition hover:bg-rose-700"
                                                    @click="open = true"
                                                >
                                                    Préparer mission
                                                </button>

                                                <div
                                                    x-show="open"
                                                    x-transition.opacity
                                                    class="fixed inset-0 z-40 bg-slate-950/60"
                                                    @click="open = false"
                                                ></div>

                                                <div
                                                    x-show="open"
                                                    x-transition
                                                    class="fixed inset-0 z-50 flex items-center justify-center p-4"
                                                    @keydown.escape.window="open = false"
                                                >
                                                    <div class="w-full max-w-2xl rounded-2xl border border-slate-200 bg-white shadow-xl" @click.stop>
                                                        <div class="flex items-start justify-between border-b border-slate-200 px-5 py-4">
                                                            <div>
                                                                <h4 class="text-base font-semibold text-slate-900">Créer une mission depuis la réclamation</h4>
                                                                <p class="mt-1 text-xs text-slate-500">Référence {{ $reclamation->compteur_reference }} • {{ $reclamation->client?->name ?? 'Client' }}</p>
                                                            </div>
                                                            <button type="button" class="rounded-md border border-slate-300 px-2 py-1 text-xs font-semibold text-slate-600 hover:bg-slate-50" @click="open = false">Fermer</button>
                                                        </div>

                                                        <form
                                                            method="POST"
                                                            action="{{ route('admin.reclamations.assign', $reclamation) }}"
                                                            class="space-y-4 px-5 py-4"
                                                            onsubmit="return confirm('Confirmer la création de mission et l\'affectation du technicien ?');"
                                                        >
                                                            @csrf

                                                            <div class="grid grid-cols-1 gap-4 sm:grid-cols-2">
                                                                <div>
                                                                    <label class="mb-1 block text-xs font-semibold uppercase tracking-wider text-slate-500">Technicien</label>
                                                                    <select name="technicien_id" class="w-full rounded-lg border-slate-300 text-sm shadow-sm focus:border-indigo-500 focus:ring-indigo-500" required>
                                                                        <option value="" disabled selected>Choisir...</option>
                                                                        @foreach ($techniciens as $t)
                                                                            <option value="{{ $t->id }}">{{ $t->prenom }} {{ $t->nom }}</option>
                                                                        @endforeach
                                                                    </select>
                                                                </div>

                                                                <div>
                                                                    <label class="mb-1 block text-xs font-semibold uppercase tracking-wider text-slate-500">Type mission</label>
                                                                    <select name="type_mission" class="w-full rounded-lg border-slate-300 text-sm shadow-sm focus:border-indigo-500 focus:ring-indigo-500" required>
                                                                        <option value="Réparation" selected>Réparation</option>
                                                                        <option value="Maintenance">Maintenance</option>
                                                                        <option value="Inspection">Inspection</option>
                                                                        <option value="Diagnostic">Diagnostic</option>
                                                                    </select>
                                                                </div>

                                                                <div>
                                                                    <label class="mb-1 block text-xs font-semibold uppercase tracking-wider text-slate-500">Priorité</label>
                                                                    <select name="priorite" class="w-full rounded-lg border-slate-300 text-sm shadow-sm focus:border-indigo-500 focus:ring-indigo-500" required>
                                                                        <option value="Basse">Basse</option>
                                                                        <option value="Moyenne">Moyenne</option>
                                                                        <option value="Haute" selected>Haute</option>
                                                                        <option value="Urgente">Urgente</option>
                                                                    </select>
                                                                </div>

                                                                <div>
                                                                    <label class="mb-1 block text-xs font-semibold uppercase tracking-wider text-slate-500">Objet réclamation</label>
                                                                    <p class="rounded-lg border border-slate-200 bg-slate-50 px-3 py-2 text-sm text-slate-700">{{ $reclamation->subject }}</p>
                                                                </div>
                                                            </div>

                                                            <div class="grid grid-cols-1 gap-4 sm:grid-cols-2">
                                                                <div>
                                                                    <label class="mb-1 block text-xs font-semibold uppercase tracking-wider text-slate-500">Planifier (date & heure)</label>
                                                                    <input type="datetime-local" name="due_at" class="w-full rounded-lg border-slate-300 bg-white px-3 py-2 text-sm shadow-sm focus:border-indigo-500 focus:ring-indigo-500" />
                                                                </div>

                                                                <div>
                                                                    <label class="mb-1 block text-xs font-semibold uppercase tracking-wider text-slate-500">Fuseau horaire</label>
                                                                    <div class="text-sm text-slate-500">Heure serveur: {{ now()->format('Y-m-d H:i') }}</div>
                                                                </div>
                                                            </div>

                                                            <div>
                                                                <label class="mb-1 block text-xs font-semibold uppercase tracking-wider text-slate-500">Note admin (optionnel)</label>
                                                                <textarea name="admin_note" rows="3" class="w-full rounded-lg border-slate-300 text-sm shadow-sm focus:border-indigo-500 focus:ring-indigo-500" placeholder="Instructions supplémentaires pour le technicien..."></textarea>
                                                            </div>

                                                            <div class="flex items-center justify-end gap-2 border-t border-slate-100 pt-3">
                                                                <button type="button" class="rounded-lg border border-slate-300 px-3 py-2 text-xs font-semibold uppercase tracking-wider text-slate-700 hover:bg-slate-50" @click="open = false">Annuler</button>
                                                                <button type="submit" class="inline-flex items-center rounded-lg bg-rose-600 px-3 py-2 text-xs font-semibold uppercase tracking-wider text-white transition hover:bg-rose-700">Confirmer et créer mission</button>
                                                            </div>
                                                        </form>
                                                    </div>
                                                </div>
                                            </div>
                                        @endif
                                    </td>
                                </tr>
                            @empty
                                <tr>
                                    <td colspan="5" class="px-4 py-10 text-center">
                                        <p class="text-sm font-semibold text-slate-700">Aucune réclamation pour le moment.</p>
                                        <p class="mt-1 text-xs text-slate-500">Les demandes des clients apparaîtront ici dès leur soumission.</p>
                                    </td>
                                </tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>
            </section>
        </div>
    </div>
</x-app-layout>
