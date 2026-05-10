<x-app-layout>
    <x-slot name="header">
        <div class="rounded-2xl bg-gradient-to-r from-slate-950 via-slate-800 to-violet-700 px-6 py-5 text-white shadow-lg">
            <p class="text-xs font-semibold uppercase tracking-[0.3em] text-violet-100">Suivi du temps</p>
            <h2 class="mt-2 text-2xl font-bold">Time Tracker</h2>
            <p class="mt-2 max-w-3xl text-sm text-violet-100/90">Vue centrée sur le chronométrage: missions actives, minutes cumulées et derniers événements timer.</p>
        </div>
    </x-slot>

    <div class="py-10">
        <div class="mx-auto max-w-7xl space-y-6 px-4 sm:px-6 lg:px-8">
            <section class="grid grid-cols-1 gap-4 md:grid-cols-4">
                <article class="rounded-2xl border border-emerald-200 bg-emerald-50 px-4 py-4">
                    <p class="text-xs uppercase tracking-wider text-emerald-700">Missions en cours</p>
                    <p class="mt-1 text-2xl font-bold text-emerald-900">{{ $summary['running'] }}</p>
                </article>
                <article class="rounded-2xl border border-amber-200 bg-amber-50 px-4 py-4">
                    <p class="text-xs uppercase tracking-wider text-amber-700">Missions bloquées</p>
                    <p class="mt-1 text-2xl font-bold text-amber-900">{{ $summary['blocked'] }}</p>
                </article>
                <article class="rounded-2xl border border-sky-200 bg-sky-50 px-4 py-4">
                    <p class="text-xs uppercase tracking-wider text-sky-700">Temps total (min)</p>
                    <p class="mt-1 text-2xl font-bold text-sky-900">{{ $summary['total_minutes'] }}</p>
                </article>
                <article class="rounded-2xl border border-indigo-200 bg-indigo-50 px-4 py-4">
                    <p class="text-xs uppercase tracking-wider text-indigo-700">Temps sur site (min)</p>
                    <p class="mt-1 text-2xl font-bold text-indigo-900">{{ $summary['on_site_minutes'] }}</p>
                </article>
            </section>

            <section class="panel-card overflow-hidden">
                <div class="panel-head">
                    <h3>Missions suivies</h3>
                    <p>{{ $activeMissions->count() }} mission(s) actives</p>
                </div>
                <div class="overflow-x-auto">
                    <table class="min-w-full divide-y divide-slate-200">
                        <thead class="bg-slate-50">
                            <tr>
                                <th class="px-4 py-3 text-left text-xs font-semibold uppercase tracking-wider text-slate-500">Référence</th>
                                <th class="px-4 py-3 text-left text-xs font-semibold uppercase tracking-wider text-slate-500">Statut</th>
                                <th class="px-4 py-3 text-left text-xs font-semibold uppercase tracking-wider text-slate-500">Total</th>
                                <th class="px-4 py-3 text-left text-xs font-semibold uppercase tracking-wider text-slate-500">On-site</th>
                                <th class="px-4 py-3 text-left text-xs font-semibold uppercase tracking-wider text-slate-500">Travel</th>
                                <th class="px-4 py-3 text-right text-xs font-semibold uppercase tracking-wider text-slate-500">Action</th>
                            </tr>
                        </thead>
                        <tbody class="divide-y divide-slate-100 bg-white">
                            @forelse ($activeMissions as $mission)
                                <tr class="hover:bg-slate-50/80">
                                    <td class="px-4 py-3 text-sm font-medium text-slate-800">{{ $mission->referencePoint?->reference ?? '—' }}</td>
                                    <td class="px-4 py-3 text-sm text-slate-700">{{ $mission->statut }}</td>
                                    <td class="px-4 py-3 text-sm text-slate-700">{{ (int) ($mission->total_working_time ?? 0) }} min</td>
                                    <td class="px-4 py-3 text-sm text-slate-700">{{ (int) ($mission->on_site_time_minutes ?? 0) }} min</td>
                                    <td class="px-4 py-3 text-sm text-slate-700">{{ (int) ($mission->travel_time_minutes ?? 0) }} min</td>
                                    <td class="px-4 py-3 text-right text-sm"><a class="font-medium text-brand-primary hover:text-brand-primary-dark" href="{{ route('missions.show', $mission) }}">Ouvrir tracker</a></td>
                                </tr>
                            @empty
                                <tr>
                                    <td colspan="6" class="px-4 py-8 text-center text-sm text-slate-500">Aucune mission active à chronométrer.</td>
                                </tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>
            </section>

            <section class="panel-card overflow-hidden">
                <div class="panel-head">
                    <h3>Derniers événements timer</h3>
                    <p>Logs récents (start/pause/resume/complete)</p>
                </div>
                <div class="overflow-x-auto">
                    <table class="min-w-full divide-y divide-slate-200">
                        <thead class="bg-slate-50">
                            <tr>
                                <th class="px-4 py-3 text-left text-xs font-semibold uppercase tracking-wider text-slate-500">Date</th>
                                <th class="px-4 py-3 text-left text-xs font-semibold uppercase tracking-wider text-slate-500">Référence</th>
                                <th class="px-4 py-3 text-left text-xs font-semibold uppercase tracking-wider text-slate-500">Action</th>
                                <th class="px-4 py-3 text-left text-xs font-semibold uppercase tracking-wider text-slate-500">Coordonnées</th>
                            </tr>
                        </thead>
                        <tbody class="divide-y divide-slate-100 bg-white">
                            @forelse ($recentLogs as $log)
                                <tr class="hover:bg-slate-50/80">
                                    <td class="px-4 py-3 text-sm text-slate-700">{{ $log->logged_at?->format('Y-m-d H:i:s') }}</td>
                                    <td class="px-4 py-3 text-sm text-slate-700">{{ $log->mission?->referencePoint?->reference ?? '—' }}</td>
                                    <td class="px-4 py-3 text-sm text-slate-700">{{ $log->action }}</td>
                                    <td class="px-4 py-3 text-sm text-slate-700">
                                        @if ($log->latitude !== null && $log->longitude !== null)
                                            {{ number_format((float) $log->latitude, 6) }}, {{ number_format((float) $log->longitude, 6) }}
                                        @else
                                            —
                                        @endif
                                    </td>
                                </tr>
                            @empty
                                <tr>
                                    <td colspan="4" class="px-4 py-8 text-center text-sm text-slate-500">Aucun log de timer disponible.</td>
                                </tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>
            </section>
        </div>
    </div>
</x-app-layout>