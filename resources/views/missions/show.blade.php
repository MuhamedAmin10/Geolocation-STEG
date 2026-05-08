<x-app-layout>
    <x-slot name="header">
        <div class="flex flex-col gap-4 md:flex-row md:items-center md:justify-between">
            <h2 class="text-2xl font-bold leading-tight text-slate-900">
                Mission #{{ $mission->id }}
            </h2>

            <div class="flex items-center gap-3">
                <a href="{{ route('missions.index') }}" class="text-slate-600 hover:text-slate-900">Toutes les missions</a>

                @can('manage-missions')
                    <a href="{{ route('missions.edit', $mission) }}" class="inline-flex items-center rounded-xl bg-brand-primary px-4 py-2 text-xs font-semibold uppercase tracking-widest text-white hover:bg-brand-primary-dark">Modifier</a>
                @endcan
            </div>
        </div>
    </x-slot>

    <div class="py-12">
        <div class="max-w-5xl mx-auto sm:px-6 lg:px-8 space-y-6">
            @if (session('status'))
                <div class="rounded-xl border border-emerald-200 bg-emerald-50 px-4 py-3 text-emerald-800">
                    {{ session('status') }}
                </div>
            @endif

            @can('work-mission', $mission)
                <div class="brand-card overflow-hidden">
                    <div
                        class="p-6 text-slate-900"
                        x-data="missionTimerWidget({
                            missionId: {{ $mission->id }},
                            actionUrl: '{{ route('missions.time-log', $mission) }}',
                            scanReferenceUrl: '{{ route('missions.reference-scans', $mission) }}',
                            verifyQrUrl: '{{ route('missions.verify-qr', $mission) }}',
                            expectedReference: @js($mission->referencePoint?->reference ?? ''),
                            initialStatus: '{{ $mission->statut }}',
                            initialStartedAt: '{{ optional($mission->started_at)->toIso8601String() }}',
                            initialTotal: {{ (int) ($mission->total_working_time ?? 0) }},
                            initialEstimated: {{ (int) ($mission->estimated_duration ?? 0) }},
                            initialOnSite: {{ (int) ($mission->on_site_time_minutes ?? 0) }},
                            initialTravel: {{ (int) ($mission->travel_time_minutes ?? 0) }}
                        })"
                    >
                        <div class="flex flex-col gap-4 md:flex-row md:items-start md:justify-between">
                            <div>
                                <h3 class="font-semibold text-slate-800">Time tracker mission</h3>
                                <p class="mt-1 text-sm text-slate-500">Suivi en temps reel avec pause automatique apres 15 minutes d'inactivite.</p>
                            </div>
                            <span class="rounded-full bg-slate-100 px-3 py-1 text-xs font-semibold text-slate-700" x-text="statusLabel"></span>
                        </div>

                            <div class="mt-4 rounded-xl border border-emerald-200 bg-emerald-50/70 p-4">
                                <div class="flex flex-col gap-1 md:flex-row md:items-center md:justify-between">
                                    <div>
                                        <h4 class="text-sm font-semibold text-slate-800">Lecture du compteur</h4>
                                        <p class="mt-1 text-xs text-slate-500">Scanner la reference, enregistrer la position GPS exacte et typer le compteur.</p>
                                    </div>
                                    <span class="text-xs font-semibold text-emerald-700">Reference attendue: {{ $mission->referencePoint?->reference ?? '—' }}</span>
                                </div>

                                <div class="mt-3 grid gap-3 lg:grid-cols-3">
                                    <div class="lg:col-span-2">
                                        <label for="scan_reference_code" class="mb-1 block text-xs font-semibold uppercase tracking-wider text-slate-500">Code scanne</label>
                                        <input id="scan_reference_code" type="text" x-model="scanReferenceCode" placeholder="Scannez ou saisissez le code" class="w-full rounded-lg border-slate-300 text-sm focus:border-brand-primary focus:ring-brand-primary">
                                    </div>
                                    <div>
                                        <label for="scan_compteur_type" class="mb-1 block text-xs font-semibold uppercase tracking-wider text-slate-500">Type compteur</label>
                                        <select id="scan_compteur_type" x-model="scanCompteurType" class="w-full rounded-lg border-slate-300 text-sm focus:border-brand-primary focus:ring-brand-primary">
                                            <option value="mechanique">Mecanique</option>
                                            <option value="electrique">Electrique</option>
                                            <option value="autre">Autre</option>
                                        </select>
                                    </div>
                                </div>

                                <div class="mt-3 grid gap-3 lg:grid-cols-3">
                                    <div class="lg:col-span-2">
                                        <label for="scan_notes" class="mb-1 block text-xs font-semibold uppercase tracking-wider text-slate-500">Notes</label>
                                        <textarea id="scan_notes" x-model="scanNotes" rows="2" placeholder="Etat du compteur, acces, anomalie..." class="w-full rounded-lg border-slate-300 text-sm focus:border-brand-primary focus:ring-brand-primary"></textarea>
                                    </div>
                                    <div class="flex flex-col justify-end gap-2">
                                        <button type="button" @click="scanQrCode()" class="rounded-lg border border-slate-300 bg-white px-4 py-2 text-xs font-semibold uppercase tracking-wider text-slate-700 hover:bg-slate-100">Scanner QR</button>
                                        <button type="button" @click="saveReferenceScan()" :disabled="scanBusy" class="rounded-lg bg-emerald-600 px-4 py-2 text-xs font-semibold uppercase tracking-wider text-white hover:bg-emerald-700 disabled:opacity-40">Enregistrer la lecture</button>
                                    </div>
                                </div>

                                <p class="mt-2 text-xs text-slate-500">La sauvegarde enregistre le code, le type, la position GPS et l'heure de lecture.</p>
                                <p class="mt-2 text-xs font-medium" :class="scanValid ? 'text-emerald-700' : 'text-rose-700'" x-show="scanMessage" x-text="scanMessage"></p>
                            </div>

                        <div class="mt-4 grid grid-cols-1 gap-3 md:grid-cols-4">
                            <article class="rounded-xl border border-slate-200 bg-slate-50 px-4 py-3">
                                <p class="text-xs uppercase tracking-wider text-slate-500">Elapsed</p>
                                <p class="mt-1 text-2xl font-bold text-slate-900" x-text="formattedElapsed"></p>
                            </article>
                            <article class="rounded-xl border border-slate-200 bg-slate-50 px-4 py-3">
                                <p class="text-xs uppercase tracking-wider text-slate-500">Estimated</p>
                                <p class="mt-1 text-2xl font-bold text-slate-900" x-text="estimatedText"></p>
                            </article>
                            <article class="rounded-xl border border-slate-200 bg-slate-50 px-4 py-3">
                                <p class="text-xs uppercase tracking-wider text-slate-500">On-site</p>
                                <p class="mt-1 text-2xl font-bold text-slate-900" x-text="onSiteText"></p>
                            </article>
                            <article class="rounded-xl border border-slate-200 bg-slate-50 px-4 py-3">
                                <p class="text-xs uppercase tracking-wider text-slate-500">Travel</p>
                                <p class="mt-1 text-2xl font-bold text-slate-900" x-text="travelText"></p>
                            </article>
                        </div>

                        <div class="mt-4 flex flex-wrap items-center gap-2">
                            <button type="button" class="rounded-xl bg-emerald-600 px-4 py-2 text-xs font-semibold uppercase tracking-wider text-white hover:bg-emerald-700 disabled:opacity-40" @click="checkIn()" :disabled="busy || isRunning || statusLabel === 'Terminée'">Check In</button>
                            <button type="button" class="rounded-xl bg-amber-500 px-4 py-2 text-xs font-semibold uppercase tracking-wider text-white hover:bg-amber-600 disabled:opacity-40" @click="logAction('pause')" :disabled="busy || !isRunning || statusLabel === 'Terminée'">Pause</button>
                            <button type="button" class="rounded-xl bg-sky-600 px-4 py-2 text-xs font-semibold uppercase tracking-wider text-white hover:bg-sky-700 disabled:opacity-40" @click="logAction('resume')" :disabled="busy || isRunning || statusLabel === 'Terminée'">Resume</button>
                            <button type="button" class="rounded-xl bg-violet-600 px-4 py-2 text-xs font-semibold uppercase tracking-wider text-white hover:bg-violet-700 disabled:opacity-40" @click="toggleBreak()" :disabled="busy || statusLabel === 'Terminée'" x-text="onBreak ? 'Break End' : 'Break Start'"></button>
                            <button type="button" class="rounded-xl bg-rose-600 px-4 py-2 text-xs font-semibold uppercase tracking-wider text-white hover:bg-rose-700 disabled:opacity-40" @click="checkOut()" :disabled="busy || statusLabel === 'Terminée'">Check Out + Complete</button>
                        </div>

                        <p class="mt-3 text-xs text-slate-500" x-text="feedback"></p>
                        <p class="mt-2 rounded-lg border border-rose-200 bg-rose-50 px-3 py-2 text-xs text-rose-700" x-show="geofenceWarning" x-text="geofenceWarning"></p>

                        <form class="mt-4 space-y-4 border-t border-slate-200 pt-4" method="POST" action="{{ route('missions.work.update', $mission) }}" enctype="multipart/form-data">
                            @csrf
                            @method('PATCH')

                            <div>
                                <x-input-label for="statut" :value="__('Statut manuel')" />
                                <select id="statut" name="statut" class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500" required>
                                    @foreach (['En cours', 'Bloquée', 'Terminée'] as $s)
                                        <option value="{{ $s }}" {{ old('statut', $mission->statut) === $s ? 'selected' : '' }}>
                                            {{ $s }}
                                        </option>
                                    @endforeach
                                </select>
                                <x-input-error :messages="$errors->get('statut')" class="mt-2" />
                            </div>

                            <div>
                                <x-input-label for="rapport" :value="__('Rapport (optionnel)')" />
                                <textarea id="rapport" name="rapport" rows="3" class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500">{{ old('rapport') }}</textarea>
                                <x-input-error :messages="$errors->get('rapport')" class="mt-2" />
                            </div>

                            <div>
                                <x-input-label for="photos" :value="__('Mission photos (before/after)')" />
                                <input id="photos" name="photos[]" type="file" multiple accept="image/png,image/jpeg" class="mt-1 block w-full rounded-md border-gray-300 text-sm shadow-sm focus:border-indigo-500 focus:ring-indigo-500">
                                <x-input-error :messages="$errors->get('photos')" class="mt-2" />
                                <x-input-error :messages="$errors->get('photos.*')" class="mt-2" />
                                <p class="mt-2 text-xs text-slate-500">Max 10 photos, 10MB each (jpg/png).</p>
                            </div>

                            <div class="flex items-center justify-end">
                                <x-primary-button class="!rounded-xl !bg-brand-primary px-5 py-2.5 !normal-case hover:!bg-brand-primary-dark">
                                    {{ __('Enregistrer') }}
                                </x-primary-button>
                            </div>
                        </form>
                    </div>
                </div>
            @endcan

            @can('work-mission', $mission)
                <div class="brand-card overflow-hidden">
                    <div class="p-6 text-slate-900">
                        <div class="flex flex-col gap-2 md:flex-row md:items-center md:justify-between">
                            <div>
                                <h3 class="font-semibold text-slate-800">Derniers scans enregistres</h3>
                                <p class="mt-1 text-sm text-slate-500">Historique des lectures du compteur avec position et type.</p>
                            </div>
                            <span class="inline-flex items-center rounded-full bg-slate-100 px-3 py-1 text-xs font-semibold uppercase tracking-wider text-slate-600">
                                {{ $mission->referenceScans->count() }} lecture(s)
                            </span>
                        </div>

                        <div class="mt-4 overflow-x-auto rounded-xl border border-slate-200">
                            <table class="min-w-full divide-y divide-slate-200">
                                <thead class="bg-slate-50">
                                    <tr>
                                        <th class="px-4 py-3 text-left text-xs font-semibold uppercase tracking-wider text-slate-500">Date</th>
                                        <th class="px-4 py-3 text-left text-xs font-semibold uppercase tracking-wider text-slate-500">Code</th>
                                        <th class="px-4 py-3 text-left text-xs font-semibold uppercase tracking-wider text-slate-500">Type</th>
                                        <th class="px-4 py-3 text-left text-xs font-semibold uppercase tracking-wider text-slate-500">Technicien</th>
                                        <th class="px-4 py-3 text-left text-xs font-semibold uppercase tracking-wider text-slate-500">GPS</th>
                                        <th class="px-4 py-3 text-left text-xs font-semibold uppercase tracking-wider text-slate-500">Distance</th>
                                        <th class="px-4 py-3 text-left text-xs font-semibold uppercase tracking-wider text-slate-500">Etat</th>
                                    </tr>
                                </thead>
                                <tbody class="divide-y divide-slate-100 bg-white">
                                    @forelse ($mission->referenceScans->take(5) as $scan)
                                        <tr class="hover:bg-slate-50/80">
                                            <td class="px-4 py-3 whitespace-nowrap text-sm text-slate-700">{{ $scan->scanned_at?->format('Y-m-d H:i') }}</td>
                                            <td class="px-4 py-3 whitespace-nowrap text-sm text-slate-700">{{ $scan->reference_code }}</td>
                                            <td class="px-4 py-3 whitespace-nowrap text-sm text-slate-700">{{ ucfirst($scan->compteur_type) }}</td>
                                            <td class="px-4 py-3 whitespace-nowrap text-sm text-slate-700">{{ $scan->technicien?->prenom }} {{ $scan->technicien?->nom }}</td>
                                            <td class="px-4 py-3 whitespace-nowrap text-sm text-slate-700">{{ number_format((float) $scan->latitude, 6) }}, {{ number_format((float) $scan->longitude, 6) }}</td>
                                            <td class="px-4 py-3 whitespace-nowrap text-sm text-slate-700">{{ $scan->distance_m !== null ? number_format((float) $scan->distance_m, 1) . ' m' : '—' }}</td>
                                            <td class="px-4 py-3 whitespace-nowrap text-sm">
                                                <span @class([
                                                    'inline-flex items-center rounded-full px-2.5 py-1 text-xs font-semibold',
                                                    'bg-emerald-100 text-emerald-800' => $scan->is_match,
                                                    'bg-rose-100 text-rose-800' => !$scan->is_match,
                                                ])>
                                                    {{ $scan->is_match ? 'Valide' : 'Non conforme' }}
                                                </span>
                                            </td>
                                        </tr>
                                    @empty
                                        <tr>
                                            <td colspan="7" class="px-4 py-8 text-center text-sm text-slate-500">Aucun scan enregistre pour cette mission.</td>
                                        </tr>
                                    @endforelse
                                </tbody>
                            </table>
                        </div>
                    </div>
                </div>
            @endcan

            <div class="brand-card overflow-hidden">
                <div class="p-6 text-slate-900">
                    <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                        <div>
                            <div class="text-xs uppercase text-slate-500">Reference</div>
                            <div class="text-lg font-semibold">
                                {{ $mission->referencePoint?->reference ?? '—' }}
                            </div>
                            <div class="text-sm text-slate-600">
                                {{ $mission->referencePoint?->adresse ?? '' }}
                            </div>
                        </div>

                        <div>
                            <div class="text-xs uppercase text-slate-500">Creee par</div>
                            <div class="text-sm">{{ $mission->creator?->name ?? '—' }} ({{ $mission->creator?->email ?? '' }})</div>
                            <div class="text-xs text-slate-500">{{ $mission->created_at?->format('Y-m-d H:i') }}</div>
                        </div>

                        <div>
                            <div class="text-xs uppercase text-slate-500">Type / Priorite</div>
                            <div class="text-sm">{{ $mission->type_mission }} — {{ $mission->priorite }}</div>
                        </div>

                        <div>
                            <div class="text-xs uppercase text-slate-500">Statut / Echeance</div>
                            <div>
                                <span @class([
                                    'rounded-full px-2.5 py-1 text-xs font-semibold',
                                    'bg-emerald-100 text-emerald-800' => $mission->statut === 'Terminée',
                                    'bg-amber-100 text-amber-800' => $mission->statut === 'Bloquée',
                                    'bg-sky-100 text-sky-800' => $mission->statut === 'En cours',
                                    'bg-slate-100 text-slate-700' => !in_array($mission->statut, ['Terminée', 'Bloquée', 'En cours']),
                                ])>
                                    {{ $mission->statut }}
                                </span>
                            </div>
                            <div class="mt-2 text-xs text-slate-500">{{ $mission->due_at?->format('Y-m-d H:i') ?? '—' }}</div>
                        </div>

                        <div class="md:col-span-2">
                            <div class="text-xs uppercase text-slate-500">Description</div>
                            <div class="text-sm whitespace-pre-line">{{ $mission->description ?: '—' }}</div>
                        </div>
                    </div>
                </div>
            </div>

            <div class="brand-card overflow-hidden">
                <div class="p-6 text-slate-900">
                    <h3 class="font-semibold text-slate-800">Affectations</h3>

                    <div class="mt-4 overflow-x-auto rounded-xl border border-slate-200">
                        <table class="min-w-full divide-y divide-slate-200">
                            <thead class="bg-slate-50">
                                <tr>
                                    <th class="px-4 py-3 text-left text-xs font-semibold uppercase tracking-wider text-slate-500">Technicien</th>
                                    <th class="px-4 py-3 text-left text-xs font-semibold uppercase tracking-wider text-slate-500">Assignee le</th>
                                    <th class="px-4 py-3 text-left text-xs font-semibold uppercase tracking-wider text-slate-500">Assignee par</th>
                                    <th class="px-4 py-3 text-left text-xs font-semibold uppercase tracking-wider text-slate-500">Rapport</th>
                                </tr>
                            </thead>
                            <tbody class="divide-y divide-slate-100 bg-white">
                                @forelse ($mission->affectations->sortByDesc('assigned_at') as $a)
                                    <tr class="hover:bg-slate-50/80">
                                        <td class="px-4 py-3 whitespace-nowrap">
                                            {{ $a->technicien?->prenom }} {{ $a->technicien?->nom }}
                                        </td>
                                        <td class="px-4 py-3 whitespace-nowrap">{{ $a->assigned_at?->format('Y-m-d H:i') }}</td>
                                        <td class="px-4 py-3 whitespace-nowrap">{{ $a->assignedBy?->name ?? '—' }}</td>
                                        <td class="px-4 py-3">{{ $a->rapport ?: '—' }}</td>
                                    </tr>
                                @empty
                                    <tr>
                                        <td colspan="4" class="px-4 py-8 text-center text-slate-500">Aucune affectation.</td>
                                    </tr>
                                @endforelse
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>

            <div class="brand-card overflow-hidden">
                <div class="p-6 text-slate-900">
                    <h3 class="font-semibold text-slate-800">Mission photos</h3>
                    <div class="mt-4 grid grid-cols-2 gap-4 sm:grid-cols-3 lg:grid-cols-4">
                        @forelse ($mission->attachments as $attachment)
                            <a href="{{ asset('storage/' . $attachment->file_path) }}" target="_blank" class="group block overflow-hidden rounded-xl border border-slate-200 bg-slate-50">
                                <img src="{{ asset('storage/' . $attachment->file_path) }}" alt="Mission photo" class="h-32 w-full object-cover transition group-hover:scale-105">
                                <div class="px-2 py-1 text-[11px] text-slate-600">
                                    {{ $attachment->uploader?->name ?? 'User' }}
                                </div>
                            </a>
                        @empty
                            <p class="col-span-full text-sm text-slate-500">No mission photos uploaded yet.</p>
                        @endforelse
                    </div>
                </div>
            </div>

            <div class="brand-card overflow-hidden">
                <div class="p-6 text-slate-900">
                    <div class="flex flex-col gap-2 md:flex-row md:items-center md:justify-between">
                        <div>
                            <h3 class="font-semibold text-slate-800">Historique des actions</h3>
                            <p class="mt-1 text-sm text-slate-500">Dernieres modifications tracees sur cette mission.</p>
                        </div>
                        <span class="inline-flex items-center rounded-full bg-slate-100 px-3 py-1 text-xs font-semibold uppercase tracking-wider text-slate-600">
                            {{ $auditLogs->count() }} entree(s)
                        </span>
                    </div>

                    <div class="mt-4 space-y-3">
                        @forelse ($auditLogs as $log)
                            @php
                                $actionStyles = [
                                    'create' => 'bg-emerald-100 text-emerald-800',
                                    'update' => 'bg-sky-100 text-sky-800',
                                    'assign' => 'bg-violet-100 text-violet-800',
                                    'change-status' => 'bg-amber-100 text-amber-800',
                                    'delete' => 'bg-rose-100 text-rose-800',
                                ];

                                $actionLabels = [
                                    'create' => 'Creation',
                                    'update' => 'Mise a jour',
                                    'assign' => 'Affectation',
                                    'change-status' => 'Statut',
                                    'delete' => 'Suppression',
                                ];

                                $oldValues = $log->old_values ?? [];
                                $newValues = $log->new_values ?? [];
                            @endphp

                            <article class="rounded-2xl border border-slate-200 bg-slate-50/70 p-4">
                                <div class="flex flex-col gap-3 md:flex-row md:items-start md:justify-between">
                                    <div class="space-y-2">
                                        <div class="flex flex-wrap items-center gap-2">
                                            <span class="inline-flex items-center rounded-full px-3 py-1 text-xs font-semibold uppercase tracking-wider {{ $actionStyles[$log->action] ?? 'bg-slate-200 text-slate-700' }}">
                                                {{ $actionLabels[$log->action] ?? $log->action }}
                                            </span>
                                            <span class="text-sm font-medium text-slate-800">{{ $log->description ?? 'Aucune description' }}</span>
                                        </div>

                                        <div class="flex flex-wrap gap-3 text-xs text-slate-500">
                                            <span>Par: {{ $log->user?->name ?? 'Système' }}</span>
                                            <span>Le: {{ $log->created_at?->format('Y-m-d H:i') }}</span>
                                        </div>
                                    </div>

                                    @if (!empty($oldValues) || !empty($newValues))
                                        <div class="grid gap-3 text-xs md:grid-cols-2 md:min-w-[24rem]">
                                            @if (!empty($oldValues))
                                                <div class="rounded-xl border border-rose-200 bg-white p-3">
                                                    <div class="font-semibold text-rose-700">Avant</div>
                                                    <pre class="mt-2 overflow-x-auto whitespace-pre-wrap break-words text-[11px] leading-relaxed text-slate-600">{{ json_encode($oldValues, JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE) }}</pre>
                                                </div>
                                            @endif
                                            @if (!empty($newValues))
                                                <div class="rounded-xl border border-emerald-200 bg-white p-3">
                                                    <div class="font-semibold text-emerald-700">Apres</div>
                                                    <pre class="mt-2 overflow-x-auto whitespace-pre-wrap break-words text-[11px] leading-relaxed text-slate-600">{{ json_encode($newValues, JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE) }}</pre>
                                                </div>
                                            @endif
                                        </div>
                                    @endif
                                </div>
                            </article>
                        @empty
                            <div class="rounded-2xl border border-dashed border-slate-300 bg-slate-50 px-4 py-8 text-center text-sm text-slate-500">
                                Aucun historique pour cette mission.
                            </div>
                        @endforelse
                    </div>
                </div>
            </div>
        </div>
    </div>

    @push('scripts')
        <script>
            function missionTimerWidget(config) {
                return {
                    missionId: config.missionId,
                    actionUrl: config.actionUrl,
                    verifyQrUrl: config.verifyQrUrl,
                    statusLabel: config.initialStatus || 'Créée',
                    startedAt: config.initialStartedAt || null,
                    totalMinutes: config.initialTotal || 0,
                    estimatedMinutes: config.initialEstimated || 0,
                    onSiteMinutes: config.initialOnSite || 0,
                    travelMinutes: config.initialTravel || 0,
                    scanReferenceUrl: config.scanReferenceUrl,
                    expectedReference: config.expectedReference || '',
                    scanReferenceCode: '',
                    scanCompteurType: 'electrique',
                    scanNotes: '',
                    scanBusy: false,
                    scanValid: false,
                    scanMessage: '',
                    busy: false,
                    isRunning: (config.initialStatus || '') === 'En cours',
                    onBreak: false,
                    feedback: '',
                    geofenceWarning: '',
                    elapsedSeconds: 0,
                    lastActivityAt: Date.now(),

                    get formattedElapsed() {
                        const total = Math.floor(this.elapsedSeconds / 60);
                        const h = String(Math.floor(total / 60)).padStart(2, '0');
                        const m = String(total % 60).padStart(2, '0');
                        const s = String(this.elapsedSeconds % 60).padStart(2, '0');
                        return `${h}:${m}:${s}`;
                    },

                    get estimatedText() {
                        return this.estimatedMinutes > 0 ? `${this.estimatedMinutes} min` : 'N/A';
                    },

                    get onSiteText() {
                        return `${this.onSiteMinutes} min`;
                    },

                    get travelText() {
                        return `${this.travelMinutes} min`;
                    },

                    init() {
                        this.elapsedSeconds = (this.totalMinutes || 0) * 60;

                        setInterval(() => {
                            if (this.isRunning && !this.onBreak && this.statusLabel !== 'Terminée') {
                                this.elapsedSeconds += 1;
                            }

                            const inactiveMs = Date.now() - this.lastActivityAt;
                            if (this.isRunning && inactiveMs >= 15 * 60 * 1000 && !this.busy) {
                                this.logAction('pause', 'Auto-paused after 15 min inactivity.');
                            }
                        }, 1000);

                        ['click', 'mousemove', 'keydown', 'touchstart'].forEach((eventName) => {
                            window.addEventListener(eventName, () => {
                                this.lastActivityAt = Date.now();
                            }, { passive: true });
                        });
                    },

                    async toggleBreak() {
                        if (this.onBreak) {
                            await this.logAction('break_end');
                            this.onBreak = false;
                            this.isRunning = true;
                            return;
                        }

                        await this.logAction('break_start');
                        this.onBreak = true;
                        this.isRunning = false;
                    },

                    async checkIn() {
                        await this.logAction('start_work', 'check_in');
                    },

                    async checkOut() {
                        await this.logAction('complete', 'check_out');
                    },

                    async saveReferenceScan() {
                        this.scanBusy = true;
                        this.scanValid = false;
                        this.scanMessage = 'Collecte de la position en cours...';

                        try {
                            const position = await this.getCurrentPosition({
                                enableHighAccuracy: true,
                                timeout: 10000,
                                maximumAge: 0,
                            });

                            if (!position) {
                                throw new Error('Position GPS indisponible. Autorisez la geolocalisation puis recommencez.');
                            }

                            const response = await fetch(this.scanReferenceUrl, {
                                method: 'POST',
                                headers: {
                                    'Content-Type': 'application/json',
                                    'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute('content'),
                                    'Accept': 'application/json',
                                },
                                body: JSON.stringify({
                                    qr_code: this.scanReferenceCode,
                                    compteur_type: this.scanCompteurType,
                                    latitude: position.latitude,
                                    longitude: position.longitude,
                                    accuracy_m: position.accuracy,
                                    notes: this.scanNotes,
                                }),
                            });

                            if (!response.ok) {
                                let message = 'Impossible d\'enregistrer la lecture.';
                                try {
                                    const failure = await response.json();
                                    if (failure.message) {
                                        message = failure.message;
                                    }
                                } catch (error) {
                                    // Keep default message.
                                }

                                throw new Error(message);
                            }

                            const data = await response.json();
                            this.scanValid = !!data.valid;
                            this.scanMessage = data.message || (this.scanValid ? 'Lecture enregistrée.' : 'Lecture enregistrée avec ecart de reference.');

                            setTimeout(() => {
                                window.location.reload();
                            }, 800);
                        } catch (error) {
                            this.scanMessage = error.message || 'Impossible d\'enregistrer la lecture.';
                            this.scanValid = false;
                        } finally {
                            this.scanBusy = false;
                        }
                    },

                    async scanQrCode() {
                        if (!('BarcodeDetector' in window)) {
                            this.scanMessage = 'Scanner non disponible sur ce navigateur. Saisissez le code manuellement.';
                            return;
                        }

                        try {
                            const stream = await navigator.mediaDevices.getUserMedia({ video: { facingMode: 'environment' } });
                            const video = document.createElement('video');
                            video.srcObject = stream;
                            await video.play();

                            const detector = new BarcodeDetector({ formats: ['qr_code'] });
                            const canvas = document.createElement('canvas');
                            canvas.width = video.videoWidth || 640;
                            canvas.height = video.videoHeight || 480;
                            const context = canvas.getContext('2d');

                            let found = null;
                            for (let i = 0; i < 30; i += 1) {
                                context.drawImage(video, 0, 0, canvas.width, canvas.height);
                                const codes = await detector.detect(canvas);
                                if (codes.length > 0 && codes[0].rawValue) {
                                    found = codes[0].rawValue;
                                    break;
                                }
                                await new Promise((resolve) => setTimeout(resolve, 100));
                            }

                            stream.getTracks().forEach((track) => track.stop());

                            if (found) {
                                this.scanReferenceCode = found;
                                this.scanMessage = 'Code QR capture.';
                            } else {
                                this.scanMessage = 'Aucun QR detecte. Reessayez.';
                            }
                        } catch (error) {
                            this.scanMessage = 'Acces camera refuse ou indisponible.';
                        }
                    },

                    async logAction(action, note = null) {
                        this.busy = true;
                        this.feedback = 'Synchronisation en cours...';
                        this.geofenceWarning = '';

                        const payload = { action };
                        if (note) {
                            payload.notes = note;
                        }

                        try {
                            const position = await this.getCurrentPosition();
                            if (position) {
                                payload.latitude = position.latitude;
                                payload.longitude = position.longitude;
                            }

                            const response = await fetch(this.actionUrl, {
                                method: 'POST',
                                headers: {
                                    'Content-Type': 'application/json',
                                    'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute('content'),
                                    'Accept': 'application/json',
                                },
                                body: JSON.stringify(payload),
                            });

                            if (!response.ok) {
                                let message = 'Timer action failed';
                                try {
                                    const failure = await response.json();
                                    if (failure.message) {
                                        message = failure.message;
                                    }
                                    if (failure.distance_meters) {
                                        message += ` (${failure.distance_meters}m away)`;
                                        this.geofenceWarning = message;
                                    }
                                } catch (e) {
                                    // Ignore JSON parse errors and keep default message.
                                }
                                throw new Error(message);
                            }

                            const data = await response.json();
                            const mission = data.mission || {};

                            this.statusLabel = mission.statut || this.statusLabel;
                            this.totalMinutes = mission.total_working_time || this.totalMinutes;
                            this.onSiteMinutes = mission.on_site_time_minutes || this.onSiteMinutes;
                            this.travelMinutes = mission.travel_time_minutes || this.travelMinutes;
                            this.elapsedSeconds = this.totalMinutes * 60;

                            if (action === 'start_work' || action === 'resume') {
                                this.isRunning = true;
                                this.onBreak = false;
                            }
                            if (action === 'pause') {
                                this.isRunning = false;
                            }
                            if (action === 'complete') {
                                this.isRunning = false;
                                this.onBreak = false;
                                this.statusLabel = 'Terminée';
                            }

                            this.feedback = 'Timer mis a jour.';
                        } catch (error) {
                            this.feedback = error.message || 'Impossible de mettre a jour le timer.';
                        } finally {
                            this.busy = false;
                        }
                    },

                    getCurrentPosition(options = {}) {
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
                                {
                                    enableHighAccuracy: options.enableHighAccuracy ?? false,
                                    timeout: options.timeout ?? 6000,
                                    maximumAge: options.maximumAge ?? 60000,
                                }
                            );
                        });
                    },
                };
            }
        </script>
    @endpush
</x-app-layout>
