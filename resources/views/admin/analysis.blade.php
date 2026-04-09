<x-app-layout>
    <x-slot name="header">
        <div class="flex flex-col gap-3 md:flex-row md:items-center md:justify-between">
            <div>
                <p class="text-xs font-semibold uppercase tracking-[0.28em] text-slate-500">Espace administrateur</p>
                <h2 class="mt-2 text-2xl font-bold leading-tight text-slate-900">Analyse admin</h2>
                <p class="mt-2 text-sm text-slate-500">Vue globale des missions, techniciens et activite recente.</p>
            </div>

            <div class="flex items-center gap-3">
                <a href="{{ route('admin.dashboard') }}" class="inline-flex items-center rounded-xl border border-slate-300 bg-white px-4 py-2 text-sm font-semibold text-slate-700 hover:bg-slate-50">Retour dashboard</a>
                <a href="{{ route('missions.index') }}" class="inline-flex items-center rounded-xl bg-slate-900 px-4 py-2 text-sm font-semibold text-white hover:bg-slate-800">Voir les missions</a>
            </div>
        </div>
    </x-slot>

    <div class="py-10">
        <div class="mx-auto max-w-7xl space-y-6 sm:px-6 lg:px-8">
            <section class="grid grid-cols-1 gap-4 sm:grid-cols-2 xl:grid-cols-4">
                <article class="rounded-2xl bg-white p-6 shadow-sm ring-1 ring-slate-200">
                    <p class="text-xs font-semibold uppercase tracking-wider text-slate-500">Utilisateurs</p>
                    <p class="mt-3 text-3xl font-bold text-slate-900">{{ $counts['users'] }}</p>
                </article>
                <article class="rounded-2xl bg-white p-6 shadow-sm ring-1 ring-slate-200">
                    <p class="text-xs font-semibold uppercase tracking-wider text-slate-500">Techniciens</p>
                    <p class="mt-3 text-3xl font-bold text-slate-900">{{ $counts['techniciens'] }}</p>
                </article>
                <article class="rounded-2xl bg-white p-6 shadow-sm ring-1 ring-slate-200">
                    <p class="text-xs font-semibold uppercase tracking-wider text-slate-500">Missions</p>
                    <p class="mt-3 text-3xl font-bold text-slate-900">{{ $counts['missions'] }}</p>
                </article>
                <article class="rounded-2xl bg-white p-6 shadow-sm ring-1 ring-slate-200">
                    <p class="text-xs font-semibold uppercase tracking-wider text-slate-500">Non affectees</p>
                    <p class="mt-3 text-3xl font-bold text-slate-900">{{ $counts['missions_unassigned'] }}</p>
                </article>
            </section>

            <section class="grid grid-cols-1 gap-6 lg:grid-cols-3">
                <div class="rounded-2xl bg-white p-6 shadow-sm ring-1 ring-slate-200 lg:col-span-2">
                    <div class="flex items-center justify-between gap-3">
                        <div>
                            <h3 class="text-lg font-semibold text-slate-900">Repartition des missions</h3>
                            <p class="mt-1 text-sm text-slate-500">Status et priorite des missions en cours.</p>
                        </div>
                    </div>

                    <div class="mt-6 grid grid-cols-1 gap-6 md:grid-cols-2">
                        <div>
                            <h4 class="text-sm font-semibold uppercase tracking-wider text-slate-500">Par statut</h4>
                            <div class="mt-3 space-y-3">
                                @foreach ($statusBreakdown as $status => $total)
                                    <div class="rounded-xl bg-slate-50 px-4 py-3">
                                        <div class="flex items-center justify-between text-sm">
                                            <span class="font-medium text-slate-700">{{ $status }}</span>
                                            <span class="font-semibold text-slate-900">{{ $total }}</span>
                                        </div>
                                    </div>
                                @endforeach
                            </div>
                        </div>

                        <div>
                            <h4 class="text-sm font-semibold uppercase tracking-wider text-slate-500">Par priorite</h4>
                            <div class="mt-3 space-y-3">
                                @foreach ($priorityBreakdown as $priority => $total)
                                    <div class="rounded-xl bg-slate-50 px-4 py-3">
                                        <div class="flex items-center justify-between text-sm">
                                            <span class="font-medium text-slate-700">{{ $priority }}</span>
                                            <span class="font-semibold text-slate-900">{{ $total }}</span>
                                        </div>
                                    </div>
                                @endforeach
                            </div>
                        </div>
                    </div>
                </div>

                <div class="rounded-2xl bg-white p-6 shadow-sm ring-1 ring-slate-200">
                    <h3 class="text-lg font-semibold text-slate-900">Indicateurs rapides</h3>
                    <div class="mt-4 grid grid-cols-2 gap-3">
                        <article class="rounded-xl bg-emerald-50 p-4">
                            <p class="text-[11px] font-semibold uppercase tracking-wider text-emerald-700">Terminees</p>
                            <p class="mt-2 text-2xl font-bold text-emerald-900">{{ $counts['missions_completed'] }}</p>
                        </article>
                        <article class="rounded-xl bg-sky-50 p-4">
                            <p class="text-[11px] font-semibold uppercase tracking-wider text-sky-700">En cours</p>
                            <p class="mt-2 text-2xl font-bold text-sky-900">{{ $counts['missions_in_progress'] }}</p>
                        </article>
                        <article class="rounded-xl bg-amber-50 p-4">
                            <p class="text-[11px] font-semibold uppercase tracking-wider text-amber-700">Bloquees</p>
                            <p class="mt-2 text-2xl font-bold text-amber-900">{{ $counts['missions_blocked'] }}</p>
                        </article>
                        <article class="rounded-xl bg-violet-50 p-4">
                            <p class="text-[11px] font-semibold uppercase tracking-wider text-violet-700">Affectees</p>
                            <p class="mt-2 text-2xl font-bold text-violet-900">{{ $counts['missions_assigned'] }}</p>
                        </article>
                    </div>
                </div>
            </section>

            <section class="grid grid-cols-1 gap-6 lg:grid-cols-2">
                <div class="rounded-2xl bg-white p-6 shadow-sm ring-1 ring-slate-200">
                    <h3 class="text-lg font-semibold text-slate-900">Techniciens les plus actifs</h3>
                    <div class="mt-4 overflow-x-auto">
                        <table class="min-w-full divide-y divide-slate-200">
                            <thead>
                                <tr class="text-left text-xs font-semibold uppercase tracking-wider text-slate-500">
                                    <th class="px-3 py-2">Technicien</th>
                                    <th class="px-3 py-2">Missions</th>
                                </tr>
                            </thead>
                            <tbody class="divide-y divide-slate-100">
                                @forelse ($topTechniciens as $row)
                                    <tr>
                                        <td class="px-3 py-3 text-sm text-slate-800">{{ $row->technicien?->prenom }} {{ $row->technicien?->nom }}</td>
                                        <td class="px-3 py-3 text-sm font-semibold text-slate-900">{{ $row->total }}</td>
                                    </tr>
                                @empty
                                    <tr>
                                        <td colspan="2" class="px-3 py-6 text-center text-sm text-slate-500">Aucune affectation disponible.</td>
                                    </tr>
                                @endforelse
                            </tbody>
                        </table>
                    </div>
                </div>

                <div class="rounded-2xl bg-white p-6 shadow-sm ring-1 ring-slate-200">
                    <h3 class="text-lg font-semibold text-slate-900">Activite recente</h3>
                    <div class="mt-4 space-y-3">
                        @forelse ($recentAuditLogs as $log)
                            <div class="rounded-xl bg-slate-50 px-4 py-3">
                                <div class="flex items-center justify-between gap-3">
                                    <div>
                                        <p class="text-sm font-medium text-slate-800">{{ $log->description ?? 'Action' }}</p>
                                        <p class="mt-1 text-xs text-slate-500">{{ $log->user?->name ?? 'Système' }} • {{ $log->created_at?->format('Y-m-d H:i') }}</p>
                                    </div>
                                    <span class="rounded-full bg-white px-3 py-1 text-[11px] font-semibold uppercase tracking-wider text-slate-600">{{ $log->action }}</span>
                                </div>
                            </div>
                        @empty
                            <div class="rounded-xl border border-dashed border-slate-200 px-4 py-8 text-center text-sm text-slate-500">
                                Aucun historique recemment.
                            </div>
                        @endforelse
                    </div>
                </div>
            </section>

            <section class="rounded-2xl bg-white p-6 shadow-sm ring-1 ring-slate-200">
                <div class="flex items-center justify-between gap-3">
                    <div>
                        <h3 class="text-lg font-semibold text-slate-900">Dernieres missions</h3>
                        <p class="mt-1 text-sm text-slate-500">Apercu des missions recentes dans le systeme.</p>
                    </div>
                </div>

                <div class="mt-4 overflow-x-auto">
                    <table class="min-w-full divide-y divide-slate-200">
                        <thead class="bg-slate-50">
                            <tr class="text-left text-xs font-semibold uppercase tracking-wider text-slate-500">
                                <th class="px-4 py-3">Mission</th>
                                <th class="px-4 py-3">Reference</th>
                                <th class="px-4 py-3">Statut</th>
                                <th class="px-4 py-3">Assignee</th>
                                <th class="px-4 py-3">Creee le</th>
                            </tr>
                        </thead>
                        <tbody class="divide-y divide-slate-100 bg-white">
                            @forelse ($recentMissions as $mission)
                                <tr>
                                    <td class="px-4 py-3 text-sm font-medium text-slate-900">#{{ $mission->id }} • {{ $mission->type_mission }}</td>
                                    <td class="px-4 py-3 text-sm text-slate-700">{{ $mission->referencePoint?->reference ?? '—' }}</td>
                                    <td class="px-4 py-3 text-sm text-slate-700">{{ $mission->statut }}</td>
                                    <td class="px-4 py-3 text-sm text-slate-700">{{ $mission->currentAffectation?->technicien?->prenom ?? '—' }} {{ $mission->currentAffectation?->technicien?->nom ?? '' }}</td>
                                    <td class="px-4 py-3 text-sm text-slate-700">{{ $mission->created_at?->format('Y-m-d H:i') }}</td>
                                </tr>
                            @empty
                                <tr>
                                    <td colspan="5" class="px-4 py-6 text-center text-sm text-slate-500">Aucune mission recente.</td>
                                </tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>
            </section>
        </div>
    </div>
</x-app-layout>
