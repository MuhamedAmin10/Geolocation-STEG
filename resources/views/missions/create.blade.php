<x-app-layout>
    <x-slot name="header">
        <div>
            <p class="text-xs font-semibold uppercase tracking-[0.22em] text-slate-500">Nouvelle intervention</p>
            <h2 class="mt-1 text-2xl font-bold leading-tight text-slate-900">
                {{ __('Créer une mission') }}
            </h2>
        </div>
    </x-slot>

    <div class="py-12">
        <div class="mx-auto max-w-4xl sm:px-6 lg:px-8">
            <div class="brand-card overflow-hidden">
                <div class="border-b border-slate-200 bg-slate-50 px-6 py-4">
                    <p class="text-sm text-slate-600">Renseignez les informations de planification et affectation.</p>
                </div>

                <div class="p-6 text-slate-900">
                    <form method="POST" action="{{ route('missions.store') }}" class="space-y-6">
                        @csrf

                        @include('missions._form', [
                            'mission' => $mission,
                            'referencePoints' => $referencePoints,
                            'techniciens' => $techniciens,
                            'currentTechnicienId' => old('technicien_id'),
                        ])

                        <div class="flex items-center justify-end gap-3 border-t border-slate-200 pt-5">
                            <a href="{{ route('missions.index') }}" class="text-slate-600 hover:text-slate-900">Annuler</a>
                            <x-primary-button class="!rounded-xl !bg-brand-primary px-5 py-2.5 !normal-case hover:!bg-brand-primary-dark">
                                {{ __('Créer') }}
                            </x-primary-button>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>
</x-app-layout>
