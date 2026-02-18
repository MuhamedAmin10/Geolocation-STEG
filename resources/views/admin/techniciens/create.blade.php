<x-app-layout>
    <x-slot name="header">
        <h2 class="font-semibold text-xl text-gray-800 leading-tight">
            {{ __('Nouveau technicien') }}
        </h2>
    </x-slot>

    <div class="py-12">
        <div class="max-w-3xl mx-auto sm:px-6 lg:px-8">
            <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg">
                <div class="p-6 text-gray-900">
                    <form method="POST" action="{{ route('admin.techniciens.store') }}" class="space-y-6">
                        @csrf

                        <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                            <div>
                                <x-input-label for="name" :value="__('Nom (compte)')" />
                                <x-text-input id="name" name="name" type="text" class="mt-1 block w-full" :value="old('name')" required />
                                <x-input-error :messages="$errors->get('name')" class="mt-2" />
                            </div>

                            <div>
                                <x-input-label for="email" :value="__('Email')" />
                                <x-text-input id="email" name="email" type="email" class="mt-1 block w-full" :value="old('email')" required />
                                <x-input-error :messages="$errors->get('email')" class="mt-2" />
                            </div>

                            <div>
                                <x-input-label for="password" :value="__('Mot de passe')" />
                                <x-text-input id="password" name="password" type="password" class="mt-1 block w-full" required />
                                <x-input-error :messages="$errors->get('password')" class="mt-2" />
                            </div>

                            <div class="flex items-center gap-2 mt-6">
                                <input id="active" name="active" type="checkbox" value="1" class="rounded border-gray-300" {{ old('active', true) ? 'checked' : '' }}>
                                <label for="active" class="text-sm text-gray-700">Compte actif</label>
                            </div>
                        </div>

                        <hr>

                        <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                            <div>
                                <x-input-label for="prenom" :value="__('Prénom')" />
                                <x-text-input id="prenom" name="prenom" type="text" class="mt-1 block w-full" :value="old('prenom')" required />
                                <x-input-error :messages="$errors->get('prenom')" class="mt-2" />
                            </div>

                            <div>
                                <x-input-label for="nom" :value="__('Nom')" />
                                <x-text-input id="nom" name="nom" type="text" class="mt-1 block w-full" :value="old('nom')" required />
                                <x-input-error :messages="$errors->get('nom')" class="mt-2" />
                            </div>

                            <div>
                                <x-input-label for="telephone" :value="__('Téléphone')" />
                                <x-text-input id="telephone" name="telephone" type="text" class="mt-1 block w-full" :value="old('telephone')" required />
                                <x-input-error :messages="$errors->get('telephone')" class="mt-2" />
                            </div>

                            <div>
                                <x-input-label for="zone_intervention" :value="__('Zone')" />
                                <x-text-input id="zone_intervention" name="zone_intervention" type="text" class="mt-1 block w-full" :value="old('zone_intervention')" />
                                <x-input-error :messages="$errors->get('zone_intervention')" class="mt-2" />
                            </div>

                            <div class="md:col-span-2">
                                <x-input-label for="competences" :value="__('Compétences')" />
                                <textarea id="competences" name="competences" rows="3" class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500">{{ old('competences') }}</textarea>
                                <x-input-error :messages="$errors->get('competences')" class="mt-2" />
                            </div>

                            <div class="flex items-center gap-2">
                                <input id="disponible" name="disponible" type="checkbox" value="1" class="rounded border-gray-300" {{ old('disponible', true) ? 'checked' : '' }}>
                                <label for="disponible" class="text-sm text-gray-700">Disponible</label>
                            </div>
                        </div>

                        <div class="flex items-center justify-end gap-3">
                            <a href="{{ route('admin.techniciens.index') }}" class="text-gray-600 hover:text-gray-900">Annuler</a>
                            <x-primary-button>{{ __('Créer') }}</x-primary-button>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>
</x-app-layout>
