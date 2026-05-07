<x-app-layout>
    <x-slot name="header">
        <div class="rounded-2xl bg-gradient-to-r from-slate-950 via-slate-800 to-emerald-700 px-6 py-5 text-white shadow-lg">
            <p class="text-xs font-semibold uppercase tracking-[0.3em] text-emerald-100">Carte opérationnelle</p>
            <h2 class="mt-2 text-2xl font-bold">Missions et points de référence</h2>
            <p class="mt-2 max-w-3xl text-sm text-emerald-100/90">Filtrer les interventions par statut et visualiser les sites sur une carte unique.</p>
        </div>
    </x-slot>

    <div class="py-10">
        <div class="mx-auto max-w-7xl space-y-6 px-4 sm:px-6 lg:px-8">
            <form method="GET" action="{{ route('missions.map') }}" class="panel-card flex flex-wrap items-end gap-4 p-5">
                <label>
                    <span class="mb-1 block text-xs font-semibold uppercase tracking-wider text-slate-500">Statut</span>
                    <select name="status" class="rounded-xl border-slate-300 bg-white px-3 py-2 text-sm focus:border-emerald-500 focus:ring-emerald-500">
                        <option value="">Tous</option>
                        @foreach ($statusOptions as $option)
                            <option value="{{ $option }}" @selected($status === $option)>{{ $option }}</option>
                        @endforeach
                    </select>
                </label>
                <button type="submit" class="rounded-xl bg-slate-950 px-4 py-2 text-sm font-semibold text-white transition hover:bg-slate-800">Filtrer</button>
                <a href="{{ route('missions.map') }}" class="rounded-xl border border-slate-300 px-4 py-2 text-sm font-semibold text-slate-700 transition hover:bg-slate-50">Réinitialiser</a>
            </form>

            <section class="panel-card overflow-hidden">
                <div class="panel-head">
                    <h3>Carte des missions</h3>
                    <p>{{ $missions->count() }} points affichés</p>
                </div>
                <div id="mission-map" class="h-[680px] w-full"></div>
            </section>
        </div>
    </div>

    @php
        $missionsMapData = $missions->map(function ($mission) {
            return [
                'id' => $mission->id,
                'status' => $mission->statut,
                'reference' => $mission->referencePoint?->reference,
                'address' => $mission->referencePoint?->adresse,
                'lat' => (float) ($mission->referencePoint?->latitude ?? 0),
                'lng' => (float) ($mission->referencePoint?->longitude ?? 0),
                'client' => $mission->client?->name,
            ];
        })->values();
    @endphp

    @push('scripts')
        <script>
            async function ensureLeaflet() {
                if (window.L) return;

                const leafletCssHref = 'https://unpkg.com/leaflet@1.9.4/dist/leaflet.css';
                const leafletJsSrc = 'https://unpkg.com/leaflet@1.9.4/dist/leaflet.js';

                if (!document.querySelector(`link[href="${leafletCssHref}"]`)) {
                    const link = document.createElement('link');
                    link.rel = 'stylesheet';
                    link.href = leafletCssHref;
                    document.head.appendChild(link);
                }

                await new Promise((resolve, reject) => {
                    if (document.querySelector(`script[src="${leafletJsSrc}"]`)) {
                        return resolve();
                    }
                    const script = document.createElement('script');
                    script.src = leafletJsSrc;
                    script.async = true;
                    script.onload = () => resolve();
                    script.onerror = () => reject(new Error('Leaflet failed to load'));
                    document.head.appendChild(script);
                });
            }

            (async () => {
                try {
                    await ensureLeaflet();
                    const map = window.L.map('mission-map').setView([34.7406, 10.7603], 8);

                    window.L.tileLayer('https://{s}.basemaps.cartocdn.com/light_all/{z}/{x}/{y}{r}.png', {
                        attribution: '&copy; OpenStreetMap &copy; CARTO',
                        subdomains: 'abcd',
                        maxZoom: 19
                    }).addTo(map);

                    const missions = @json($missionsMapData);

                    const statusColors = {
                        'Terminée': '#059669',
                        'En cours': '#0284c7',
                        'Créée': '#6366f1',
                        'Bloquée': '#d97706',
                        'Annulée': '#dc2626',
                    };

                    const bounds = [];
                    missions.forEach((mission) => {
                        if (!mission.lat || !mission.lng) {
                            return;
                        }

                        bounds.push([mission.lat, mission.lng]);
                        const color = statusColors[mission.status] || '#334155';
                        const marker = window.L.circleMarker([mission.lat, mission.lng], {
                            radius: 8,
                            color,
                            fillColor: color,
                            fillOpacity: 0.9,
                            weight: 2,
                        }).addTo(map);

                        marker.bindPopup(`
                            <div style="min-width: 180px">
                                <div style="font-weight: 700; margin-bottom: 4px">Mission #${mission.id}</div>
                                <div style="font-size: 12px; color: #475569">${mission.reference ?? 'Référence'} · ${mission.status}</div>
                                <div style="font-size: 12px; color: #64748b; margin-top: 4px">${mission.address ?? ''}</div>
                                <div style="font-size: 12px; color: #0f172a; margin-top: 4px">${mission.client ?? ''}</div>
                            </div>
                        `);
                    });

                    if (bounds.length > 0) {
                        map.fitBounds(bounds, { padding: [30, 30] });
                    }
                } catch (error) {
                    document.getElementById('mission-map').innerHTML = '<div class="flex h-full items-center justify-center text-sm text-slate-500">Impossible de charger la carte.</div>';
                }
            })();
        </script>
    @endpush
</x-app-layout>
