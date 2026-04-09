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
                    <div class="p-6 text-slate-900">
                        <h3 class="font-semibold text-slate-800">Mise a jour technicien</h3>

                        <form class="mt-4 space-y-4" method="POST" action="{{ route('missions.work.update', $mission) }}">
                            @csrf
                            @method('PATCH')

                            <div>
                                <x-input-label for="statut" :value="__('Statut')" />
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
                                <textarea id="rapport" name="rapport" rows="4" class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500">{{ old('rapport') }}</textarea>
                                <x-input-error :messages="$errors->get('rapport')" class="mt-2" />
                                <p class="mt-2 text-xs text-gray-500">Le rapport est enregistré sur votre dernière affectation.</p>
                            </div>

                            <div class="flex items-center justify-end border-t border-slate-200 pt-4">
                                <x-primary-button class="!rounded-xl !bg-brand-primary px-5 py-2.5 !normal-case hover:!bg-brand-primary-dark">
                                    {{ __('Enregistrer') }}
                                </x-primary-button>
                            </div>
                        </form>
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
</x-app-layout>
