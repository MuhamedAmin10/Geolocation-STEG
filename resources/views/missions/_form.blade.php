@php
    $types = ['Branchement', 'Coupure', 'Réparation', 'Contrôle', 'Autre'];
    $priorites = ['Basse', 'Normale', 'Haute', 'Urgente'];
    $statuts = ['Créée', 'Assignée', 'En cours', 'Bloquée', 'Terminée', 'Annulée'];
@endphp

<div class="grid grid-cols-1 gap-6">
    <div>
        <x-input-label for="reference_id" :value="__('Référence')" />
        <select id="reference_id" name="reference_id" class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500" required>
            <option value="" disabled {{ old('reference_id', $mission->reference_id ?? '') ? '' : 'selected' }}>Choisir une référence...</option>
            @foreach ($referencePoints as $rp)
                <option value="{{ $rp->id }}" {{ (string) old('reference_id', $mission->reference_id ?? '') === (string) $rp->id ? 'selected' : '' }}>
                    {{ $rp->reference }} — {{ \Illuminate\Support\Str::limit($rp->adresse ?? '', 60) }}
                </option>
            @endforeach
        </select>
        <x-input-error :messages="$errors->get('reference_id')" class="mt-2" />
    </div>

    <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
        <div>
            <x-input-label for="type_mission" :value="__('Type mission')" />
            <select id="type_mission" name="type_mission" class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500" required>
                @foreach ($types as $t)
                    <option value="{{ $t }}" {{ old('type_mission', $mission->type_mission ?? 'Autre') === $t ? 'selected' : '' }}>
                        {{ $t }}
                    </option>
                @endforeach
            </select>
            <x-input-error :messages="$errors->get('type_mission')" class="mt-2" />
        </div>

        <div>
            <x-input-label for="priorite" :value="__('Priorité')" />
            <select id="priorite" name="priorite" class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500" required>
                @foreach ($priorites as $p)
                    <option value="{{ $p }}" {{ old('priorite', $mission->priorite ?? 'Normale') === $p ? 'selected' : '' }}>
                        {{ $p }}
                    </option>
                @endforeach
            </select>
            <x-input-error :messages="$errors->get('priorite')" class="mt-2" />
        </div>
    </div>

    <div>
        <x-input-label for="statut" :value="__('Statut')" />
        <select id="statut" name="statut" class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500" required>
            @foreach ($statuts as $s)
                <option value="{{ $s }}" {{ old('statut', $mission->statut ?? 'Créée') === $s ? 'selected' : '' }}>
                    {{ $s }}
                </option>
            @endforeach
        </select>
        <x-input-error :messages="$errors->get('statut')" class="mt-2" />
    </div>

    <div>
        <x-input-label for="due_at" :value="__('Échéance (optionnel)')" />
        <x-text-input id="due_at" name="due_at" type="datetime-local" class="mt-1 block w-full" :value="old('due_at', isset($mission) && $mission->due_at ? $mission->due_at->format('Y-m-d\\TH:i') : '')" />
        <x-input-error :messages="$errors->get('due_at')" class="mt-2" />
    </div>

    <div>
        <x-input-label for="description" :value="__('Description')" />
        <textarea id="description" name="description" rows="4" class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500">{{ old('description', $mission->description ?? '') }}</textarea>
        <x-input-error :messages="$errors->get('description')" class="mt-2" />
    </div>

    <div>
        <x-input-label for="technicien_id" :value="__('Assigner à (optionnel)')" />
        <select id="technicien_id" name="technicien_id" class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500">
            <option value="">— Aucun —</option>
            @foreach ($techniciens as $t)
                <option value="{{ $t->id }}" {{ (string) old('technicien_id', $currentTechnicienId ?? '') === (string) $t->id ? 'selected' : '' }}>
                    {{ $t->prenom }} {{ $t->nom }}{{ $t->zone_intervention ? ' — '.$t->zone_intervention : '' }}
                </option>
            @endforeach
        </select>
        <x-input-error :messages="$errors->get('technicien_id')" class="mt-2" />
        <p class="mt-2 text-xs text-gray-500">Changer le technicien crée une nouvelle affectation (historique conservé).</p>
    </div>
</div>
