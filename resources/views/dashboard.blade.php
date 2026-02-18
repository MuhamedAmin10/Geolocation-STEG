<x-app-layout>
    <x-slot name="header">
        <h2 class="font-semibold text-xl text-gray-800 leading-tight">
            {{ __('Dashboard') }}
        </h2>
    </x-slot>

    <div class="py-12">
        <div class="max-w-7xl mx-auto sm:px-6 lg:px-8">
            <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg">
                <div class="p-6 text-gray-900">
                    <div class="text-gray-900 font-medium">{{ __("You're logged in!") }}</div>

                    <div class="mt-6 flex flex-wrap items-center gap-3">
                        <a href="{{ route('missions.index') }}" class="inline-flex items-center px-4 py-2 bg-gray-800 text-white rounded-md text-sm hover:bg-gray-700">Missions</a>
                        <a href="{{ route('reference.search') }}" class="inline-flex items-center px-4 py-2 bg-white border rounded-md text-sm hover:bg-gray-50">Recherche Référence</a>

                        @can('access-admin')
                            <a href="{{ route('admin.dashboard') }}" class="inline-flex items-center px-4 py-2 bg-gray-800 text-white rounded-md text-sm hover:bg-gray-700">Admin</a>
                        @endcan
                    </div>
                </div>
            </div>
        </div>
    </div>
</x-app-layout>
