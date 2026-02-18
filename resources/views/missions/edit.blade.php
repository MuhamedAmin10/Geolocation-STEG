<x-app-layout>
    <x-slot name="header">
        <h2 class="font-semibold text-xl text-gray-800 leading-tight">
            {{ __('Modifier la mission') }} #{{ $mission->id }}
        </h2>
    </x-slot>

    <div class="py-12">
        <div class="max-w-3xl mx-auto sm:px-6 lg:px-8">
            <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg">
                <div class="p-6 text-gray-900">
                    <form method="POST" action="{{ route('missions.update', $mission) }}" class="space-y-6">
                        @csrf
                        @method('PUT')

                        @include('missions._form', [
                            'mission' => $mission,
                            'referencePoints' => $referencePoints,
                            'techniciens' => $techniciens,
                            'currentTechnicienId' => old('technicien_id', $mission->currentAffectation?->technicien_id),
                        ])

                        <div class="flex items-center justify-end gap-3">
                            <a href="{{ route('missions.show', $mission) }}" class="text-gray-600 hover:text-gray-900">Retour</a>
                            <x-primary-button>
                                {{ __('Enregistrer') }}
                            </x-primary-button>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>
</x-app-layout>
