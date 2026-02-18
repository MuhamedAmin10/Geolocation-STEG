<x-app-layout>
    <x-slot name="header">
        <div class="flex items-center justify-between">
            <h2 class="font-semibold text-xl text-gray-800 leading-tight">
                {{ __('Techniciens') }}
            </h2>

            <a href="{{ route('admin.techniciens.create') }}" class="inline-flex items-center px-4 py-2 bg-gray-800 border border-transparent rounded-md font-semibold text-xs text-white uppercase tracking-widest hover:bg-gray-700">
                {{ __('Nouveau technicien') }}
            </a>
        </div>
    </x-slot>

    <div class="py-12">
        <div class="max-w-7xl mx-auto sm:px-6 lg:px-8">
            <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg">
                <div class="p-6 text-gray-900">
                    @if (session('status'))
                        <div class="mb-4 rounded border border-green-200 bg-green-50 px-4 py-2 text-green-800">
                            {{ session('status') }}
                        </div>
                    @endif

                    <div class="overflow-x-auto">
                        <table class="min-w-full divide-y divide-gray-200">
                            <thead class="bg-gray-50">
                                <tr>
                                    <th class="px-4 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Nom</th>
                                    <th class="px-4 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Email</th>
                                    <th class="px-4 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Téléphone</th>
                                    <th class="px-4 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Zone</th>
                                    <th class="px-4 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Actif</th>
                                    <th class="px-4 py-3 text-right text-xs font-medium text-gray-500 uppercase tracking-wider">Actions</th>
                                </tr>
                            </thead>
                            <tbody class="bg-white divide-y divide-gray-200">
                                @forelse ($techniciens as $t)
                                    <tr>
                                        <td class="px-4 py-3 whitespace-nowrap">
                                            {{ $t->prenom }} {{ $t->nom }}
                                            <div class="text-xs text-gray-500">{{ $t->user?->name }}</div>
                                        </td>
                                        <td class="px-4 py-3 whitespace-nowrap">{{ $t->user?->email }}</td>
                                        <td class="px-4 py-3 whitespace-nowrap">{{ $t->telephone }}</td>
                                        <td class="px-4 py-3 whitespace-nowrap">{{ $t->zone_intervention ?? '—' }}</td>
                                        <td class="px-4 py-3 whitespace-nowrap">
                                            {{ $t->user?->active ? 'Oui' : 'Non' }}
                                        </td>
                                        <td class="px-4 py-3 whitespace-nowrap text-right space-x-2">
                                            <a class="text-indigo-600 hover:text-indigo-900" href="{{ route('admin.techniciens.edit', $t) }}">Modifier</a>

                                            <form class="inline" method="POST" action="{{ route('admin.techniciens.destroy', $t) }}" onsubmit="return confirm('Désactiver ce technicien ?')">
                                                @csrf
                                                @method('DELETE')
                                                <button type="submit" class="text-red-600 hover:text-red-800">Désactiver</button>
                                            </form>
                                        </td>
                                    </tr>
                                @empty
                                    <tr>
                                        <td colspan="6" class="px-4 py-6 text-center text-gray-500">Aucun technicien.</td>
                                    </tr>
                                @endforelse
                            </tbody>
                        </table>
                    </div>

                    <div class="mt-6">
                        {{ $techniciens->links() }}
                    </div>
                </div>
            </div>
        </div>
    </div>
</x-app-layout>
