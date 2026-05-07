<x-app-layout>
    <x-slot name="header">
        <div class="flex flex-col gap-3 md:flex-row md:items-center md:justify-between">
            <div>
                <p class="text-xs font-semibold uppercase tracking-[0.28em] text-slate-500">Espace administrateur</p>
                <h2 class="mt-2 text-2xl font-bold leading-tight text-slate-900">Analyse admin simple</h2>
                <p class="mt-2 text-sm text-slate-500">Vue globale facile a lire pour suivre l'organisation, les retards et l'avancement des missions.</p>
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
                    <p class="text-xs font-semibold uppercase tracking-wider text-slate-500">En retard</p>
                    <p class="mt-3 text-3xl font-bold text-slate-900">{{ $overdueMissions }}</p>
                </article>
            </section>

            <section class="grid grid-cols-1 gap-6 lg:grid-cols-3">
                <div class="rounded-2xl bg-white p-6 shadow-sm ring-1 ring-slate-200">
                    <h3 class="text-lg font-semibold text-slate-900">Résumé de gestion</h3>
                    <p class="mt-1 text-sm text-slate-500">Ces trois chiffres suffisent pour voir si l'activité avance correctement.</p>
                    <div class="mt-4 grid grid-cols-1 gap-3">
                        <article class="rounded-xl bg-emerald-50 p-4">
                            <p class="text-[11px] font-semibold uppercase tracking-wider text-emerald-700">Taux de completion</p>
                            <p class="mt-2 text-2xl font-bold text-emerald-900">{{ $completionRate }}%</p>
                        </article>
                        <article class="rounded-xl bg-sky-50 p-4">
                            <p class="text-[11px] font-semibold uppercase tracking-wider text-sky-700">Taux d'affectation</p>
                            <p class="mt-2 text-2xl font-bold text-sky-900">{{ $assignmentRate }}%</p>
                        </article>
                        <article class="rounded-xl bg-amber-50 p-4">
                            <p class="text-[11px] font-semibold uppercase tracking-wider text-amber-700">Non affectees</p>
                            <p class="mt-2 text-2xl font-bold text-amber-900">{{ $counts['missions_unassigned'] }}</p>
                        </article>
                    </div>
                </div>

                <div class="rounded-2xl bg-white p-6 shadow-sm ring-1 ring-slate-200 lg:col-span-2">
                    <h3 class="text-lg font-semibold text-slate-900">Lecture rapide des missions</h3>
                    <p class="mt-1 text-sm text-slate-500">Situation actuelle sans surcharge de détail.</p>
                    <div class="mt-4 grid grid-cols-2 gap-3 sm:grid-cols-4">
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
                        <article class="rounded-xl bg-rose-50 p-4">
                            <p class="text-[11px] font-semibold uppercase tracking-wider text-rose-700">En retard</p>
                            <p class="mt-2 text-2xl font-bold text-rose-900">{{ $overdueMissions }}</p>
                        </article>
                    </div>
                </div>
            </section>

            <section class="grid grid-cols-1 gap-6 lg:grid-cols-2">
                <div class="rounded-2xl bg-white p-6 shadow-sm ring-1 ring-slate-200">
                    <h3 class="text-lg font-semibold text-slate-900">Dernieres missions</h3>
                    <p class="mt-1 text-sm text-slate-500">Les missions les plus recentes du système.</p>
                    <div class="mt-4 overflow-x-auto">
                        <table class="min-w-full divide-y divide-slate-200">
                            <thead class="bg-slate-50">
                                <tr class="text-left text-xs font-semibold uppercase tracking-wider text-slate-500">
                                    <th class="px-4 py-3">Mission</th>
                                    <th class="px-4 py-3">Reference</th>
                                    <th class="px-4 py-3">Statut</th>
                                    <th class="px-4 py-3">Creee le</th>
                                </tr>
                            </thead>
                            <tbody class="divide-y divide-slate-100 bg-white">
                                @forelse ($recentMissions as $mission)
                                    <tr>
                                        <td class="px-4 py-3 text-sm font-medium text-slate-900">#{{ $mission->id }} • {{ $mission->type_mission }}</td>
                                        <td class="px-4 py-3 text-sm text-slate-700">{{ $mission->referencePoint?->reference ?? '—' }}</td>
                                        <td class="px-4 py-3 text-sm text-slate-700">{{ $mission->statut }}</td>
                                        <td class="px-4 py-3 text-sm text-slate-700">{{ $mission->created_at?->format('Y-m-d H:i') }}</td>
                                    </tr>
                                @empty
                                    <tr>
                                        <td colspan="4" class="px-4 py-6 text-center text-sm text-slate-500">Aucune mission récente.</td>
                                    </tr>
                                @endforelse
                            </tbody>
                        </table>
                    </div>
                </div>

                <div class="rounded-2xl bg-white p-6 shadow-sm ring-1 ring-slate-200">
                    <h3 class="text-lg font-semibold text-slate-900">Activité récente</h3>
                    <p class="mt-1 text-sm text-slate-500">Derniers changements enregistrés dans le système.</p>
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
        </div>
    </div>
</x-app-layout>
