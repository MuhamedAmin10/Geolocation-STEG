<x-app-layout>
    <x-slot name="header">
        <div class="flex items-center justify-between">
            <h2 class="font-semibold text-xl text-gray-800 leading-tight">
                {{ __('Missions') }}
            </h2>

            @can('manage-missions')
                <a href="{{ route('missions.create') }}" class="inline-flex items-center px-4 py-2 bg-gray-800 border border-transparent rounded-md font-semibold text-xs text-white uppercase tracking-widest hover:bg-gray-700 focus:outline-none focus:ring-2 focus:ring-gray-300 focus:ring-offset-2 transition">
                    {{ __('Créer une mission') }}
                </a>
            @endcan
        </div>
    </x-slot>

    <div class="py-12">
        <div class="max-w-7xl mx-auto sm:px-6 lg:px-8">
            <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg">
                <div class="p-6 text-gray-900">
                    @if (!empty($referenceId))
                        <div class="mb-4 rounded border border-blue-200 bg-blue-50 px-4 py-2 text-blue-800 flex items-center justify-between">
                            <div>
                                Historique des missions pour la référence sélectionnée.
                            </div>
                            <a class="text-sm underline" href="{{ route('missions.index') }}">Afficher toutes</a>
                        </div>
                    @endif

                    @if (session('status'))
                        <div class="mb-4 rounded border border-green-200 bg-green-50 px-4 py-2 text-green-800">
                            {{ session('status') }}
                        </div>
                    @endif

                    <div class="overflow-x-auto">
                        <table class="min-w-full divide-y divide-gray-200">
                            <thead class="bg-gray-50">
                                <tr>
                                    <th class="px-4 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Référence</th>
                                    <th class="px-4 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Type</th>
                                    <th class="px-4 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Priorité</th>
                                    <th class="px-4 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Statut</th>
                                    <th class="px-4 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Technicien</th>
                                    <th class="px-4 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Échéance</th>
                                    <th class="px-4 py-3 text-right text-xs font-medium text-gray-500 uppercase tracking-wider">Actions</th>
                                </tr>
                            </thead>
                            <tbody class="bg-white divide-y divide-gray-200">
                                @forelse ($missions as $mission)
                                    <tr>
                                        <td class="px-4 py-3 whitespace-nowrap">
                                            <a class="text-indigo-600 hover:text-indigo-900" href="{{ route('missions.show', $mission) }}">
                                                {{ $mission->referencePoint?->reference ?? '—' }}
                                            </a>
                                            <div class="text-xs text-gray-500">#{{ $mission->id }}</div>
                                        </td>
                                        <td class="px-4 py-3 whitespace-nowrap">{{ $mission->type_mission }}</td>
                                        <td class="px-4 py-3 whitespace-nowrap">{{ $mission->priorite }}</td>
                                        <td class="px-4 py-3 whitespace-nowrap">{{ $mission->statut }}</td>
                                        <td class="px-4 py-3 whitespace-nowrap">
                                            @php
                                                $aff = $mission->currentAffectation;
                                                $tech = $aff?->technicien;
                                            @endphp
                                            {{ $tech ? ($tech->prenom.' '.$tech->nom) : '—' }}
                                        </td>
                                        <td class="px-4 py-3 whitespace-nowrap">
                                            {{ $mission->due_at?->format('Y-m-d H:i') ?? '—' }}
                                        </td>
                                        <td class="px-4 py-3 whitespace-nowrap text-right space-x-2">
                                            <a class="text-indigo-600 hover:text-indigo-900" href="{{ route('missions.show', $mission) }}">Voir</a>

                                            @can('manage-missions')
                                                <a class="text-gray-600 hover:text-gray-900" href="{{ route('missions.edit', $mission) }}">Modifier</a>

                                                <form class="inline" method="POST" action="{{ route('missions.destroy', $mission) }}" onsubmit="return confirm('Supprimer cette mission ?')">
                                                    @csrf
                                                    @method('DELETE')
                                                    <button type="submit" class="text-red-600 hover:text-red-800">Supprimer</button>
                                                </form>
                                            @endcan
                                        </td>
                                    </tr>
                                @empty
                                    <tr>
                                        <td colspan="7" class="px-4 py-6 text-center text-gray-500">
                                            Aucune mission.
                                        </td>
                                    </tr>
                                @endforelse
                            </tbody>
                        </table>
                    </div>

                    <div class="mt-6">
                        {{ $missions->links() }}
                    </div>
                </div>
            </div>
        </div>
    </div>
</x-app-layout>
