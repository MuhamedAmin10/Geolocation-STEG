<x-app-layout>
    <x-slot name="header">
        <div>
            <p class="text-xs font-semibold uppercase tracking-[0.22em] text-slate-500">Mise a jour</p>
            <h2 class="mt-1 text-2xl font-bold leading-tight text-slate-900">
                {{ __('Modifier la mission') }} #{{ $mission->id }}
            </h2>
        </div>
    </x-slot>

    <div class="py-12">
        <div class="mx-auto max-w-4xl sm:px-6 lg:px-8">
            <div class="brand-card overflow-hidden">
                <div class="border-b border-slate-200 bg-slate-50 px-6 py-4">
                    <p class="text-sm text-slate-600">Ajustez les details de la mission et son affectation.</p>
                </div>

                <div class="p-6 text-slate-900">
                    <form method="POST" action="{{ route('missions.update', $mission) }}" class="space-y-6">
                        @csrf
                        @method('PUT')

                        @include('missions._form', [
                            'mission' => $mission,
                            'referencePoints' => $referencePoints,
                            'techniciens' => $techniciens,
                            'currentTechnicienId' => old('technicien_id', $mission->currentAffectation?->technicien_id),
                        ])

                        <div class="flex items-center justify-end gap-3 border-t border-slate-200 pt-5">
                            <a href="{{ route('missions.show', $mission) }}" class="text-slate-600 hover:text-slate-900">Retour</a>
                            <x-primary-button class="!rounded-xl !bg-brand-primary px-5 py-2.5 !normal-case hover:!bg-brand-primary-dark">
                                {{ __('Enregistrer') }}
                            </x-primary-button>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>
</x-app-layout>
