<x-app-layout>
    <x-slot name="header">
        <div class="flex items-center justify-between">
            <h2 class="font-semibold text-xl text-gray-800 leading-tight">
                Modifier / Valider la position
            </h2>
            <div class="flex items-center gap-3">
                <a href="{{ route('reference.search') }}" class="text-gray-600 hover:text-gray-900">Retour recherche</a>

                @can('manage-references')
                    <form method="POST" action="{{ route('reference-points.destroy', $referencePoint) }}" onsubmit="return confirm('Archiver (supprimer) cette référence ?')">
                        @csrf
                        @method('DELETE')
                        <button type="submit" class="inline-flex items-center px-3 py-2 rounded-md bg-red-600 text-white hover:bg-red-700">
                            Archiver
                        </button>
                    </form>
                @endcan
            </div>
        </div>
    </x-slot>

    <div class="py-12">
        <div class="max-w-5xl mx-auto sm:px-6 lg:px-8 space-y-6">
            @if (session('status'))
                <div class="rounded border border-green-200 bg-green-50 px-4 py-2 text-green-800">
                    {{ session('status') }}
                </div>
            @endif

            <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg">
                <div class="p-6 text-gray-900">
                    <div class="mb-4">
                        <div class="text-xs uppercase text-gray-500">Référence</div>
                        <div class="text-lg font-semibold">{{ $referencePoint->reference }}</div>
                        <div class="text-sm text-gray-600">{{ $referencePoint->adresse }}</div>
                    </div>

                    <div class="grid grid-cols-1 lg:grid-cols-2 gap-6">
                        <div>
                            <div id="map" style="height: 420px;"></div>
                            <p class="mt-2 text-xs text-gray-500">Cliquez sur la carte pour définir la position (lat/lng).</p>
                        </div>

                        <div>
                            <form method="POST" action="{{ route('reference-points.update', $referencePoint) }}" class="space-y-4">
                                @csrf
                                @method('PUT')

                                <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                                    <div>
                                        <x-input-label for="latitude" :value="__('Latitude')" />
                                        <x-text-input id="latitude" name="latitude" type="text" class="mt-1 block w-full" :value="old('latitude', $referencePoint->latitude)" required />
                                        <x-input-error :messages="$errors->get('latitude')" class="mt-2" />
                                    </div>

                                    <div>
                                        <x-input-label for="longitude" :value="__('Longitude')" />
                                        <x-text-input id="longitude" name="longitude" type="text" class="mt-1 block w-full" :value="old('longitude', $referencePoint->longitude)" required />
                                        <x-input-error :messages="$errors->get('longitude')" class="mt-2" />
                                    </div>
                                </div>

                                <div>
                                    <x-input-label for="adresse" :value="__('Adresse')" />
                                    <textarea id="adresse" name="adresse" rows="3" class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500">{{ old('adresse', $referencePoint->adresse) }}</textarea>
                                    <x-input-error :messages="$errors->get('adresse')" class="mt-2" />
                                </div>

                                <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                                    <div>
                                        <x-input-label for="gouvernorat" :value="__('Gouvernorat')" />
                                        <x-text-input id="gouvernorat" name="gouvernorat" type="text" class="mt-1 block w-full" :value="old('gouvernorat', $referencePoint->gouvernorat)" />
                                        <x-input-error :messages="$errors->get('gouvernorat')" class="mt-2" />
                                    </div>
                                    <div>
                                        <x-input-label for="delegation" :value="__('Délégation')" />
                                        <x-text-input id="delegation" name="delegation" type="text" class="mt-1 block w-full" :value="old('delegation', $referencePoint->delegation)" />
                                        <x-input-error :messages="$errors->get('delegation')" class="mt-2" />
                                    </div>
                                </div>

                                <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                                    <div>
                                        <x-input-label for="precision_m" :value="__('Précision (m)')" />
                                        <x-text-input id="precision_m" name="precision_m" type="number" class="mt-1 block w-full" :value="old('precision_m', $referencePoint->precision_m)" />
                                        <x-input-error :messages="$errors->get('precision_m')" class="mt-2" />
                                    </div>
                                    <div>
                                        <x-input-label for="statut" :value="__('Statut')" />
                                        <select id="statut" name="statut" class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500" required>
                                            @foreach (['à vérifier', 'validé', 'rejeté'] as $s)
                                                <option value="{{ $s }}" {{ old('statut', $referencePoint->statut) === $s ? 'selected' : '' }}>
                                                    {{ $s }}
                                                </option>
                                            @endforeach
                                        </select>
                                        <x-input-error :messages="$errors->get('statut')" class="mt-2" />
                                    </div>
                                </div>

                                <div class="flex items-center justify-end gap-3">
                                    <x-primary-button>Enregistrer</x-primary-button>
                                </div>
                            </form>
                        </div>
                    </div>
                </div>
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

                if (!window.L) throw new Error('Leaflet not available');
            }

            async function init() {
                await ensureLeaflet();

                const latInput = document.getElementById('latitude');
                const lngInput = document.getElementById('longitude');

                const lat = parseFloat(latInput.value);
                const lng = parseFloat(lngInput.value);

                const map = window.L.map('map').setView([lat, lng], 16);
                window.L.tileLayer('https://{s}.tile.openstreetmap.org/{z}/{x}/{y}.png', {
                    attribution: '&copy; OpenStreetMap contributors'
                }).addTo(map);

                let marker = window.L.marker([lat, lng]).addTo(map);

                map.on('click', (e) => {
                    const { lat, lng } = e.latlng;
                    latInput.value = lat.toFixed(8);
                    lngInput.value = lng.toFixed(8);

                    if (marker) marker.remove();
                    marker = window.L.marker([lat, lng]).addTo(map);
                });
            }

            init().catch(() => {
                // If Leaflet fails, map stays empty; form still usable.
            });
        </script>
    @endpush
</x-app-layout>
