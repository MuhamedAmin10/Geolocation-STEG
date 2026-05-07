<x-app-layout>
    @php
        $isTechnicien = auth()->user()?->role === 'Technicien';
        $headerTotal = $isTechnicien
            ? (($activeMissions->total() ?? 0) + ($completedMissions->total() ?? 0))
            : ($missions->total() ?? 0);
    @endphp

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
                    Total: {{ $headerTotal }}
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
                    @php
                        $totalMissions = $isTechnicien
                            ? (($activeMissions->total() ?? 0) + ($completedMissions->total() ?? 0))
                            : ($missions->total() ?? 0);
                    @endphp

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

                    @if (session('warning'))
                        <div class="mb-4 rounded-xl border border-amber-200 bg-amber-50 px-4 py-3 text-amber-800">
                            {{ session('warning') }}
                        </div>
                    @endif

                    <div class="mb-5 grid grid-cols-1 gap-3 sm:grid-cols-3">
                        <div class="rounded-xl border border-slate-200 bg-slate-50 px-4 py-3">
                            <div class="text-xs uppercase tracking-wider text-slate-500">Total</div>
                            <div class="mt-1 text-xl font-semibold text-slate-900">{{ $totalMissions }}</div>
                        </div>
                        <div class="rounded-xl border border-amber-200 bg-amber-50 px-4 py-3">
                            <div class="text-xs uppercase tracking-wider text-amber-700">Non terminées</div>
                            <div class="mt-1 text-xl font-semibold text-amber-800">{{ $isTechnicien ? $activeMissions->total() : $missions->where('statut', '!=', 'Terminée')->count() }}</div>
                        </div>
                        <div class="rounded-xl border border-emerald-200 bg-emerald-50 px-4 py-3">
                            <div class="text-xs uppercase tracking-wider text-emerald-700">Terminées</div>
                            <div class="mt-1 text-xl font-semibold text-emerald-800">{{ $isTechnicien ? $completedMissions->total() : $missions->where('statut', 'Terminée')->count() }}</div>
                        </div>
                    </div>

                    @if ($isTechnicien)
                        <form method="GET" action="{{ route('missions.index') }}" class="mb-6 grid gap-4 rounded-2xl border border-slate-200 bg-slate-50 p-4 md:grid-cols-5">
                            <input type="hidden" name="mine" value="1">

                            <label class="md:col-span-2">
                                <span class="mb-1 block text-xs font-semibold uppercase tracking-wider text-slate-500">Recherche mission</span>
                                <input
                                    type="text"
                                    name="search"
                                    value="{{ $search ?? '' }}"
                                    placeholder="Reference, type, description..."
                                    class="w-full rounded-xl border-slate-300 bg-white px-3 py-2 text-sm text-slate-800 focus:border-brand-primary focus:ring-brand-primary"
                                >
                            </label>

                            <label>
                                <span class="mb-1 block text-xs font-semibold uppercase tracking-wider text-slate-500">Statut actif</span>
                                <select name="active_status" class="w-full rounded-xl border-slate-300 bg-white px-3 py-2 text-sm text-slate-800 focus:border-brand-primary focus:ring-brand-primary">
                                    <option value="all" @selected(($activeStatus ?? 'all') === 'all')>Tous</option>
                                    <option value="En cours" @selected(($activeStatus ?? '') === 'En cours')>En cours</option>
                                    <option value="Bloquée" @selected(($activeStatus ?? '') === 'Bloquée')>Bloquée</option>
                                    <option value="Assignée" @selected(($activeStatus ?? '') === 'Assignée')>Assignee</option>
                                    <option value="Créée" @selected(($activeStatus ?? '') === 'Créée')>Creee</option>
                                </select>
                            </label>

                            <label>
                                <span class="mb-1 block text-xs font-semibold uppercase tracking-wider text-slate-500">Priorité active</span>
                                <select name="active_priority" class="w-full rounded-xl border-slate-300 bg-white px-3 py-2 text-sm text-slate-800 focus:border-brand-primary focus:ring-brand-primary">
                                    <option value="all" @selected(($activePriority ?? 'all') === 'all')>Toutes</option>
                                    <option value="Urgente" @selected(($activePriority ?? '') === 'Urgente')>Urgente</option>
                                    <option value="Haute" @selected(($activePriority ?? '') === 'Haute')>Haute</option>
                                    <option value="Normale" @selected(($activePriority ?? '') === 'Normale')>Normale</option>
                                    <option value="Basse" @selected(($activePriority ?? '') === 'Basse')>Basse</option>
                                </select>
                            </label>

                            <label>
                                <span class="mb-1 block text-xs font-semibold uppercase tracking-wider text-slate-500">Scope active</span>
                                <select name="active_scope" class="w-full rounded-xl border-slate-300 bg-white px-3 py-2 text-sm text-slate-800 focus:border-brand-primary focus:ring-brand-primary">
                                    <option value="all" @selected(($activeScope ?? 'all') === 'all')>Toute la periode</option>
                                    <option value="overdue" @selected(($activeScope ?? '') === 'overdue')>En retard</option>
                                    <option value="today" @selected(($activeScope ?? '') === 'today')>Aujourd'hui</option>
                                    <option value="week" @selected(($activeScope ?? '') === 'week')>Cette semaine</option>
                                    <option value="no_due" @selected(($activeScope ?? '') === 'no_due')>Sans echeance</option>
                                </select>
                            </label>

                            <label>
                                <span class="mb-1 block text-xs font-semibold uppercase tracking-wider text-slate-500">Terminées sur</span>
                                <select name="completed_period" class="w-full rounded-xl border-slate-300 bg-white px-3 py-2 text-sm text-slate-800 focus:border-brand-primary focus:ring-brand-primary">
                                    <option value="all" @selected(($completedPeriod ?? 'all') === 'all')>Toute la periode</option>
                                    <option value="7d" @selected(($completedPeriod ?? '') === '7d')>7 jours</option>
                                    <option value="30d" @selected(($completedPeriod ?? '') === '30d')>30 jours</option>
                                    <option value="90d" @selected(($completedPeriod ?? '') === '90d')>90 jours</option>
                                </select>
                            </label>

                            <div class="md:col-span-5 flex flex-wrap items-center gap-3">
                                <button type="submit" class="inline-flex items-center rounded-xl bg-brand-primary px-4 py-2 text-xs font-semibold uppercase tracking-wider text-white transition hover:bg-brand-primary-dark">
                                    Appliquer filtres
                                </button>
                                <a href="{{ route('missions.index', ['mine' => 1]) }}" class="inline-flex items-center rounded-xl border border-slate-300 bg-white px-4 py-2 text-xs font-semibold uppercase tracking-wider text-slate-700 transition hover:bg-slate-100">
                                    Réinitialiser
                                </a>
                            </div>
                        </form>

                        <div class="space-y-8">
                            <section>
                                <div class="mb-3 flex items-center justify-between">
                                    <h3 class="text-sm font-semibold uppercase tracking-[0.16em] text-slate-600">Missions non terminées</h3>
                                    <div class="flex items-center gap-2">
                                        <a href="{{ route('missions.export-technician-csv', array_merge(request()->query(), ['type' => 'active'])) }}" class="rounded-full border border-slate-300 bg-white px-3 py-1 text-xs font-semibold text-slate-700 transition hover:bg-slate-100">Export CSV</a>
                                        <span class="rounded-full border border-amber-200 bg-amber-50 px-3 py-1 text-xs font-semibold text-amber-700">{{ $activeMissions->total() }} missions</span>
                                    </div>
                                </div>
                                <div class="mb-3 flex flex-wrap gap-2">
                                    <a href="{{ route('missions.index', array_merge(request()->query(), ['mine' => 1, 'active_scope' => 'overdue'])) }}" class="rounded-full border border-rose-200 bg-rose-50 px-3 py-1 text-xs font-semibold text-rose-700 transition hover:bg-rose-100">En retard</a>
                                    <a href="{{ route('missions.index', array_merge(request()->query(), ['mine' => 1, 'active_scope' => 'today'])) }}" class="rounded-full border border-sky-200 bg-sky-50 px-3 py-1 text-xs font-semibold text-sky-700 transition hover:bg-sky-100">Aujourd'hui</a>
                                    <a href="{{ route('missions.index', array_merge(request()->query(), ['mine' => 1, 'active_scope' => 'week'])) }}" class="rounded-full border border-indigo-200 bg-indigo-50 px-3 py-1 text-xs font-semibold text-indigo-700 transition hover:bg-indigo-100">Cette semaine</a>
                                    <a href="{{ route('missions.index', array_merge(request()->query(), ['mine' => 1, 'active_scope' => 'all'])) }}" class="rounded-full border border-slate-300 bg-white px-3 py-1 text-xs font-semibold text-slate-700 transition hover:bg-slate-100">Tout</a>
                                </div>
                                <div class="overflow-x-auto rounded-xl border border-amber-200">
                                    <table class="min-w-full divide-y divide-slate-200">
                                        <thead class="bg-amber-50/60">
                                            <tr>
                                                <th class="px-4 py-3 text-left text-xs font-semibold uppercase tracking-wider text-slate-500">Référence</th>
                                                <th class="px-4 py-3 text-left text-xs font-semibold uppercase tracking-wider text-slate-500">Type</th>
                                                <th class="px-4 py-3 text-left text-xs font-semibold uppercase tracking-wider text-slate-500">Priorité</th>
                                                <th class="px-4 py-3 text-left text-xs font-semibold uppercase tracking-wider text-slate-500">Statut</th>
                                                <th class="px-4 py-3 text-left text-xs font-semibold uppercase tracking-wider text-slate-500">SLA</th>
                                                <th class="px-4 py-3 text-left text-xs font-semibold uppercase tracking-wider text-slate-500">Échéance</th>
                                                <th class="px-4 py-3 text-right text-xs font-semibold uppercase tracking-wider text-slate-500">Actions</th>
                                            </tr>
                                        </thead>
                                        <tbody class="divide-y divide-slate-100 bg-white">
                                            @forelse ($activeMissions as $mission)
                                                <tr @class([
                                                    'hover:bg-amber-50/30',
                                                    'bg-rose-50/40' => $mission->due_at && $mission->due_at->isPast(),
                                                ])>
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
                                                            'bg-amber-100 text-amber-800' => $mission->statut === 'Bloquée',
                                                            'bg-sky-100 text-sky-800' => $mission->statut === 'En cours',
                                                            'bg-slate-100 text-slate-700' => !in_array($mission->statut, ['Bloquée', 'En cours']),
                                                        ])>{{ $mission->statut }}</span>
                                                    </td>
                                                    <td class="px-4 py-3 whitespace-nowrap">
                                                        @php
                                                            $slaState = $mission->slaState();
                                                            $slaClass = match ($slaState) {
                                                                'red' => 'bg-rose-100 text-rose-800',
                                                                'yellow' => 'bg-amber-100 text-amber-800',
                                                                default => 'bg-emerald-100 text-emerald-800',
                                                            };
                                                            $slaText = match ($slaState) {
                                                                'red' => 'Overdue',
                                                                'yellow' => 'Warning',
                                                                default => 'On track',
                                                            };
                                                        @endphp
                                                        <span class="rounded-full px-2.5 py-1 text-xs font-semibold {{ $slaClass }}">{{ $slaText }}</span>
                                                    </td>
                                                    <td class="px-4 py-3 whitespace-nowrap text-sm text-slate-600">{{ $mission->due_at?->format('Y-m-d H:i') ?? '—' }}</td>
                                                    <td class="space-x-2 whitespace-nowrap px-4 py-3 text-right">
                                                        <a class="font-medium text-brand-primary hover:text-brand-primary-dark" href="{{ route('missions.show', $mission) }}">Voir</a>
                                                    </td>
                                                </tr>
                                            @empty
                                                <tr>
                                                    <td colspan="7" class="px-4 py-8 text-center text-slate-500">Aucune mission non terminée.</td>
                                                </tr>
                                            @endforelse
                                        </tbody>
                                    </table>
                                </div>
                                <div class="mt-4">{{ $activeMissions->links() }}</div>
                            </section>

                            <section>
                                <div class="mb-3 flex items-center justify-between">
                                    <h3 class="text-sm font-semibold uppercase tracking-[0.16em] text-slate-600">Missions terminées</h3>
                                    <div class="flex items-center gap-2">
                                        <a href="{{ route('missions.export-technician-csv', array_merge(request()->query(), ['type' => 'completed'])) }}" class="rounded-full border border-slate-300 bg-white px-3 py-1 text-xs font-semibold text-slate-700 transition hover:bg-slate-100">Export CSV</a>
                                        <span class="rounded-full border border-emerald-200 bg-emerald-50 px-3 py-1 text-xs font-semibold text-emerald-700">{{ $completedMissions->total() }} missions</span>
                                    </div>
                                </div>
                                <div class="overflow-x-auto rounded-xl border border-emerald-200">
                                    <table class="min-w-full divide-y divide-slate-200">
                                        <thead class="bg-emerald-50/70">
                                            <tr>
                                                <th class="px-4 py-3 text-left text-xs font-semibold uppercase tracking-wider text-slate-500">Référence</th>
                                                <th class="px-4 py-3 text-left text-xs font-semibold uppercase tracking-wider text-slate-500">Type</th>
                                                <th class="px-4 py-3 text-left text-xs font-semibold uppercase tracking-wider text-slate-500">Priorité</th>
                                                <th class="px-4 py-3 text-left text-xs font-semibold uppercase tracking-wider text-slate-500">Statut</th>
                                                <th class="px-4 py-3 text-left text-xs font-semibold uppercase tracking-wider text-slate-500">SLA</th>
                                                <th class="px-4 py-3 text-left text-xs font-semibold uppercase tracking-wider text-slate-500">Clôturée le</th>
                                                <th class="px-4 py-3 text-right text-xs font-semibold uppercase tracking-wider text-slate-500">Actions</th>
                                            </tr>
                                        </thead>
                                        <tbody class="divide-y divide-slate-100 bg-white">
                                            @forelse ($completedMissions as $mission)
                                                <tr class="hover:bg-emerald-50/30">
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
                                                        <span class="rounded-full bg-emerald-100 px-2.5 py-1 text-xs font-semibold text-emerald-800">{{ $mission->statut }}</span>
                                                    </td>
                                                    <td class="px-4 py-3 whitespace-nowrap">
                                                        @php
                                                            $slaState = $mission->slaState();
                                                            $slaClass = match ($slaState) {
                                                                'red' => 'bg-rose-100 text-rose-800',
                                                                'yellow' => 'bg-amber-100 text-amber-800',
                                                                default => 'bg-emerald-100 text-emerald-800',
                                                            };
                                                            $slaText = match ($slaState) {
                                                                'red' => 'Overdue',
                                                                'yellow' => 'Warning',
                                                                default => 'On track',
                                                            };
                                                        @endphp
                                                        <span class="rounded-full px-2.5 py-1 text-xs font-semibold {{ $slaClass }}">{{ $slaText }}</span>
                                                    </td>
                                                    <td class="px-4 py-3 whitespace-nowrap text-sm text-slate-600">{{ $mission->completed_at?->format('Y-m-d H:i') ?? '—' }}</td>
                                                    <td class="space-x-2 whitespace-nowrap px-4 py-3 text-right">
                                                        <a class="font-medium text-brand-primary hover:text-brand-primary-dark" href="{{ route('missions.show', $mission) }}">Voir</a>
                                                    </td>
                                                </tr>
                                            @empty
                                                <tr>
                                                    <td colspan="7" class="px-4 py-8 text-center text-slate-500">Aucune mission terminée.</td>
                                                </tr>
                                            @endforelse
                                        </tbody>
                                    </table>
                                </div>
                                <div class="mt-4">{{ $completedMissions->links() }}</div>
                            </section>
                        </div>
                    @else
                        <div class="overflow-x-auto rounded-xl border border-slate-200">
                            <table class="min-w-full divide-y divide-slate-200">
                                <thead class="bg-slate-50">
                                    <tr>
                                        <th class="px-4 py-3 text-left text-xs font-semibold uppercase tracking-wider text-slate-500">Référence</th>
                                        <th class="px-4 py-3 text-left text-xs font-semibold uppercase tracking-wider text-slate-500">Type</th>
                                        <th class="px-4 py-3 text-left text-xs font-semibold uppercase tracking-wider text-slate-500">Priorité</th>
                                        <th class="px-4 py-3 text-left text-xs font-semibold uppercase tracking-wider text-slate-500">Statut</th>
                                        <th class="px-4 py-3 text-left text-xs font-semibold uppercase tracking-wider text-slate-500">SLA</th>
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
                                                    $slaState = $mission->slaState();
                                                    $slaClass = match ($slaState) {
                                                        'red' => 'bg-rose-100 text-rose-800',
                                                        'yellow' => 'bg-amber-100 text-amber-800',
                                                        default => 'bg-emerald-100 text-emerald-800',
                                                    };
                                                    $slaText = match ($slaState) {
                                                        'red' => 'Overdue',
                                                        'yellow' => 'Warning',
                                                        default => 'On track',
                                                    };
                                                @endphp
                                                <span class="rounded-full px-2.5 py-1 text-xs font-semibold {{ $slaClass }}">{{ $slaText }}</span>
                                                <div class="mt-1 text-[11px] text-slate-500">{{ $mission->sla_level ?? 'Bronze' }} • {{ $mission->expectedDurationMinutes() }} min</div>
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
                                            <td colspan="8" class="px-4 py-8 text-center text-slate-500">Aucune mission trouvée.</td>
                                        </tr>
                                    @endforelse
                                </tbody>
                            </table>
                        </div>

                        <div class="mt-6">
                            {{ $missions->links() }}
                        </div>
                    @endif
                </div>
            </div>
        </div>
    </div>
</x-app-layout>
