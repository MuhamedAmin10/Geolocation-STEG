<x-app-layout>
    <x-slot name="header">
        <div class="analysis-hero rounded-2xl px-6 py-5 text-white">
            <div class="flex flex-col gap-4 md:flex-row md:items-center md:justify-between">
                <div>
                    <p class="text-xs font-semibold uppercase tracking-[0.28em] text-cyan-100">Tableau de performance</p>
                    <h2 class="mt-2 text-2xl font-bold leading-tight md:text-3xl">Analyse de travail technicien</h2>
                    <p class="mt-2 text-sm text-cyan-100/90">Lecture simple de votre activite: volume, qualite, delais et evolution mensuelle.</p>
                </div>

                <div class="space-y-2">
                    <form method="GET" action="{{ route('missions.analysis') }}" class="flex flex-wrap items-center gap-2">
                        <label for="period" class="text-[11px] font-semibold uppercase tracking-wider text-cyan-100">Periode</label>
                        <select
                            id="period"
                            name="period"
                            class="rounded-xl border border-white/30 bg-white/10 px-3 py-2 text-xs font-semibold uppercase tracking-wider text-white backdrop-blur focus:border-white/60 focus:outline-none"
                        >
                            <option value="7d" @selected($selectedPeriod === '7d') class="text-slate-900">7 jours</option>
                            <option value="30d" @selected($selectedPeriod === '30d') class="text-slate-900">30 jours</option>
                            <option value="90d" @selected($selectedPeriod === '90d') class="text-slate-900">90 jours</option>
                            <option value="all" @selected($selectedPeriod === 'all') class="text-slate-900">Tout</option>
                            <option value="custom" @selected($selectedPeriod === 'custom') class="text-slate-900">Personnalise</option>
                        </select>

                        <input
                            id="start_date"
                            name="start_date"
                            type="date"
                            value="{{ old('start_date', $rangeStart?->toDateString()) }}"
                            class="analysis-custom-date rounded-xl border border-white/30 bg-white/10 px-3 py-2 text-xs font-semibold text-white backdrop-blur focus:border-white/60 focus:outline-none"
                        />
                        <input
                            id="end_date"
                            name="end_date"
                            type="date"
                            value="{{ old('end_date', $rangeEnd?->toDateString()) }}"
                            class="analysis-custom-date rounded-xl border border-white/30 bg-white/10 px-3 py-2 text-xs font-semibold text-white backdrop-blur focus:border-white/60 focus:outline-none"
                        />

                        <button type="submit" class="inline-flex items-center rounded-xl border border-white/30 bg-white/10 px-3 py-2 text-xs font-semibold uppercase tracking-wider text-white backdrop-blur transition hover:bg-white/20">
                            Appliquer
                        </button>

                        <a
                            href="{{ route('missions.analysis.export', array_filter(['period' => $selectedPeriod, 'start_date' => $rangeStart?->toDateString(), 'end_date' => $rangeEnd?->toDateString()])) }}"
                            class="inline-flex items-center rounded-xl border border-white/30 bg-white/10 px-3 py-2 text-xs font-semibold uppercase tracking-wider text-white backdrop-blur transition hover:bg-white/20"
                        >
                            Export PDF
                        </a>

                        <a href="{{ route('missions.index', ['mine' => 1]) }}" class="inline-flex items-center rounded-xl border border-white/30 bg-white/10 px-4 py-2 text-xs font-semibold uppercase tracking-wider text-white backdrop-blur transition hover:bg-white/20">
                            Historique pour moi
                        </a>
                        <span class="inline-flex items-center rounded-xl border border-white/30 bg-white/10 px-4 py-2 text-xs font-semibold uppercase tracking-wider text-cyan-50 backdrop-blur">
                            Terminees ce mois: {{ $currentMonthCompleted }}
                        </span>
                    </form>
                    @if ($rangeStart && $rangeEnd)
                        <p class="text-xs text-cyan-100/90">
                            Fenetre active: du {{ $rangeStart->format('Y-m-d') }} au {{ $rangeEnd->format('Y-m-d') }}
                        </p>
                    @endif
                    @error('start_date')
                        <p class="text-xs font-medium text-rose-200">{{ $message }}</p>
                    @enderror
                    @error('end_date')
                        <p class="text-xs font-medium text-rose-200">{{ $message }}</p>
                    @enderror
                </div>
            </div>
        </div>
    </x-slot>

    <div class="py-10">
        <div class="mx-auto max-w-7xl space-y-6 sm:px-6 lg:px-8">
            <section class="panel-card p-4 sm:p-5">
                <div class="flex flex-col gap-4 lg:flex-row lg:items-center lg:justify-between">
                    <div>
                        <h3 class="text-base font-semibold text-slate-900">Resume rapide</h3>
                        <p class="mt-1 text-sm text-slate-600">Les 4 chiffres a regarder en premier.</p>
                    </div>
                    <div class="grid w-full grid-cols-2 gap-3 sm:grid-cols-4 lg:w-auto">
                        <article class="kpi-compact">
                            <p class="kpi-compact-label">Missions</p>
                            <p class="kpi-compact-value">{{ $total }}</p>
                        </article>
                        <article class="kpi-compact">
                            <p class="kpi-compact-label">Validees</p>
                            <p class="kpi-compact-value text-emerald-700">{{ $validatedRate }}%</p>
                        </article>
                        <article class="kpi-compact">
                            <p class="kpi-compact-label">A temps</p>
                            <p class="kpi-compact-value text-teal-700">{{ $onTimeRate }}%</p>
                        </article>
                        <article class="kpi-compact">
                            <p class="kpi-compact-label">Temps moyen</p>
                            <p class="kpi-compact-value text-violet-700">{{ $avgResolutionHours }}h</p>
                        </article>
                    </div>
                </div>
            </section>

            <section class="grid grid-cols-1 gap-4 sm:grid-cols-2 xl:grid-cols-5">
                <article class="kpi-card">
                    <p class="kpi-label">Missions total</p>
                    <p class="kpi-value">{{ $total }}</p>
                </article>
                <article class="kpi-card kpi-card-emerald">
                    <p class="kpi-label">Terminees</p>
                    <p class="kpi-value">{{ $completed }}</p>
                </article>
                <article class="kpi-card kpi-card-sky">
                    <p class="kpi-label">En cours</p>
                    <p class="kpi-value">{{ $inProgress }}</p>
                </article>
                <article class="kpi-card kpi-card-amber">
                    <p class="kpi-label">Bloquees</p>
                    <p class="kpi-value">{{ $blocked }}</p>
                </article>
                <article class="kpi-card kpi-card-slate">
                    <p class="kpi-label">Annulees</p>
                    <p class="kpi-value">{{ $cancelled }}</p>
                </article>
            </section>

            <section class="grid grid-cols-1 gap-5 xl:grid-cols-12">
                <article class="panel-card xl:col-span-4">
                    <div class="panel-head">
                        <h3>Lecture des KPIs</h3>
                        <p>Indicateurs actionnables et faciles a comparer</p>
                    </div>

                    <div class="space-y-4 p-5">
                        <article class="metric-ring-card">
                            <div class="metric-ring" style="--p: {{ $validatedRate }}; --ring: #2563eb; --ring-soft: #dbeafe;">
                                <span>{{ $validatedRate }}%</span>
                            </div>
                            <div>
                                <h3 class="metric-title">Taux de validation</h3>
                                <p class="metric-text">Missions terminees sur total assigne.</p>
                            </div>
                        </article>

                        <article class="metric-ring-card">
                            <div class="metric-ring" style="--p: {{ $onTimeRate }}; --ring: #0f766e; --ring-soft: #ccfbf1;">
                                <span>{{ $onTimeRate }}%</span>
                            </div>
                            <div>
                                <h3 class="metric-title">Respect des delais</h3>
                                <p class="metric-text">Missions terminees dans les delais prevus.</p>
                            </div>
                        </article>

                        <article class="metric-ring-card">
                            <div class="metric-ring" style="--p: {{ $highPriorityResolutionRate }}; --ring: #c2410c; --ring-soft: #ffedd5;">
                                <span>{{ $highPriorityResolutionRate }}%</span>
                            </div>
                            <div>
                                <h3 class="metric-title">Haute priorite resolue</h3>
                                <p class="metric-text">Part terminee des missions Haute/Urgente.</p>
                            </div>
                        </article>

                        <div class="rounded-xl border border-slate-200 bg-slate-50 px-4 py-3">
                            <p class="text-xs uppercase tracking-wider text-slate-600">Regle de lecture</p>
                            <p class="mt-1 text-sm text-slate-700">Objectif recommande: validation > 80%, delais > 70%, bloquees < 15%.</p>
                        </div>
                    </div>
                </article>

                <article class="panel-card xl:col-span-3">
                    <div class="panel-head">
                        <h3>Repartition par statut</h3>
                        <p>Vue instantanee de la charge actuelle</p>
                    </div>
                    <div class="panel-body h-[320px]">
                        <canvas id="statusChart"></canvas>
                    </div>
                </article>

                <article class="panel-card xl:col-span-5">
                    <div class="panel-head">
                        <h3>
                            Tendance
                            @if ($selectedPeriod === '7d')
                                7 derniers jours
                            @elseif ($selectedPeriod === '30d')
                                30 derniers jours
                            @elseif ($selectedPeriod === '90d')
                                90 derniers jours
                            @elseif ($selectedPeriod === 'all')
                                globale
                            @else
                                personnalisee
                            @endif
                        </h3>
                        <p>Progression des missions assignees et terminees</p>
                    </div>
                    <div class="panel-body h-[320px]">
                        <canvas id="trendChart"></canvas>
                    </div>
                </article>
            </section>

            <section class="grid grid-cols-1 gap-5 xl:grid-cols-12">
                <article class="panel-card xl:col-span-4">
                    <div class="panel-head">
                        <h3>Performance directe</h3>
                        <p>Barres de progression pour decision rapide</p>
                    </div>
                    <div class="space-y-4 p-5">
                        <div>
                            <div class="mb-2 flex items-center justify-between text-sm">
                                <span class="font-medium text-slate-700">Taux bloque</span>
                                <span class="font-semibold text-rose-700">{{ $blockedRate }}%</span>
                            </div>
                            <div class="h-2.5 overflow-hidden rounded-full bg-rose-100">
                                <div class="h-full rounded-full bg-gradient-to-r from-rose-500 to-rose-600" style="width: {{ min(max($blockedRate, 0), 100) }}%"></div>
                            </div>
                            <p class="mt-1 text-xs text-slate-500">Plus ce chiffre est faible, mieux c'est.</p>
                        </div>

                        <div>
                            <div class="mb-2 flex items-center justify-between text-sm">
                                <span class="font-medium text-slate-700">Validation</span>
                                <span class="font-semibold text-blue-700">{{ $validatedRate }}%</span>
                            </div>
                            <div class="h-2.5 overflow-hidden rounded-full bg-blue-100">
                                <div class="h-full rounded-full bg-gradient-to-r from-blue-500 to-blue-600" style="width: {{ min(max($validatedRate, 0), 100) }}%"></div>
                            </div>
                            <p class="mt-1 text-xs text-slate-500">Montre la part de missions finalisees.</p>
                        </div>

                        <div>
                            <div class="mb-2 flex items-center justify-between text-sm">
                                <span class="font-medium text-slate-700">Delais respectes</span>
                                <span class="font-semibold text-teal-700">{{ $onTimeRate }}%</span>
                            </div>
                            <div class="h-2.5 overflow-hidden rounded-full bg-teal-100">
                                <div class="h-full rounded-full bg-gradient-to-r from-teal-500 to-teal-600" style="width: {{ min(max($onTimeRate, 0), 100) }}%"></div>
                            </div>
                            <p class="mt-1 text-xs text-slate-500">Mesure la ponctualite d'execution.</p>
                        </div>

                        <div class="rounded-xl border border-violet-200 bg-violet-50 px-4 py-3">
                            <p class="text-xs uppercase tracking-wider text-violet-700">Temps moyen de resolution</p>
                            <p class="mt-1 text-3xl font-bold text-violet-800">{{ $avgResolutionHours }} <span class="text-base font-semibold">h</span></p>
                        </div>
                    </div>
                </article>

                <article class="panel-card xl:col-span-8">
                    <div class="panel-head">
                        <h3>Missions recentes</h3>
                        <p>Dernieres missions qui vous concernent, avec acces direct</p>
                    </div>
                    <div class="overflow-x-auto rounded-b-2xl">
                        <table class="min-w-full divide-y divide-slate-200">
                            <thead class="bg-slate-50">
                                <tr>
                                    <th class="px-4 py-3 text-left text-xs font-semibold uppercase tracking-wider text-slate-500">Mission</th>
                                    <th class="px-4 py-3 text-left text-xs font-semibold uppercase tracking-wider text-slate-500">Reference</th>
                                    <th class="px-4 py-3 text-left text-xs font-semibold uppercase tracking-wider text-slate-500">Statut</th>
                                    <th class="px-4 py-3 text-left text-xs font-semibold uppercase tracking-wider text-slate-500">Echeance</th>
                                    <th class="px-4 py-3 text-right text-xs font-semibold uppercase tracking-wider text-slate-500">Action</th>
                                </tr>
                            </thead>
                            <tbody class="divide-y divide-slate-100 bg-white">
                                @forelse ($recentMissions as $mission)
                                    <tr class="hover:bg-slate-50/80">
                                        <td class="px-4 py-3 text-sm font-medium text-slate-800">#{{ $mission->id }}</td>
                                        <td class="px-4 py-3 text-sm text-slate-700">{{ $mission->referencePoint?->reference ?? '—' }}</td>
                                        <td class="px-4 py-3">
                                            <span class="rounded-full bg-slate-100 px-2.5 py-1 text-xs font-semibold text-slate-700">{{ $mission->statut }}</span>
                                        </td>
                                        <td class="px-4 py-3 text-sm text-slate-600">{{ $mission->due_at?->format('Y-m-d H:i') ?? '—' }}</td>
                                        <td class="px-4 py-3 text-right">
                                            <a href="{{ route('missions.show', $mission) }}" class="font-medium text-brand-primary hover:text-brand-primary-dark">Voir</a>
                                        </td>
                                    </tr>
                                @empty
                                    <tr>
                                        <td colspan="5" class="px-4 py-8 text-center text-sm text-slate-500">Aucune mission trouvee.</td>
                                    </tr>
                                @endforelse
                            </tbody>
                        </table>
                    </div>
                </article>
            </section>
        </div>
    </div>

    @push('scripts')
        <style>
            .analysis-hero {
                background:
                    radial-gradient(1200px 300px at 10% -30%, rgba(255, 255, 255, 0.35) 0%, rgba(255, 255, 255, 0) 55%),
                    linear-gradient(120deg, #0f172a 0%, #1d4ed8 45%, #0284c7 100%);
                box-shadow: 0 20px 45px rgba(2, 6, 23, 0.22);
            }

            .kpi-card {
                border-radius: 1rem;
                border: 1px solid #dbe3ef;
                background: #fff;
                padding: 1.1rem 1.1rem 1rem;
                box-shadow: 0 8px 24px rgba(15, 23, 42, 0.05);
            }

            .kpi-card-emerald { background: linear-gradient(150deg, #ecfdf5 0%, #ffffff 80%); border-color: #a7f3d0; }
            .kpi-card-sky { background: linear-gradient(150deg, #f0f9ff 0%, #ffffff 80%); border-color: #bae6fd; }
            .kpi-card-amber { background: linear-gradient(150deg, #fffbeb 0%, #ffffff 80%); border-color: #fde68a; }
            .kpi-card-slate { background: linear-gradient(150deg, #f8fafc 0%, #ffffff 80%); border-color: #cbd5e1; }

            .kpi-label {
                font-size: 0.68rem;
                letter-spacing: 0.16em;
                text-transform: uppercase;
                color: #475569;
                font-weight: 700;
            }

            .kpi-compact {
                border-radius: 0.8rem;
                border: 1px solid #dbe3ef;
                background: #ffffff;
                padding: 0.55rem 0.7rem;
            }

            .kpi-compact-label {
                font-size: 0.63rem;
                letter-spacing: 0.14em;
                text-transform: uppercase;
                color: #64748b;
                font-weight: 700;
            }

            .kpi-compact-value {
                margin-top: 0.1rem;
                font-size: 1.05rem;
                font-weight: 800;
                color: #0f172a;
            }

            .kpi-value {
                margin-top: 0.5rem;
                font-size: 2rem;
                line-height: 1;
                font-weight: 800;
                color: #0f172a;
            }

            .metric-ring-card {
                border-radius: 1rem;
                border: 1px solid #dbe3ef;
                background: #fff;
                padding: 0.85rem;
                display: flex;
                align-items: center;
                gap: 1rem;
                box-shadow: 0 8px 24px rgba(15, 23, 42, 0.05);
            }

            .metric-ring {
                --p: 0;
                --ring: #2563eb;
                --ring-soft: #dbeafe;
                width: 86px;
                height: 86px;
                border-radius: 9999px;
                display: grid;
                place-items: center;
                background: conic-gradient(var(--ring) calc(var(--p) * 1%), var(--ring-soft) 0);
                position: relative;
                flex-shrink: 0;
            }

            .metric-ring::after {
                content: "";
                position: absolute;
                inset: 10px;
                background: white;
                border-radius: inherit;
            }

            .metric-ring span {
                position: relative;
                z-index: 1;
                font-size: 0.86rem;
                font-weight: 700;
                color: #0f172a;
            }

            .metric-title {
                font-size: 0.92rem;
                font-weight: 700;
                color: #0f172a;
            }

            .metric-text {
                margin-top: 0.25rem;
                font-size: 0.78rem;
                color: #64748b;
            }

            .panel-card {
                border-radius: 1rem;
                border: 1px solid #dbe3ef;
                background: #fff;
                overflow: hidden;
                box-shadow: 0 10px 28px rgba(15, 23, 42, 0.06);
            }

            .panel-head {
                padding: 1rem 1.2rem;
                border-bottom: 1px solid #e2e8f0;
                background: linear-gradient(180deg, #f8fafc 0%, #ffffff 100%);
            }

            .panel-head h3 {
                font-size: 1rem;
                font-weight: 700;
                color: #0f172a;
            }

            .panel-head p {
                margin-top: 0.2rem;
                font-size: 0.78rem;
                color: #64748b;
            }

            .panel-body {
                padding: 1rem;
            }
        </style>

        <script>
            function syncAnalysisDateInputs() {
                const periodEl = document.getElementById('period');
                const customDateInputs = document.querySelectorAll('.analysis-custom-date');

                if (!periodEl || customDateInputs.length === 0) {
                    return;
                }

                const toggle = () => {
                    const isCustom = periodEl.value === 'custom';
                    customDateInputs.forEach((input) => {
                        input.classList.toggle('hidden', !isCustom);
                    });
                };

                periodEl.addEventListener('change', toggle);
                toggle();
            }

            async function ensureChartJs() {
                if (window.Chart) {
                    return;
                }

                await new Promise((resolve, reject) => {
                    const existing = document.querySelector('script[data-chartjs="1"]');
                    if (existing) {
                        existing.addEventListener('load', resolve, { once: true });
                        existing.addEventListener('error', reject, { once: true });
                        return;
                    }

                    const script = document.createElement('script');
                    script.src = 'https://cdn.jsdelivr.net/npm/chart.js@4.4.3/dist/chart.umd.min.js';
                    script.async = true;
                    script.dataset.chartjs = '1';
                    script.onload = resolve;
                    script.onerror = reject;
                    document.head.appendChild(script);
                });
            }

            async function initAnalysisCharts() {
                try {
                    await ensureChartJs();

                    const trendCtx = document.getElementById('trendChart');
                    const statusCtx = document.getElementById('statusChart');
                    if (!trendCtx || !statusCtx) {
                        return;
                    }

                    const monthlyLabels = @json($monthlyLabels);
                    const monthlyCompleted = @json($monthlyCompleted);
                    const monthlyAssigned = @json($monthlyAssigned);

                    const statusLabels = @json($statusLabels);
                    const statusData = @json($statusData);

                    new window.Chart(trendCtx, {
                        type: 'line',
                        data: {
                            labels: monthlyLabels,
                            datasets: [
                                {
                                    label: 'Assignees',
                                    data: monthlyAssigned,
                                    borderColor: '#64748b',
                                    backgroundColor: 'rgba(100, 116, 139, 0.15)',
                                    borderWidth: 2,
                                    tension: 0.35,
                                    fill: true,
                                    pointRadius: 4,
                                    pointHoverRadius: 5,
                                },
                                {
                                    label: 'Terminees',
                                    data: monthlyCompleted,
                                    borderColor: '#0284c7',
                                    backgroundColor: 'rgba(2, 132, 199, 0.20)',
                                    borderWidth: 3,
                                    tension: 0.35,
                                    fill: true,
                                    pointRadius: 4,
                                    pointHoverRadius: 5,
                                },
                            ],
                        },
                        options: {
                            responsive: true,
                            maintainAspectRatio: false,
                            plugins: {
                                legend: {
                                    position: 'top',
                                    labels: { boxWidth: 12, boxHeight: 12, usePointStyle: true },
                                },
                            },
                            scales: {
                                y: {
                                    beginAtZero: true,
                                    ticks: { precision: 0 },
                                    grid: { color: 'rgba(148, 163, 184, 0.2)' },
                                },
                                x: {
                                    grid: { display: false },
                                },
                            },
                        },
                    });

                    new window.Chart(statusCtx, {
                        type: 'doughnut',
                        data: {
                            labels: statusLabels,
                            datasets: [
                                {
                                    data: statusData,
                                    backgroundColor: ['#10b981', '#0284c7', '#f59e0b', '#94a3b8', '#cbd5e1'],
                                    borderWidth: 0,
                                    hoverOffset: 8,
                                },
                            ],
                        },
                        options: {
                            responsive: true,
                            maintainAspectRatio: false,
                            cutout: '64%',
                            plugins: {
                                legend: {
                                    position: 'bottom',
                                    labels: { boxWidth: 10, boxHeight: 10, usePointStyle: true },
                                },
                            },
                        },
                    });
                } catch (error) {
                    console.error('Charts failed to load', error);
                }
            }

            initAnalysisCharts();
            syncAnalysisDateInputs();
        </script>
    @endpush
</x-app-layout>
