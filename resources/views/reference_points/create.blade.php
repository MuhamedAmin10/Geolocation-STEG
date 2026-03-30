<x-app-layout>
    <x-slot name="header">
        <div class="flex items-center justify-between">
            <h2 class="font-semibold text-xl text-gray-800 leading-tight">
                Ajouter une référence
            </h2>
            <a href="{{ route('admin.dashboard') }}" class="text-gray-600 hover:text-gray-900 text-sm">← Retour admin</a>
        </div>
    </x-slot>

    <div class="py-12">
        <div class="max-w-5xl mx-auto sm:px-6 lg:px-8 space-y-6">

            @if ($errors->any())
                <div class="rounded border border-red-200 bg-red-50 px-4 py-2 text-red-700">
                    <ul class="list-disc list-inside space-y-1">
                        @foreach ($errors->all() as $error)
                            <li>{{ $error }}</li>
                        @endforeach
                    </ul>
                </div>
            @endif

            <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg">
                <div class="p-6 text-gray-900">
                    <div class="grid grid-cols-1 lg:grid-cols-2 gap-6">

                        {{-- Map for picking coordinates --}}
                        <div>
                            <div id="map" style="height: 420px;"></div>
                            <p class="mt-2 text-xs text-gray-500">Cliquez sur la carte pour remplir automatiquement les coordonnées.</p>
                        </div>

                        {{-- Form --}}
                        <div>
                            <form method="POST" action="{{ route('reference-points.store') }}" class="space-y-4">
                                @csrf

                                <div>
                                    <x-input-label for="reference" :value="__('Référence compteur')" />
                                    <x-text-input id="reference" name="reference" type="text" class="mt-1 block w-full" :value="old('reference')" required autofocus placeholder="Ex: 717717770" />
                                    <x-input-error :messages="$errors->get('reference')" class="mt-2" />
                                </div>

                                <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                                    <div>
                                        <x-input-label for="latitude" :value="__('Latitude')" />
                                        <x-text-input id="latitude" name="latitude" type="text" class="mt-1 block w-full" :value="old('latitude')" required placeholder="Ex: 34.7406" />
                                        <x-input-error :messages="$errors->get('latitude')" class="mt-2" />
                                    </div>
                                    <div>
                                        <x-input-label for="longitude" :value="__('Longitude')" />
                                        <x-text-input id="longitude" name="longitude" type="text" class="mt-1 block w-full" :value="old('longitude')" required placeholder="Ex: 10.7603" />
                                        <x-input-error :messages="$errors->get('longitude')" class="mt-2" />
                                    </div>
                                </div>

                                <div>
                                    <x-input-label for="adresse" :value="__('Adresse')" />
                                    <textarea id="adresse" name="adresse" rows="3"
                                        class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500"
                                        placeholder="Adresse complète (optionnel)">{{ old('adresse') }}</textarea>
                                    <x-input-error :messages="$errors->get('adresse')" class="mt-2" />
                                </div>

                                <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                                    <div>
                                        <x-input-label for="gouvernorat" :value="__('Gouvernorat')" />
                                        <x-text-input id="gouvernorat" name="gouvernorat" type="text" class="mt-1 block w-full" :value="old('gouvernorat')" />
                                        <x-input-error :messages="$errors->get('gouvernorat')" class="mt-2" />
                                    </div>
                                    <div>
                                        <x-input-label for="delegation" :value="__('Délégation')" />
                                        <x-text-input id="delegation" name="delegation" type="text" class="mt-1 block w-full" :value="old('delegation')" />
                                        <x-input-error :messages="$errors->get('delegation')" class="mt-2" />
                                    </div>
                                </div>

                                <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                                    <div>
                                        <x-input-label for="precision_m" :value="__('Précision (m)')" />
                                        <x-text-input id="precision_m" name="precision_m" type="number" min="0" class="mt-1 block w-full" :value="old('precision_m')" />
                                        <x-input-error :messages="$errors->get('precision_m')" class="mt-2" />
                                    </div>
                                    <div>
                                        <x-input-label for="statut" :value="__('Statut')" />
                                        <select id="statut" name="statut"
                                            class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500"
                                            required>
                                            @foreach (['à vérifier', 'validé', 'rejeté'] as $s)
                                                <option value="{{ $s }}" {{ old('statut', 'à vérifier') === $s ? 'selected' : '' }}>
                                                    {{ $s }}
                                                </option>
                                            @endforeach
                                        </select>
                                        <x-input-error :messages="$errors->get('statut')" class="mt-2" />
                                    </div>
                                </div>

                                <div class="flex items-center gap-4 pt-2">
                                    <button type="submit"
                                        class="inline-flex items-center px-5 py-2 bg-green-600 text-white rounded-md text-sm font-semibold hover:bg-green-700">
                                        Enregistrer
                                    </button>
                                    <a href="{{ route('admin.dashboard') }}" class="text-sm text-gray-600 hover:text-gray-800">Annuler</a>
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
            async function initMap() {
                const leafletCssHref = 'https://unpkg.com/leaflet@1.9.4/dist/leaflet.css';
                const leafletJsSrc  = 'https://unpkg.com/leaflet@1.9.4/dist/leaflet.js';

                if (!document.querySelector(`link[href="${leafletCssHref}"]`)) {
                    const link = document.createElement('link');
                    link.rel = 'stylesheet';
                    link.href = leafletCssHref;
                    document.head.appendChild(link);
                }

                await new Promise((resolve, reject) => {
                    if (window.L) return resolve();
                    const script = document.createElement('script');
                    script.src = leafletJsSrc;
                    script.onload = () => resolve();
                    script.onerror = () => reject();
                    document.head.appendChild(script);
                });

                const defaultLat = parseFloat(document.getElementById('latitude').value) || 34.7406;
                const defaultLng = parseFloat(document.getElementById('longitude').value) || 10.7603;

                const map = window.L.map('map').setView([defaultLat, defaultLng], 12);
                window.L.tileLayer('https://{s}.tile.openstreetmap.org/{z}/{x}/{y}.png', {
                    attribution: '&copy; OpenStreetMap contributors'
                }).addTo(map);

                let marker = null;

                // If existing values, show marker
                if (document.getElementById('latitude').value && document.getElementById('longitude').value) {
                    marker = window.L.marker([defaultLat, defaultLng]).addTo(map);
                }

                map.on('click', (e) => {
                    const { lat, lng } = e.latlng;
                    document.getElementById('latitude').value  = lat.toFixed(8);
                    document.getElementById('longitude').value = lng.toFixed(8);
                    if (marker) marker.remove();
                    marker = window.L.marker([lat, lng]).addTo(map);
                });
            }

            initMap();
        </script>
    @endpush
</x-app-layout>
