<x-app-layout>
    <x-slot name="header">
        <div class="rounded-2xl bg-gradient-to-r from-slate-950 via-slate-800 to-cyan-700 px-6 py-5 text-white shadow-lg">
            <p class="text-xs font-semibold uppercase tracking-[0.3em] text-cyan-100">Client Portal</p>
            <h2 class="mt-2 text-2xl font-bold">Suivi des demandes et retours</h2>
            <p class="mt-2 max-w-3xl text-sm text-cyan-100/90">Vue client pour suivre les missions, noter les interventions, et retrouver l’historique de service.</p>
        </div>
    </x-slot>

    <div class="py-10">
        <div class="mx-auto max-w-7xl space-y-6 px-4 sm:px-6 lg:px-8">
            @if (session('status'))
                <div class="rounded-2xl border border-emerald-200 bg-emerald-50 px-4 py-3 text-sm font-medium text-emerald-800">
                    {{ session('status') }}
                </div>
            @endif

            <section class="grid gap-4 sm:grid-cols-2 xl:grid-cols-6">
                <article class="panel-card p-5">
                    <p class="text-xs font-semibold uppercase tracking-wider text-slate-500">Missions</p>
                    <p class="mt-2 text-3xl font-bold text-slate-900">{{ $stats['total'] }}</p>
                </article>
                <article class="panel-card p-5">
                    <p class="text-xs font-semibold uppercase tracking-wider text-emerald-600">Terminees</p>
                    <p class="mt-2 text-3xl font-bold text-emerald-700">{{ $stats['completed'] }}</p>
                </article>
                <article class="panel-card p-5">
                    <p class="text-xs font-semibold uppercase tracking-wider text-sky-600">En cours</p>
                    <p class="mt-2 text-3xl font-bold text-sky-700">{{ $stats['in_progress'] }}</p>
                </article>
                <article class="panel-card p-5">
                    <p class="text-xs font-semibold uppercase tracking-wider text-amber-600">Bloquees</p>
                    <p class="mt-2 text-3xl font-bold text-amber-700">{{ $stats['blocked'] }}</p>
                </article>
                <article class="panel-card p-5">
                    <p class="text-xs font-semibold uppercase tracking-wider text-rose-600">Réclamations</p>
                    <p class="mt-2 text-3xl font-bold text-rose-700">{{ $stats['reclamations_total'] }}</p>
                </article>
                <article class="panel-card p-5">
                    <p class="text-xs font-semibold uppercase tracking-wider text-violet-600">Note moyenne</p>
                    <p class="mt-2 text-3xl font-bold text-violet-700">{{ number_format($stats['avg_rating'], 1) }}/5</p>
                </article>
            </section>

            <section class="grid gap-6 xl:grid-cols-12">
                <article class="panel-card xl:col-span-7">
                    <div class="panel-head">
                        <h3>Déposer une réclamation</h3>
                        <p>Indiquez la référence de votre compteur, puis décrivez le problème constaté.</p>
                    </div>

                    <form method="POST" action="{{ route('client.reclamations.store') }}" class="space-y-4 p-5">
                        @csrf

                        <div>
                            <label for="compteur_reference" class="mb-1 block text-xs font-semibold uppercase tracking-wider text-slate-500">Référence compteur</label>
                            <input
                                id="compteur_reference"
                                name="compteur_reference"
                                type="text"
                                value="{{ old('compteur_reference') }}"
                                class="w-full rounded-xl border-slate-300 bg-white px-3 py-2 text-sm focus:border-cyan-500 focus:ring-cyan-500"
                                placeholder="Ex: CP-000123"
                                required
                            />
                            @error('compteur_reference')
                                <p class="mt-1 text-xs font-medium text-rose-600">{{ $message }}</p>
                            @enderror
                        </div>

                        <div>
                            <label for="subject" class="mb-1 block text-xs font-semibold uppercase tracking-wider text-slate-500">Objet</label>
                            <input
                                id="subject"
                                name="subject"
                                type="text"
                                value="{{ old('subject') }}"
                                class="w-full rounded-xl border-slate-300 bg-white px-3 py-2 text-sm focus:border-cyan-500 focus:ring-cyan-500"
                                placeholder="Panne du compteur, coupure, fuite, etc."
                                required
                            />
                            @error('subject')
                                <p class="mt-1 text-xs font-medium text-rose-600">{{ $message }}</p>
                            @enderror
                        </div>

                        <div>
                            <label for="reclamation_description" class="mb-1 block text-xs font-semibold uppercase tracking-wider text-slate-500">Description</label>
                            <textarea
                                id="reclamation_description"
                                name="description"
                                rows="5"
                                class="w-full rounded-xl border-slate-300 bg-white px-3 py-2 text-sm focus:border-cyan-500 focus:ring-cyan-500"
                                placeholder="Expliquez le problème, l’heure, les symptômes, et toute précision utile."
                                required
                            >{{ old('description') }}</textarea>
                            @error('description')
                                <p class="mt-1 text-xs font-medium text-rose-600">{{ $message }}</p>
                            @enderror
                        </div>

                        <div class="flex items-center justify-end gap-3 border-t border-slate-200 pt-4">
                            <button type="submit" class="rounded-xl bg-rose-600 px-4 py-2 text-sm font-semibold text-white transition hover:bg-rose-700">Envoyer la réclamation</button>
                        </div>
                    </form>
                </article>

                <article class="panel-card xl:col-span-5">
                    <div class="panel-head">
                        <h3>Mes réclamations</h3>
                        <p>Suivi de l’état de traitement et des missions créées.</p>
                    </div>

                    <div class="overflow-x-auto">
                        <table class="min-w-full divide-y divide-slate-200">
                            <thead class="bg-slate-50">
                                <tr>
                                    <th class="px-4 py-3 text-left text-xs font-semibold uppercase tracking-wider text-slate-500">Objet</th>
                                    <th class="px-4 py-3 text-left text-xs font-semibold uppercase tracking-wider text-slate-500">Statut</th>
                                    <th class="px-4 py-3 text-left text-xs font-semibold uppercase tracking-wider text-slate-500">Mission</th>
                                </tr>
                            </thead>
                            <tbody class="divide-y divide-slate-200 bg-white">
                                @forelse ($reclamations as $reclamation)
                                    <tr>
                                        <td class="px-4 py-3">
                                            <div class="text-sm font-semibold text-slate-900">{{ $reclamation->subject }}</div>
                                            <div class="text-xs text-slate-500">{{ $reclamation->compteur_reference }}</div>
                                        </td>
                                        <td class="px-4 py-3 text-sm text-slate-700">{{ $reclamation->status }}</td>
                                        <td class="px-4 py-3 text-sm text-slate-700">
                                            @if ($reclamation->mission)
                                                #{{ $reclamation->mission->id }} - {{ $reclamation->mission->statut }}
                                            @else
                                                —
                                            @endif
                                        </td>
                                    </tr>
                                @empty
                                    <tr>
                                        <td colspan="3" class="px-4 py-8 text-center text-sm text-slate-500">Aucune réclamation enregistrée.</td>
                                    </tr>
                                @endforelse
                            </tbody>
                        </table>
                    </div>
                </article>
            </section>

            <section class="grid gap-6 xl:grid-cols-12">
                <article class="panel-card xl:col-span-7">
                    <div class="panel-head">
                        <h3>Dernieres missions</h3>
                        <p>Statut, reference, technicien et derniere note client.</p>
                    </div>
                    <div class="overflow-x-auto">
                        <table class="min-w-full divide-y divide-slate-200">
                            <thead class="bg-slate-50">
                                <tr>
                                    <th class="px-4 py-3 text-left text-xs font-semibold uppercase tracking-wider text-slate-500">Mission</th>
                                    <th class="px-4 py-3 text-left text-xs font-semibold uppercase tracking-wider text-slate-500">Reference</th>
                                    <th class="px-4 py-3 text-left text-xs font-semibold uppercase tracking-wider text-slate-500">Statut</th>
                                    <th class="px-4 py-3 text-left text-xs font-semibold uppercase tracking-wider text-slate-500">Technicien</th>
                                    <th class="px-4 py-3 text-left text-xs font-semibold uppercase tracking-wider text-slate-500">Feedback</th>
                                </tr>
                            </thead>
                            <tbody class="divide-y divide-slate-200 bg-white">
                                @forelse ($missions as $mission)
                                    <tr>
                                        <td class="px-4 py-3 text-sm font-semibold text-slate-900">#{{ $mission->id }}</td>
                                        <td class="px-4 py-3 text-sm text-slate-700">{{ $mission->referencePoint?->reference ?? '—' }}</td>
                                        <td class="px-4 py-3 text-sm text-slate-700">{{ $mission->statut }}</td>
                                        <td class="px-4 py-3 text-sm text-slate-700">
                                            {{ trim(($mission->currentAffectation?->technicien?->prenom ?? '') . ' ' . ($mission->currentAffectation?->technicien?->nom ?? '')) ?: ($mission->currentAffectation?->technicien?->user?->name ?? '—') }}
                                        </td>
                                        <td class="px-4 py-3 text-sm text-slate-700">
                                            @if ($mission->clientFeedback)
                                                <div class="font-semibold text-slate-900">{{ $mission->clientFeedback->rating }}/5</div>
                                                <div class="text-xs text-slate-500">{{ $mission->clientFeedback->comment ?? 'Sans commentaire' }}</div>
                                            @else
                                                —
                                            @endif
                                        </td>
                                    </tr>
                                @empty
                                    <tr>
                                        <td colspan="5" class="px-4 py-8 text-center text-sm text-slate-500">Aucune mission disponible pour ce client.</td>
                                    </tr>
                                @endforelse
                            </tbody>
                        </table>
                    </div>
                </article>

                <article class="panel-card xl:col-span-5">
                    <div class="panel-head">
                        <h3>Votre entreprise</h3>
                        <p>Données de référence pour le suivi contractuel.</p>
                    </div>
                    <div class="space-y-4 p-5 text-sm text-slate-700">
                        <div>
                            <p class="text-xs uppercase tracking-wider text-slate-500">Client</p>
                            <p class="mt-1 font-semibold text-slate-900">{{ $client->name }}</p>
                        </div>
                        <div>
                            <p class="text-xs uppercase tracking-wider text-slate-500">Email</p>
                            <p class="mt-1">{{ $client->email ?? auth()->user()->email }}</p>
                        </div>
                        <div>
                            <p class="text-xs uppercase tracking-wider text-slate-500">Entreprise</p>
                            <p class="mt-1">{{ $client->company ?? '—' }}</p>
                        </div>
                        <div>
                            <p class="text-xs uppercase tracking-wider text-slate-500">Niveau SLA</p>
                            <p class="mt-1 inline-flex rounded-full bg-cyan-100 px-3 py-1 text-xs font-semibold text-cyan-800">{{ $client->sla_level }}</p>
                        </div>
                    </div>
                </article>
            </section>

            <section class="panel-card">
                <div class="panel-head">
                    <h3>Ajouter un retour client</h3>
                    <p>Noter une mission terminee et ajouter un commentaire.</p>
                </div>
                <form method="POST" action="{{ route('client.feedback.store') }}" class="grid gap-4 p-5 md:grid-cols-4">
                    @csrf
                    <label class="md:col-span-2">
                        <span class="mb-1 block text-xs font-semibold uppercase tracking-wider text-slate-500">Mission</span>
                        <select name="mission_id" class="w-full rounded-xl border-slate-300 bg-white px-3 py-2 text-sm focus:border-cyan-500 focus:ring-cyan-500">
                            @foreach ($missions as $mission)
                                <option value="{{ $mission->id }}">#{{ $mission->id }} - {{ $mission->referencePoint?->reference ?? 'Reference' }}</option>
                            @endforeach
                        </select>
                    </label>
                    <label>
                        <span class="mb-1 block text-xs font-semibold uppercase tracking-wider text-slate-500">Note</span>
                        <select name="rating" class="w-full rounded-xl border-slate-300 bg-white px-3 py-2 text-sm focus:border-cyan-500 focus:ring-cyan-500">
                            @for ($rating = 1; $rating <= 5; $rating++)
                                <option value="{{ $rating }}">{{ $rating }}/5</option>
                            @endfor
                        </select>
                    </label>
                    <label class="md:col-span-4">
                        <span class="mb-1 block text-xs font-semibold uppercase tracking-wider text-slate-500">Commentaire</span>
                        <textarea name="comment" rows="4" class="w-full rounded-xl border-slate-300 bg-white px-3 py-2 text-sm focus:border-cyan-500 focus:ring-cyan-500" placeholder="Dites-nous ce qui a bien fonctionné ou ce qui peut être amélioré."></textarea>
                    </label>
                    <div class="md:col-span-4">
                        <button type="submit" class="rounded-xl bg-slate-950 px-4 py-2 text-sm font-semibold text-white transition hover:bg-slate-800">Enregistrer le retour</button>
                    </div>
                </form>
            </section>
        </div>
    </div>
</x-app-layout>
