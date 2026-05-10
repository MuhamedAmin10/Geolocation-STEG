<x-app-layout>
    <x-slot name="header">
        <div class="rounded-2xl bg-gradient-to-r from-slate-950 via-slate-800 to-amber-700 px-6 py-5 text-white shadow-lg">
            <p class="text-xs font-semibold uppercase tracking-[0.3em] text-amber-100">Planning terrain</p>
            <h2 class="mt-2 text-2xl font-bold">Today's Schedule</h2>
            <p class="mt-2 max-w-3xl text-sm text-amber-100/90">Vue rapide des missions d'aujourd'hui, en retard et de la semaine.</p>
        </div>
    </x-slot>

    <div class="py-10">
        <div class="mx-auto max-w-7xl space-y-6 px-4 sm:px-6 lg:px-8">
            <section class="grid grid-cols-1 gap-4 md:grid-cols-4">
                <article class="rounded-2xl border border-sky-200 bg-sky-50 px-4 py-4">
                    <p class="text-xs uppercase tracking-wider text-sky-700">Aujourd'hui</p>
                    <p class="mt-1 text-2xl font-bold text-sky-900">{{ $todayMissions->count() }}</p>
                </article>
                <article class="rounded-2xl border border-rose-200 bg-rose-50 px-4 py-4">
                    <p class="text-xs uppercase tracking-wider text-rose-700">En retard</p>
                    <p class="mt-1 text-2xl font-bold text-rose-900">{{ $overdueMissions->count() }}</p>
                </article>
                <article class="rounded-2xl border border-indigo-200 bg-indigo-50 px-4 py-4">
                    <p class="text-xs uppercase tracking-wider text-indigo-700">Cette semaine</p>
                    <p class="mt-1 text-2xl font-bold text-indigo-900">{{ $weekMissions->count() }}</p>
                </article>
                <article class="rounded-2xl border border-slate-200 bg-slate-50 px-4 py-4">
                    <p class="text-xs uppercase tracking-wider text-slate-700">Sans échéance</p>
                    <p class="mt-1 text-2xl font-bold text-slate-900">{{ $noDueMissions->count() }}</p>
                </article>
            </section>

            <section class="panel-card overflow-hidden">
                <div class="panel-head">
                    <h3>Missions du jour</h3>
                    <p>{{ now()->format('Y-m-d') }}</p>
                </div>
                <div class="overflow-x-auto">
                    <table class="min-w-full divide-y divide-slate-200">
                        <thead class="bg-slate-50">
                            <tr>
                                <th class="px-4 py-3 text-left text-xs font-semibold uppercase tracking-wider text-slate-500">Référence</th>
                                <th class="px-4 py-3 text-left text-xs font-semibold uppercase tracking-wider text-slate-500">Type</th>
                                <th class="px-4 py-3 text-left text-xs font-semibold uppercase tracking-wider text-slate-500">Statut</th>
                                <th class="px-4 py-3 text-left text-xs font-semibold uppercase tracking-wider text-slate-500">Échéance</th>
                                <th class="px-4 py-3 text-right text-xs font-semibold uppercase tracking-wider text-slate-500">Action</th>
                            </tr>
                        </thead>
                        <tbody class="divide-y divide-slate-100 bg-white">
                            @forelse ($todayMissions as $mission)
                                <tr class="hover:bg-slate-50/80">
                                    <td class="px-4 py-3 text-sm font-medium text-slate-800">{{ $mission->referencePoint?->reference ?? '—' }}</td>
                                    <td class="px-4 py-3 text-sm text-slate-700">{{ $mission->type_mission }}</td>
                                    <td class="px-4 py-3 text-sm text-slate-700">{{ $mission->statut }}</td>
                                    <td class="px-4 py-3 text-sm text-slate-700">{{ $mission->due_at?->format('Y-m-d H:i') ?? '—' }}</td>
                                    <td class="px-4 py-3 text-right text-sm"><a class="font-medium text-brand-primary hover:text-brand-primary-dark" href="{{ route('missions.show', $mission) }}">Ouvrir</a></td>
                                </tr>
                            @empty
                                <tr>
                                    <td colspan="5" class="px-4 py-8 text-center text-sm text-slate-500">Aucune mission planifiée aujourd'hui.</td>
                                </tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>
            </section>

            <section class="grid grid-cols-1 gap-5 lg:grid-cols-2">
                <article class="panel-card overflow-hidden">
                    <div class="panel-head">
                        <h3>En retard</h3>
                        <p>Priorité intervention</p>
                    </div>
                    <div class="divide-y divide-slate-100">
                        @forelse ($overdueMissions as $mission)
                            <div class="flex items-center justify-between px-5 py-3">
                                <div>
                                    <p class="text-sm font-semibold text-slate-800">{{ $mission->referencePoint?->reference ?? '—' }}</p>
                                    <p class="text-xs text-slate-500">{{ $mission->due_at?->format('Y-m-d H:i') ?? 'Sans date' }}</p>
                                </div>
                                <a class="text-sm font-medium text-rose-700 hover:text-rose-900" href="{{ route('missions.show', $mission) }}">Traiter</a>
                            </div>
                        @empty
                            <p class="px-5 py-6 text-sm text-slate-500">Aucune mission en retard.</p>
                        @endforelse
                    </div>
                </article>

                <article class="panel-card overflow-hidden">
                    <div class="panel-head">
                        <h3>Cette semaine</h3>
                        <p>Vue calendrier simplifiée</p>
                    </div>
                    <div class="divide-y divide-slate-100">
                        @forelse ($weekMissions as $mission)
                            <div class="flex items-center justify-between px-5 py-3">
                                <div>
                                    <p class="text-sm font-semibold text-slate-800">{{ $mission->referencePoint?->reference ?? '—' }}</p>
                                    <p class="text-xs text-slate-500">{{ $mission->due_at?->translatedFormat('D d M H:i') ?? 'Sans date' }}</p>
                                </div>
                                <span class="rounded-full bg-slate-100 px-2.5 py-1 text-xs font-semibold text-slate-700">{{ $mission->statut }}</span>
                            </div>
                        @empty
                            <p class="px-5 py-6 text-sm text-slate-500">Aucune mission planifiée sur la semaine.</p>
                        @endforelse
                    </div>
                </article>
            </section>
        </div>
    </div>
</x-app-layout>