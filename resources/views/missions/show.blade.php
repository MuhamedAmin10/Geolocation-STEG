<x-app-layout>
    <x-slot name="header">
        <div class="flex items-center justify-between">
            <h2 class="font-semibold text-xl text-gray-800 leading-tight">
                Mission #{{ $mission->id }}
            </h2>

            <div class="flex items-center gap-3">
                <a href="{{ route('missions.index') }}" class="text-gray-600 hover:text-gray-900">Toutes les missions</a>

                @can('manage-missions')
                    <a href="{{ route('missions.edit', $mission) }}" class="inline-flex items-center px-4 py-2 bg-gray-800 border border-transparent rounded-md font-semibold text-xs text-white uppercase tracking-widest hover:bg-gray-700">Modifier</a>
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

            @can('work-mission', $mission)
                <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg">
                    <div class="p-6 text-gray-900">
                        <h3 class="font-semibold text-gray-800">Mise à jour technicien</h3>

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

                            <div class="flex items-center justify-end">
                                <x-primary-button>
                                    {{ __('Enregistrer') }}
                                </x-primary-button>
                            </div>
                        </form>
                    </div>
                </div>
            @endcan

            <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg">
                <div class="p-6 text-gray-900">
                    <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                        <div>
                            <div class="text-xs uppercase text-gray-500">Référence</div>
                            <div class="text-lg font-semibold">
                                {{ $mission->referencePoint?->reference ?? '—' }}
                            </div>
                            <div class="text-sm text-gray-600">
                                {{ $mission->referencePoint?->adresse ?? '' }}
                            </div>
                        </div>

                        <div>
                            <div class="text-xs uppercase text-gray-500">Créée par</div>
                            <div class="text-sm">{{ $mission->creator?->name ?? '—' }} ({{ $mission->creator?->email ?? '' }})</div>
                            <div class="text-xs text-gray-500">{{ $mission->created_at?->format('Y-m-d H:i') }}</div>
                        </div>

                        <div>
                            <div class="text-xs uppercase text-gray-500">Type / Priorité</div>
                            <div class="text-sm">{{ $mission->type_mission }} — {{ $mission->priorite }}</div>
                        </div>

                        <div>
                            <div class="text-xs uppercase text-gray-500">Statut / Échéance</div>
                            <div class="text-sm">{{ $mission->statut }}</div>
                            <div class="text-xs text-gray-500">{{ $mission->due_at?->format('Y-m-d H:i') ?? '—' }}</div>
                        </div>

                        <div class="md:col-span-2">
                            <div class="text-xs uppercase text-gray-500">Description</div>
                            <div class="text-sm whitespace-pre-line">{{ $mission->description ?: '—' }}</div>
                        </div>
                    </div>
                </div>
            </div>

            <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg">
                <div class="p-6 text-gray-900">
                    <h3 class="font-semibold text-gray-800">Affectations</h3>

                    <div class="mt-4 overflow-x-auto">
                        <table class="min-w-full divide-y divide-gray-200">
                            <thead class="bg-gray-50">
                                <tr>
                                    <th class="px-4 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Technicien</th>
                                    <th class="px-4 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Assignée le</th>
                                    <th class="px-4 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Assignée par</th>
                                    <th class="px-4 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Rapport</th>
                                </tr>
                            </thead>
                            <tbody class="bg-white divide-y divide-gray-200">
                                @forelse ($mission->affectations->sortByDesc('assigned_at') as $a)
                                    <tr>
                                        <td class="px-4 py-3 whitespace-nowrap">
                                            {{ $a->technicien?->prenom }} {{ $a->technicien?->nom }}
                                        </td>
                                        <td class="px-4 py-3 whitespace-nowrap">{{ $a->assigned_at?->format('Y-m-d H:i') }}</td>
                                        <td class="px-4 py-3 whitespace-nowrap">{{ $a->assignedBy?->name ?? '—' }}</td>
                                        <td class="px-4 py-3">{{ $a->rapport ?: '—' }}</td>
                                    </tr>
                                @empty
                                    <tr>
                                        <td colspan="4" class="px-4 py-6 text-center text-gray-500">Aucune affectation.</td>
                                    </tr>
                                @endforelse
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>
        </div>
    </div>
</x-app-layout>
