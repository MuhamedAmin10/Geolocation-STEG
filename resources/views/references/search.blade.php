<x-app-layout>
    <x-slot name="header">
        <h2 class="font-semibold text-xl text-gray-800 leading-tight">
            Recherche Référence Compteur
        </h2>
    </x-slot>

    <div class="py-6">
        <div class="max-w-7xl mx-auto sm:px-6 lg:px-8 space-y-4">
            <div class="bg-white p-4 shadow sm:rounded-lg">
                <div class="flex flex-col sm:flex-row gap-2">
                    <input id="refInput" class="border rounded px-3 py-2 w-full sm:w-80" placeholder="Ex: 717717770" />
                    <button id="searchBtn" class="bg-blue-600 text-white px-4 py-2 rounded">Rechercher</button>
                    <span id="status" class="text-sm text-gray-600 self-center"></span>
                </div>

                <div id="actions" class="mt-3 hidden flex flex-wrap gap-2">
                    <a id="createMissionLink" href="#" class="px-3 py-2 rounded-md bg-gray-800 text-white hover:bg-gray-700">Créer une mission</a>
                    <a id="historyLink" href="#" class="px-3 py-2 rounded-md bg-gray-800 text-white hover:bg-gray-700">Historique des missions</a>
                    <a id="editRefLink" href="#" class="px-3 py-2 rounded-md bg-gray-800 text-white hover:bg-gray-700 hidden">Modifier / Valider position</a>
                </div>
            </div>

            <div class="bg-white p-4 shadow sm:rounded-lg">
                <div id="map" style="height: 520px;"></div>
            </div>
        </div>
    </div>

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

                if (!window.L) {
                    throw new Error('Leaflet not available');
                }
            }

            let map;
            let marker;

            const actionsEl = document.getElementById('actions');
            const createMissionLink = document.getElementById('createMissionLink');
            const historyLink = document.getElementById('historyLink');
            const editRefLink = document.getElementById('editRefLink');
            const canManageReferences = @json(auth()->user()?->can('manage-references') ?? false);

            function hideActions() {
                actionsEl.classList.add('hidden');
                editRefLink.classList.add('hidden');
                createMissionLink.href = '#';
                historyLink.href = '#';
                editRefLink.href = '#';
            }

            function showActions(referencePointId) {
                actionsEl.classList.remove('hidden');
                createMissionLink.href = `{{ route('missions.create') }}?reference_id=${encodeURIComponent(referencePointId)}`;
                historyLink.href = `{{ route('missions.index') }}?reference_id=${encodeURIComponent(referencePointId)}`;

                if (canManageReferences) {
                    editRefLink.classList.remove('hidden');
                    editRefLink.href = `{{ url('/reference-points') }}/${encodeURIComponent(referencePointId)}/edit`;
                }
            }

            async function initMap() {
                const status = document.getElementById('status');

                try {
                    await ensureLeaflet();
                    map = window.L.map('map').setView([34.7406, 10.7603], 12);
                    window.L.tileLayer('https://{s}.tile.openstreetmap.org/{z}/{x}/{y}.png', {
                        attribution: '&copy; OpenStreetMap contributors'
                    }).addTo(map);
                } catch (e) {
                    status.textContent = "Leaflet n'est pas chargé. Démarrez Vite (npm run dev) ou rechargez la page.";
                }
            }

            async function searchRef() {
                const ref = document.getElementById('refInput').value.trim();
                const status = document.getElementById('status');
                status.textContent = '';
                hideActions();

                if (!ref) {
                    status.textContent = 'Veuillez saisir une référence.';
                    return;
                }

                try {
                    if (!map) {
                        await initMap();
                    }

                    if (!map) {
                        return;
                    }

                    status.textContent = 'Recherche...';
                    const res = await fetch(`/api/references/${encodeURIComponent(ref)}`);

                    const contentType = res.headers.get('content-type') || '';
                    if (!contentType.includes('application/json')) {
                        if (res.redirected) {
                            status.textContent = 'Session expirée. Rechargez la page.';
                            return;
                        }

                        status.textContent = 'Réponse invalide du serveur.';
                        return;
                    }

                    const data = await res.json();

                    if (!res.ok) {
                        status.textContent = data?.message ?? 'Erreur';
                        return;
                    }

                    const latlng = [data.latitude, data.longitude];
                    const referencePointId = data.id;

                    if (!referencePointId) {
                        status.textContent = 'Référence trouvée mais ID manquant (API).';
                        return;
                    }

                    if (marker) marker.remove();

                    const lat = parseFloat(latlng[0]);
                    const lng = parseFloat(latlng[1]);
                    if (!Number.isFinite(lat) || !Number.isFinite(lng)) {
                        status.textContent = 'Coordonnées invalides.';
                        return;
                    }

                    marker = window.L.marker(latlng)
                        .addTo(map)
                        .bindPopup(`<b>${data.reference}</b><br/>${data.adresse ?? ''}`)
                        .openPopup();

                    map.setView(latlng, 16);
                    status.textContent = 'OK';
                    showActions(referencePointId);
                } catch (e) {
                    status.textContent = 'Erreur (JS ou réseau). Vérifiez que Vite est démarré.';
                }
            }

            document.getElementById('searchBtn').addEventListener('click', searchRef);
            document.getElementById('refInput').addEventListener('keydown', (e) => {
                if (e.key === 'Enter') searchRef();
            });

            initMap();
        </script>
    @endpush
</x-app-layout>