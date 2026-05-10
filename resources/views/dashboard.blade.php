<x-app-layout>
    <x-slot name="header">
        <div class="page-header">
            <p class="text-xs font-semibold uppercase tracking-[0.28em] text-sky-100/90">Centre de supervision</p>
            <h2 class="mt-2 text-2xl font-bold leading-tight text-white">{{ __('Dashboard') }}</h2>
            <p class="mt-2 max-w-3xl text-sm text-sky-100/90">Recherchez une reference compteur, visualisez sa position et declenchez rapidement les actions metier.</p>
        </div>
    </x-slot>

    <div class="py-10">
        <div class="mx-auto max-w-7xl space-y-6 px-4 sm:px-6 lg:px-8">

            {{-- Recherche Référence Compteur --}}
            <section class="panel-card p-6">
                <h3 class="mb-4 flex items-center gap-2 text-lg font-semibold text-slate-800">
                    <svg class="h-5 w-5 text-sky-700" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z"></path>
                    </svg>
                    Recherche Référence Compteur
                </h3>

                <div class="flex flex-col sm:flex-row gap-3">
                    <div class="relative flex-1">
                        <input id="refInput" 
                               class="w-full rounded-xl border-slate-300 bg-white px-4 py-3 text-sm focus:border-sky-600 focus:ring-sky-600" 
                               placeholder="Ex: 717717770" />
                        <svg class="absolute right-3 top-3.5 h-5 w-5 text-slate-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z"></path>
                        </svg>
                    </div>
                    <button id="searchBtn" 
                            class="btn-primary px-6 py-3 text-[11px]">
                        Rechercher
                    </button>
                    <span id="status" class="self-center text-sm text-slate-600 flex items-center gap-1">
                        <span class="inline-block h-1.5 w-1.5 rounded-full bg-slate-400"></span>
                        Prêt
                    </span>
                </div>

                <div id="actions" class="mt-4 hidden flex flex-wrap gap-3">
                    @can('manage-missions')
                        <a id="createMissionLink" href="#" 
                           class="btn-primary gap-2 px-4 py-2.5">
                            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4"></path>
                            </svg>
                            Créer une mission
                        </a>
                    @endcan
                    <a id="historyLink" href="#" 
                       class="btn-secondary gap-2 px-4 py-2.5">
                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z"></path>
                        </svg>
                        Historique des missions
                    </a>
                    <a id="googleMapsLink" href="#" target="_blank" rel="noopener"
                       class="hidden inline-flex items-center gap-2 rounded-xl bg-amber-500 px-4 py-2.5 text-xs font-semibold uppercase tracking-[0.12em] text-white transition hover:bg-amber-600">
                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17.657 16.657L13.414 20.9a2 2 0 01-2.828 0L6.343 16.657a8 8 0 1111.314 0z"></path>
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 11a3 3 0 11-6 0 3 3 0 016 0z"></path>
                        </svg>
                        Ouvrir Google Maps
                    </a>
                    @if (auth()->user()?->role === 'Technicien')
                        <a id="historyForMeLink" href="{{ route('missions.index', ['mine' => 1]) }}"
                           class="btn-secondary gap-2 px-4 py-2.5">
                            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12h6m-3-3v6m8 4H4a1 1 0 01-1-1V6a1 1 0 011-1h5.586a1 1 0 01.707.293l1.414 1.414A1 1 0 0012.414 7H20a1 1 0 011 1v10a1 1 0 01-1 1z"></path>
                            </svg>
                            Historique pour moi
                        </a>

                        <a id="analysisLink" href="{{ route('missions.analysis') }}"
                           class="btn-secondary gap-2 px-4 py-2.5">
                            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11 3v18m-4-8h8m6 8H3a1 1 0 01-1-1V4a1 1 0 011-1h18a1 1 0 011 1v16a1 1 0 01-1 1z"></path>
                            </svg>
                            Analyse de travail
                        </a>
                    @endif
                    <a id="editRefLink" href="#" 
                       class="hidden btn-secondary gap-2 px-4 py-2.5">
                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15.232 5.232l3.536 3.536m-2.036-5.036a2.5 2.5 0 113.536 3.536L6.5 21.036H3v-3.572L16.732 3.732z"></path>
                        </svg>
                        Modifier / Valider position
                    </a>
                </div>
            </section>

            {{-- Carte --}}
            <section class="panel-card overflow-hidden p-6">
                <div id="map" style="height: 520px; border-radius: 0.9rem; overflow: hidden;" class="border border-slate-200 shadow-inner"></div>
            </section>

        </div>
    </div>

    @push('scripts')
        <style>
            @keyframes pulse {
                0%, 100% { opacity: 1; }
                50% { opacity: 0.5; }
            }
            .loading-pulse {
                animation: pulse 1.5s ease-in-out infinite;
            }
        </style>
        
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

                if (!window.L) throw new Error('Leaflet not available');
            }

            let map;
            let marker;

            const actionsEl = document.getElementById('actions');
            const createMissionLink = document.getElementById('createMissionLink');
            const historyLink = document.getElementById('historyLink');
            const googleMapsLink = document.getElementById('googleMapsLink');
            const editRefLink = document.getElementById('editRefLink');
            const canManageReferences = @json(auth()->user()?->can('manage-references') ?? false);
            const canManageMissions = @json(auth()->user()?->can('manage-missions') ?? false);
            const isTechnicien = @json((auth()->user()?->role ?? null) === 'Technicien');

            function updateStatus(message, isError = false, isLoading = false) {
                const status = document.getElementById('status');
                const dot = status.querySelector('span');
                
                if (isLoading) {
                    status.innerHTML = `<span class="inline-block w-1.5 h-1.5 rounded-full bg-blue-500 loading-pulse"></span><span class="ml-1">${message}</span>`;
                } else if (isError) {
                    status.innerHTML = `<span class="inline-block w-1.5 h-1.5 rounded-full bg-red-500"></span><span class="ml-1 text-red-600">${message}</span>`;
                } else {
                    status.innerHTML = `<span class="inline-block w-1.5 h-1.5 rounded-full bg-green-500"></span><span class="ml-1">${message}</span>`;
                }
            }

            function hideActions() {
                actionsEl.classList.add('hidden');
                editRefLink.classList.add('hidden');
                if (createMissionLink) {
                    createMissionLink.href = '#';
                }
                historyLink.href = '#';
                if (googleMapsLink) {
                    googleMapsLink.href = '#';
                    googleMapsLink.classList.add('hidden');
                }
                editRefLink.href = '#';
            }

            function showActions(referencePointId, lat, lng) {
                actionsEl.classList.remove('hidden');
                if (canManageMissions && createMissionLink) {
                    createMissionLink.href = `{{ route('missions.create') }}?reference_id=${encodeURIComponent(referencePointId)}`;
                }

                historyLink.href = isTechnicien
                    ? `{{ route('missions.index') }}?mine=1&reference_id=${encodeURIComponent(referencePointId)}`
                    : `{{ route('missions.index') }}?reference_id=${encodeURIComponent(referencePointId)}`;

                if (canManageReferences) {
                    editRefLink.classList.remove('hidden');
                    editRefLink.href = `{{ url('/reference-points') }}/${encodeURIComponent(referencePointId)}/edit`;
                }

                if (googleMapsLink && Number.isFinite(lat) && Number.isFinite(lng)) {
                    googleMapsLink.href = `https://www.google.com/maps/search/?api=1&query=${encodeURIComponent(`${lat},${lng}`)}`;
                    googleMapsLink.classList.remove('hidden');
                }
            }

            async function initMap() {
                try {
                    await ensureLeaflet();
                    map = window.L.map('map').setView([34.7406, 10.7603], 12);
                    
                    // Custom tile layer with better styling
                    window.L.tileLayer('https://{s}.basemaps.cartocdn.com/light_all/{z}/{x}/{y}{r}.png', {
                        attribution: '&copy; <a href="https://www.openstreetmap.org/copyright">OpenStreetMap</a> &copy; <a href="https://carto.com/attributions">CARTO</a>',
                        subdomains: 'abcd',
                        maxZoom: 19
                    }).addTo(map);
                    
                    updateStatus('Carte chargée');
                } catch (e) {
                    updateStatus("Leaflet n'est pas chargé. Démarrez Vite (npm run dev) ou rechargez la page.", true);
                }
            }

            async function searchRef() {
                const ref = document.getElementById('refInput').value.trim();
                updateStatus('');

                hideActions();

                if (!ref) {
                    updateStatus('Veuillez saisir une référence.', true);
                    return;
                }

                try {
                    if (!map) await initMap();
                    if (!map) return;

                    updateStatus('Recherche en cours...', false, true);
                    const res = await fetch(`/api/references/${encodeURIComponent(ref)}`);

                    const contentType = res.headers.get('content-type') || '';
                    if (!contentType.includes('application/json')) {
                        if (res.redirected) {
                            updateStatus('Session expirée. Rechargez la page.', true);
                            return;
                        }
                        updateStatus('Réponse invalide du serveur.', true);
                        return;
                    }

                    const data = await res.json();

                    if (!res.ok) {
                        updateStatus(data?.message ?? 'Erreur', true);
                        return;
                    }

                    const latlng = [data.latitude, data.longitude];
                    const referencePointId = data.id;

                    if (!referencePointId) {
                        updateStatus('Référence trouvée mais ID manquant (API).', true);
                        return;
                    }

                    if (marker) marker.remove();

                    const lat = parseFloat(latlng[0]);
                    const lng = parseFloat(latlng[1]);
                    if (!Number.isFinite(lat) || !Number.isFinite(lng)) {
                        updateStatus('Coordonnées invalides.', true);
                        return;
                    }

                    // Custom marker with popup styling
                    const customIcon = window.L.divIcon({
                        className: 'custom-marker',
                        html: '<div class="w-6 h-6 bg-blue-600 rounded-full border-4 border-white shadow-lg"></div>',
                        iconSize: [24, 24],
                        iconAnchor: [12, 12]
                    });

                    marker = window.L.marker(latlng, { icon: customIcon })
                        .addTo(map)
                        .bindPopup(`
                            <div class="text-center">
                                <strong class="text-blue-600">${data.reference}</strong>
                                ${data.adresse ? `<br/><span class="text-gray-600 text-sm">${data.adresse}</span>` : ''}
                            </div>
                        `)
                        .openPopup();

                    map.setView(latlng, 16);
                    updateStatus('Référence trouvée !');
                    showActions(referencePointId, lat, lng);
                } catch (e) {
                    console.error(e);
                    updateStatus('Erreur (JS ou réseau). Vérifiez que Vite est démarré.', true);
                }
            }

            document.getElementById('searchBtn').addEventListener('click', searchRef);
            document.getElementById('refInput').addEventListener('keydown', (e) => {
                if (e.key === 'Enter') searchRef();
            });

            // Subtle entry animation for dashboard cards.
            document.addEventListener('DOMContentLoaded', () => {
                const cards = document.querySelectorAll('.panel-card');
                cards.forEach((card, index) => {
                    card.style.opacity = '0';
                    card.style.transform = 'translateY(20px)';
                    setTimeout(() => {
                        card.style.transition = 'all 0.5s ease-out';
                        card.style.opacity = '1';
                        card.style.transform = 'translateY(0)';
                    }, index * 100);
                });
            });

            initMap();
        </script>
        
        <style>
            .custom-marker {
                background: transparent;
                border: none;
            }
            
            .leaflet-popup-content-wrapper {
                border-radius: 12px;
                box-shadow: 0 4px 15px rgba(0,0,0,0.1);
            }
            
            .leaflet-popup-tip {
                box-shadow: 0 4px 15px rgba(0,0,0,0.1);
            }
        </style>
    @endpush
</x-app-layout>