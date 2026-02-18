<x-app-layout>
    <x-slot name="header">
        <h2 class="font-semibold text-xl text-gray-800 leading-tight">
            {{ __('Admin') }}
        </h2>
    </x-slot>

    <div class="py-12">
        <div class="max-w-7xl mx-auto sm:px-6 lg:px-8 space-y-6">
            @if (session('status'))
                <div class="rounded border border-green-200 bg-green-50 px-4 py-2 text-green-800">
                    {{ session('status') }}
                </div>
            @endif

            <div class="grid grid-cols-1 md:grid-cols-4 gap-4">
                <div class="bg-white shadow-sm sm:rounded-lg p-6">
                    <div class="text-xs uppercase text-gray-500">Utilisateurs</div>
                    <div class="text-2xl font-semibold">{{ $counts['users'] }}</div>
                </div>
                <div class="bg-white shadow-sm sm:rounded-lg p-6">
                    <div class="text-xs uppercase text-gray-500">Techniciens</div>
                    <div class="text-2xl font-semibold">{{ $counts['techniciens'] }}</div>
                </div>
                <div class="bg-white shadow-sm sm:rounded-lg p-6">
                    <div class="text-xs uppercase text-gray-500">Missions</div>
                    <div class="text-2xl font-semibold">{{ $counts['missions'] }}</div>
                </div>
                <div class="bg-white shadow-sm sm:rounded-lg p-6">
                    <div class="text-xs uppercase text-gray-500">Non affectées</div>
                    <div class="text-2xl font-semibold">{{ $counts['missions_unassigned'] }}</div>
                </div>
            </div>

            <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg">
                <div class="p-6 text-gray-900">
                    <div class="flex flex-wrap items-center gap-3">
                        @can('manage-missions')
                            <a href="{{ route('missions.create') }}" class="inline-flex items-center px-4 py-2 bg-gray-800 text-white rounded-md text-sm hover:bg-gray-700">Créer une mission</a>
                        @endcan
                        <a href="{{ route('missions.index') }}" class="inline-flex items-center px-4 py-2 bg-gray-800 text-white rounded-md text-sm hover:bg-gray-700">Missions</a>
                        <a href="{{ route('admin.techniciens.index') }}" class="inline-flex items-center px-4 py-2 bg-gray-800 text-white rounded-md text-sm hover:bg-gray-700">Gérer les techniciens</a>
                        <a href="{{ route('reference.search') }}" class="inline-flex items-center px-4 py-2 bg-white border rounded-md text-sm hover:bg-gray-50">Recherche Référence</a>
                    </div>
                </div>
            </div>

            <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg">
                <div class="p-6 text-gray-900">
                    <div class="flex items-center justify-between">
                        <h3 class="font-semibold text-gray-800">Affecter une mission (non affectées)</h3>
                        <a class="text-sm text-gray-600 hover:text-gray-900" href="{{ route('missions.index') }}">Voir tout</a>
                    </div>

                    <div class="mt-4 overflow-x-auto">
                        <table class="min-w-full divide-y divide-gray-200">
                            <thead class="bg-gray-50">
                                <tr>
                                    <th class="px-4 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Mission</th>
                                    <th class="px-4 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Référence</th>
                                    <th class="px-4 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Créée par</th>
                                    <th class="px-4 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Affectation</th>
                                </tr>
                            </thead>
                            <tbody class="bg-white divide-y divide-gray-200">
                                @forelse ($unassignedMissions as $m)
                                    <tr>
                                        <td class="px-4 py-3 whitespace-nowrap">
                                            <a class="text-indigo-600 hover:text-indigo-900" href="{{ route('missions.show', $m) }}">#{{ $m->id }}</a>
                                            <div class="text-xs text-gray-500">{{ $m->type_mission }} — {{ $m->priorite }}</div>
                                        </td>
                                        <td class="px-4 py-3 whitespace-nowrap">
                                            {{ $m->referencePoint?->reference ?? '—' }}
                                        </td>
                                        <td class="px-4 py-3 whitespace-nowrap">
                                            {{ $m->creator?->name ?? '—' }}
                                        </td>
                                        <td class="px-4 py-3">
                                            <form method="POST" action="{{ route('admin.missions.assign', $m) }}" class="flex items-center gap-2">
                                                @csrf
                                                <select name="technicien_id" class="rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500" required>
                                                    <option value="" disabled selected>Choisir...</option>
                                                    @foreach ($techniciens as $t)
                                                        <option value="{{ $t->id }}">{{ $t->prenom }} {{ $t->nom }}</option>
                                                    @endforeach
                                                </select>
                                                <button type="submit" class="inline-flex items-center px-3 py-2 bg-gray-800 text-white rounded-md text-sm hover:bg-gray-700">Affecter</button>
                                            </form>
                                        </td>
                                    </tr>
                                @empty
                                    <tr>
                                        <td colspan="4" class="px-4 py-6 text-center text-gray-500">Aucune mission non affectée.</td>
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
