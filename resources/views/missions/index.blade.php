<x-app-layout>
    <x-slot name="header">
        <div class="flex flex-col gap-4 md:flex-row md:items-center md:justify-between">
            <div>
                <p class="text-xs font-semibold uppercase tracking-[0.22em] text-slate-500">Pilotage opérationnel</p>
                <h2 class="mt-1 text-2xl font-bold leading-tight text-slate-900">
                    {{ __('Missions') }}
                </h2>
            </div>

            <div class="flex items-center gap-3">
                <span class="rounded-full border border-sky-200 bg-sky-50 px-3 py-1 text-xs font-semibold text-sky-700">
                    Total: {{ $missions->total() }}
                </span>

                @if (auth()->user()?->role === 'Technicien')
                    <a href="{{ route('missions.index', ['mine' => 1]) }}" class="inline-flex items-center rounded-xl border border-slate-300 bg-white px-4 py-2 text-xs font-semibold uppercase tracking-wider text-slate-700 transition hover:bg-slate-50">
                        Historique pour moi
                    </a>

                    <a href="{{ route('missions.analysis') }}" class="inline-flex items-center rounded-xl border border-amber-300 bg-amber-50 px-4 py-2 text-xs font-semibold uppercase tracking-wider text-amber-700 transition hover:bg-amber-100">
                        Analyse de travail
                    </a>
                @endif

                @can('manage-missions')
                    <a href="{{ route('missions.create') }}" class="inline-flex items-center rounded-xl bg-brand-primary px-4 py-2 text-xs font-semibold uppercase tracking-widest text-white shadow-sm transition hover:bg-brand-primary-dark focus:outline-none focus:ring-2 focus:ring-brand-primary focus:ring-offset-2">
                        {{ __('Créer une mission') }}
                    </a>
                @endcan
            </div>
        </div>
    </x-slot>

    <div class="py-12">
        <div class="max-w-7xl mx-auto sm:px-6 lg:px-8">
            <div class="brand-card overflow-hidden">
                <div class="p-6 text-slate-900">
                    @if (!empty($referenceId))
                        <div class="mb-4 flex items-center justify-between rounded-xl border border-sky-200 bg-sky-50 px-4 py-3 text-sky-800">
                            <div>
                                {{ ($onlyMine ?? false) ? 'Historique de vos missions pour la référence sélectionnée.' : 'Historique des missions pour la référence sélectionnée.' }}
                            </div>
                            <a class="text-sm underline" href="{{ route('missions.index') }}">Afficher toutes</a>
                        </div>
                    @endif

                    @if (session('status'))
                        <div class="mb-4 rounded-xl border border-emerald-200 bg-emerald-50 px-4 py-3 text-emerald-800">
                            {{ session('status') }}
                        </div>
                    @endif

                    <div class="mb-5 grid grid-cols-1 gap-3 sm:grid-cols-3">
                        <div class="rounded-xl border border-slate-200 bg-slate-50 px-4 py-3">
                            <div class="text-xs uppercase tracking-wider text-slate-500">En cours</div>
                            <div class="mt-1 text-xl font-semibold text-slate-900">{{ $missions->where('statut', 'En cours')->count() }}</div>
                        </div>
                        <div class="rounded-xl border border-amber-200 bg-amber-50 px-4 py-3">
                            <div class="text-xs uppercase tracking-wider text-amber-700">Bloquées</div>
                            <div class="mt-1 text-xl font-semibold text-amber-800">{{ $missions->where('statut', 'Bloquée')->count() }}</div>
                        </div>
                        <div class="rounded-xl border border-emerald-200 bg-emerald-50 px-4 py-3">
                            <div class="text-xs uppercase tracking-wider text-emerald-700">Terminées</div>
                            <div class="mt-1 text-xl font-semibold text-emerald-800">{{ $missions->where('statut', 'Terminée')->count() }}</div>
                        </div>
                    </div>

                    <div class="overflow-x-auto rounded-xl border border-slate-200">
                        <table class="min-w-full divide-y divide-slate-200">
                            <thead class="bg-slate-50">
                                <tr>
                                    <th class="px-4 py-3 text-left text-xs font-semibold uppercase tracking-wider text-slate-500">Référence</th>
                                    <th class="px-4 py-3 text-left text-xs font-semibold uppercase tracking-wider text-slate-500">Type</th>
                                    <th class="px-4 py-3 text-left text-xs font-semibold uppercase tracking-wider text-slate-500">Priorité</th>
                                    <th class="px-4 py-3 text-left text-xs font-semibold uppercase tracking-wider text-slate-500">Statut</th>
                                    <th class="px-4 py-3 text-left text-xs font-semibold uppercase tracking-wider text-slate-500">Technicien</th>
                                    <th class="px-4 py-3 text-left text-xs font-semibold uppercase tracking-wider text-slate-500">Échéance</th>
                                    <th class="px-4 py-3 text-right text-xs font-semibold uppercase tracking-wider text-slate-500">Actions</th>
                                </tr>
                            </thead>
                            <tbody class="divide-y divide-slate-100 bg-white">
                                @forelse ($missions as $mission)
                                    <tr class="hover:bg-slate-50/80">
                                        <td class="px-4 py-3 whitespace-nowrap">
                                            <a class="font-medium text-brand-primary hover:text-brand-primary-dark" href="{{ route('missions.show', $mission) }}">
                                                {{ $mission->referencePoint?->reference ?? '—' }}
                                            </a>
                                            <div class="text-xs text-slate-500">#{{ $mission->id }}</div>
                                        </td>
                                        <td class="px-4 py-3 whitespace-nowrap text-slate-700">{{ $mission->type_mission }}</td>
                                        <td class="px-4 py-3 whitespace-nowrap">
                                            <span class="rounded-full bg-slate-100 px-2.5 py-1 text-xs font-semibold text-slate-700">{{ $mission->priorite }}</span>
                                        </td>
                                        <td class="px-4 py-3 whitespace-nowrap">
                                            <span @class([
                                                'rounded-full px-2.5 py-1 text-xs font-semibold',
                                                'bg-emerald-100 text-emerald-800' => $mission->statut === 'Terminée',
                                                'bg-amber-100 text-amber-800' => $mission->statut === 'Bloquée',
                                                'bg-sky-100 text-sky-800' => $mission->statut === 'En cours',
                                                'bg-slate-100 text-slate-700' => !in_array($mission->statut, ['Terminée', 'Bloquée', 'En cours']),
                                            ])>
                                                {{ $mission->statut }}
                                            </span>
                                        </td>
                                        <td class="px-4 py-3 whitespace-nowrap">
                                            @php
                                                $aff = $mission->currentAffectation;
                                                $tech = $aff?->technicien;
                                            @endphp
                                            {{ $tech ? ($tech->prenom.' '.$tech->nom) : '—' }}
                                        </td>
                                        <td class="px-4 py-3 whitespace-nowrap text-sm text-slate-600">
                                            {{ $mission->due_at?->format('Y-m-d H:i') ?? '—' }}
                                        </td>
                                        <td class="space-x-2 whitespace-nowrap px-4 py-3 text-right">
                                            <a class="font-medium text-brand-primary hover:text-brand-primary-dark" href="{{ route('missions.show', $mission) }}">Voir</a>

                                            @can('manage-missions')
                                                <a class="text-slate-600 hover:text-slate-900" href="{{ route('missions.edit', $mission) }}">Modifier</a>

                                                <form class="inline" method="POST" action="{{ route('missions.destroy', $mission) }}" onsubmit="return confirm('Supprimer cette mission ?')">
                                                    @csrf
                                                    @method('DELETE')
                                                    <button type="submit" class="text-red-600 hover:text-red-800">Supprimer</button>
                                                </form>
                                            @endcan
                                        </td>
                                    </tr>
                                @empty
                                    <tr>
                                        <td colspan="7" class="px-4 py-10 text-center text-slate-500">
                                            Aucune mission.
                                        </td>
                                    </tr>
                                @endforelse
                            </tbody>
                        </table>
                    </div>

                    <div class="mt-6">
                        {{ $missions->links() }}
                    </div>
                </div>
            </div>
        </div>
    </div>
</x-app-layout>
