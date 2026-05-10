<x-app-layout>
    <x-slot name="header">
        <div class="page-header">
            <p class="text-xs font-semibold uppercase tracking-[0.3em] text-sky-100">Collecte terrain</p>
            <h2 class="mt-2 text-2xl font-bold">Collecte des references compteur</h2>
            <p class="mt-2 max-w-3xl text-sm text-sky-100/90">Scannez les compteurs d'une zone pour enrichir automatiquement la base des references de la ville.</p>
        </div>
    </x-slot>

    <div class="py-10">
        <div class="mx-auto max-w-7xl space-y-6 px-4 sm:px-6 lg:px-8">
            <section
                class="panel-card p-6"
                x-data="referenceCollector({
                    submitUrl: '{{ route('references.collect.store') }}',
                })"
            >
                <div class="flex flex-col gap-2 md:flex-row md:items-center md:justify-between">
                    <div>
                        <h3 class="text-lg font-semibold text-slate-800">Nouveau scan</h3>
                        <p class="mt-1 text-sm text-slate-500">Le systeme cree la reference automatiquement si elle n'existe pas.</p>
                    </div>
                    <span class="rounded-full bg-slate-100 px-3 py-1 text-xs font-semibold text-slate-600">GPS requis</span>
                </div>

                <div class="mt-5 grid gap-4 lg:grid-cols-3">
                    <div class="lg:col-span-2">
                        <label for="reference_code" class="mb-1 block text-xs font-semibold uppercase tracking-wider text-slate-500">Code reference</label>
                        <input id="reference_code" x-model="referenceCode" type="text" placeholder="Ex: 717717770" class="w-full rounded-xl border-slate-300 text-sm focus:border-sky-500 focus:ring-sky-500">
                    </div>
                    <div class="flex flex-col justify-end gap-2">
                        <button type="button" @click="scanCode()" class="btn-secondary">Scanner QR</button>
                    </div>
                </div>

                <div class="mt-4 grid gap-4 lg:grid-cols-3">
                    <div>
                        <label for="meter_type" class="mb-1 block text-xs font-semibold uppercase tracking-wider text-slate-500">Type compteur</label>
                        <select id="meter_type" x-model="meterType" class="w-full rounded-xl border-slate-300 text-sm focus:border-sky-500 focus:ring-sky-500">
                            <option value="electrique">Electrique</option>
                            <option value="mechanique">Mecanique</option>
                            <option value="autre">Autre</option>
                        </select>
                    </div>
                    <div class="lg:col-span-2">
                        <label for="notes" class="mb-1 block text-xs font-semibold uppercase tracking-wider text-slate-500">Notes terrain (optionnel)</label>
                        <textarea id="notes" x-model="notes" rows="2" placeholder="Etat du compteur, acces, details zone..." class="w-full rounded-xl border-slate-300 text-sm focus:border-sky-500 focus:ring-sky-500"></textarea>
                    </div>
                </div>

                <div class="mt-5 flex flex-wrap items-center gap-3">
                    <button type="button" @click="saveScan()" :disabled="busy" class="btn-primary px-5 py-2.5 text-[11px] disabled:opacity-40">Enregistrer ce compteur</button>
                    <span class="text-xs" :class="messageOk ? 'text-emerald-700' : 'text-rose-700'" x-show="message" x-text="message"></span>
                </div>

                <div x-show="scanning" x-cloak class="fixed inset-0 z-50 flex items-center justify-center bg-slate-950/80 p-4" @click.self="stopScanner()">
                    <div class="w-full max-w-xl rounded-2xl bg-slate-900 p-4 shadow-2xl">
                        <div class="mb-3 flex items-center justify-between text-slate-100">
                            <h4 class="text-sm font-semibold uppercase tracking-wider">Scanner QR</h4>
                            <button type="button" @click="stopScanner()" class="rounded-lg border border-slate-600 px-3 py-1 text-xs font-semibold text-slate-200 hover:bg-slate-800">Fermer</button>
                        </div>
                        <div class="relative overflow-hidden rounded-xl border border-slate-700 bg-black">
                            <video x-ref="scannerVideo" autoplay playsinline muted class="h-[26rem] w-full object-cover"></video>
                            <div class="pointer-events-none absolute inset-0 flex items-center justify-center">
                                <div class="h-52 w-52 rounded-2xl border-2 border-emerald-400/90 shadow-[0_0_0_9999px_rgba(2,6,23,0.45)]"></div>
                            </div>
                        </div>
                        <p class="mt-3 text-xs text-slate-300">Alignez le QR code dans le cadre pour detection automatique.</p>
                    </div>
                </div>
            </section>

            <section class="panel-card overflow-hidden">
                <div class="panel-head">
                    <h3>Derniers scans</h3>
                    <p>{{ $recentScans->count() }} scan(s)</p>
                </div>
                <div class="overflow-x-auto">
                    <table class="min-w-full divide-y divide-slate-200">
                        <thead class="bg-slate-50">
                            <tr>
                                <th class="px-4 py-3 text-left text-xs font-semibold uppercase tracking-wider text-slate-500">Date</th>
                                <th class="px-4 py-3 text-left text-xs font-semibold uppercase tracking-wider text-slate-500">Reference</th>
                                <th class="px-4 py-3 text-left text-xs font-semibold uppercase tracking-wider text-slate-500">Type</th>
                                <th class="px-4 py-3 text-left text-xs font-semibold uppercase tracking-wider text-slate-500">GPS</th>
                                <th class="px-4 py-3 text-left text-xs font-semibold uppercase tracking-wider text-slate-500">Statut</th>
                            </tr>
                        </thead>
                        <tbody class="divide-y divide-slate-100 bg-white">
                            @forelse ($recentScans as $scan)
                                <tr class="hover:bg-slate-50/80">
                                    <td class="px-4 py-3 whitespace-nowrap text-sm text-slate-700">{{ $scan->scanned_at?->format('Y-m-d H:i') }}</td>
                                    <td class="px-4 py-3 whitespace-nowrap text-sm font-semibold text-slate-800">{{ $scan->reference_code }}</td>
                                    <td class="px-4 py-3 whitespace-nowrap text-sm text-slate-700">{{ ucfirst($scan->meter_type) }}</td>
                                    <td class="px-4 py-3 whitespace-nowrap text-sm text-slate-700">{{ number_format((float) $scan->latitude, 6) }}, {{ number_format((float) $scan->longitude, 6) }}</td>
                                    <td class="px-4 py-3 whitespace-nowrap text-sm">
                                        <span @class([
                                            'inline-flex items-center rounded-full px-2.5 py-1 text-xs font-semibold',
                                            'bg-sky-100 text-sky-800' => $scan->was_created,
                                            'bg-slate-100 text-slate-700' => !$scan->was_created,
                                        ])>
                                            {{ $scan->was_created ? 'Nouvelle reference' : 'Reference existante' }}
                                        </span>
                                    </td>
                                </tr>
                            @empty
                                <tr>
                                    <td colspan="5" class="px-4 py-8 text-center text-sm text-slate-500">Aucun scan enregistre pour le moment.</td>
                                </tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>
            </section>
        </div>
    </div>

    @push('scripts')
        <script>
            function referenceCollector(config) {
                return {
                    submitUrl: config.submitUrl,
                    referenceCode: '',
                    meterType: 'electrique',
                    notes: '',
                    busy: false,
                    scanning: false,
                    scannerStream: null,
                    message: '',
                    messageOk: false,

                    async scanCode() {
                        this.message = 'Demarrage du scanner...';
                        this.messageOk = false;

                        try {
                            const video = await this.startScanner();
                            const found = 'BarcodeDetector' in window
                                ? await this.scanWithBarcodeDetector(video)
                                : await this.scanWithJsQrFallback(video);

                            if (found) {
                                this.referenceCode = found;
                                this.message = 'Code scanne avec succes.';
                                this.messageOk = true;
                            } else {
                                this.message = 'Aucun QR detecte, veuillez reessayer.';
                                this.messageOk = false;
                            }
                        } catch (error) {
                            this.message = error.message || 'Camera non accessible.';
                            this.messageOk = false;
                        } finally {
                            this.stopScanner();
                        }
                    },

                    async startScanner() {
                        this.stopScanner();

                        const stream = await navigator.mediaDevices.getUserMedia({ video: { facingMode: 'environment' } });
                        this.scannerStream = stream;
                        this.scanning = true;

                        await this.$nextTick();

                        const video = this.$refs.scannerVideo;
                        if (!video) {
                            throw new Error('Apercu camera indisponible.');
                        }

                        video.srcObject = stream;
                        await video.play();

                        return video;
                    },

                    stopScanner() {
                        if (this.scannerStream) {
                            this.scannerStream.getTracks().forEach((track) => track.stop());
                            this.scannerStream = null;
                        }
                        this.scanning = false;
                    },

                    async scanWithBarcodeDetector(video) {
                        let found = null;

                        const detector = new BarcodeDetector({ formats: ['qr_code'] });
                        const canvas = document.createElement('canvas');
                        canvas.width = video.videoWidth || 720;
                        canvas.height = video.videoHeight || 960;
                        const context = canvas.getContext('2d');

                        for (let i = 0; i < 80 && this.scanning; i += 1) {
                            if (!context) {
                                break;
                            }

                            context.drawImage(video, 0, 0, canvas.width, canvas.height);
                            const codes = await detector.detect(canvas);
                            if (codes.length > 0 && codes[0].rawValue) {
                                found = codes[0].rawValue;
                                break;
                            }

                            await new Promise((resolve) => setTimeout(resolve, 100));
                        }

                        return found;
                    },

                    async ensureJsQr() {
                        if (window.jsQR) {
                            return;
                        }

                        await new Promise((resolve, reject) => {
                            const src = 'https://cdn.jsdelivr.net/npm/jsqr@1.4.0/dist/jsQR.js';

                            if (document.querySelector(`script[src="${src}"]`)) {
                                resolve();
                                return;
                            }

                            const script = document.createElement('script');
                            script.src = src;
                            script.async = true;
                            script.onload = () => resolve();
                            script.onerror = () => reject(new Error('Chargement du decodeur QR impossible.'));
                            document.head.appendChild(script);
                        });
                    },

                    async scanWithJsQrFallback(video) {
                        await this.ensureJsQr();

                        const canvas = document.createElement('canvas');
                        canvas.width = video.videoWidth || 720;
                        canvas.height = video.videoHeight || 960;
                        const context = canvas.getContext('2d', { willReadFrequently: true });

                        let found = null;
                        for (let i = 0; i < 100 && this.scanning; i += 1) {
                            if (!context) {
                                break;
                            }

                            context.drawImage(video, 0, 0, canvas.width, canvas.height);
                            const imageData = context.getImageData(0, 0, canvas.width, canvas.height);
                            const code = window.jsQR(imageData.data, canvas.width, canvas.height, {
                                inversionAttempts: 'dontInvert',
                            });

                            if (code && code.data) {
                                found = code.data;
                                break;
                            }

                            await new Promise((resolve) => setTimeout(resolve, 100));
                        }

                        return found;
                    },

                    async saveScan() {
                        this.busy = true;
                        this.message = 'Collecte GPS en cours...';
                        this.messageOk = false;

                        try {
                            const position = await this.getCurrentPosition();
                            if (!position) {
                                throw new Error('Position GPS indisponible. Activez la localisation et reessayez.');
                            }

                            const response = await fetch(this.submitUrl, {
                                method: 'POST',
                                headers: {
                                    'Content-Type': 'application/json',
                                    'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute('content'),
                                    'Accept': 'application/json',
                                },
                                body: JSON.stringify({
                                    reference_code: this.referenceCode,
                                    meter_type: this.meterType,
                                    latitude: position.latitude,
                                    longitude: position.longitude,
                                    accuracy_m: position.accuracy,
                                    notes: this.notes,
                                }),
                            });

                            const data = await response.json();
                            if (!response.ok) {
                                throw new Error(data.message || 'Impossible d\'enregistrer le scan.');
                            }

                            this.message = data.message || 'Scan enregistre.';
                            this.messageOk = true;

                            setTimeout(() => window.location.reload(), 800);
                        } catch (error) {
                            this.message = error.message || 'Erreur pendant la sauvegarde.';
                            this.messageOk = false;
                        } finally {
                            this.busy = false;
                        }
                    },

                    getCurrentPosition() {
                        return new Promise((resolve) => {
                            if (!('geolocation' in navigator)) {
                                resolve(null);
                                return;
                            }

                            navigator.geolocation.getCurrentPosition(
                                (position) => {
                                    resolve({
                                        latitude: position.coords.latitude,
                                        longitude: position.coords.longitude,
                                        accuracy: position.coords.accuracy ?? null,
                                    });
                                },
                                () => resolve(null),
                                { enableHighAccuracy: true, timeout: 12000, maximumAge: 0 }
                            );
                        });
                    },
                };
            }
        </script>
    @endpush
</x-app-layout>