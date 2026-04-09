@php
    $types = ['Branchement', 'Coupure', 'Réparation', 'Contrôle', 'Autre'];
    $priorites = ['Basse', 'Normale', 'Haute', 'Urgente'];
    $statuts = ['Créée', 'Assignée', 'En cours', 'Bloquée', 'Terminée', 'Annulée'];
    $labelClass = 'text-[0.72rem] font-semibold uppercase tracking-[0.16em] text-slate-600';
    $controlClass = 'mt-2 block w-full rounded-xl border-slate-300 bg-white px-3 py-2 text-sm font-medium text-slate-800 shadow-sm transition focus:border-brand-primary focus:ring-brand-primary';
@endphp

<div class="grid grid-cols-1 gap-6">
    <div>
        <x-input-label for="reference_id" :value="__('Référence')" class="{{ $labelClass }}" />
        <select id="reference_id" name="reference_id" class="{{ $controlClass }}" required data-tom-select data-placeholder="Rechercher une référence...">
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
            <x-input-label for="type_mission" :value="__('Type mission')" class="{{ $labelClass }}" />
            <select id="type_mission" name="type_mission" class="{{ $controlClass }}" required>
                @foreach ($types as $t)
                    <option value="{{ $t }}" {{ old('type_mission', $mission->type_mission ?? 'Autre') === $t ? 'selected' : '' }}>
                        {{ $t }}
                    </option>
                @endforeach
            </select>
            <x-input-error :messages="$errors->get('type_mission')" class="mt-2" />
        </div>

        <div>
            <x-input-label for="priorite" :value="__('Priorité')" class="{{ $labelClass }}" />
            <select id="priorite" name="priorite" class="{{ $controlClass }}" required>
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
        <x-input-label for="statut" :value="__('Statut')" class="{{ $labelClass }}" />
        <select id="statut" name="statut" class="{{ $controlClass }}" required>
            @foreach ($statuts as $s)
                <option value="{{ $s }}" {{ old('statut', $mission->statut ?? 'Créée') === $s ? 'selected' : '' }}>
                    {{ $s }}
                </option>
            @endforeach
        </select>
        <x-input-error :messages="$errors->get('statut')" class="mt-2" />
    </div>

    <div>
        <x-input-label for="due_at" :value="__('Échéance (optionnel)')" class="{{ $labelClass }}" />
        <x-text-input id="due_at" name="due_at" type="datetime-local" class="{{ $controlClass }}" :value="old('due_at', isset($mission) && $mission->due_at ? $mission->due_at->format('Y-m-d\\TH:i') : '')" />
        <x-input-error :messages="$errors->get('due_at')" class="mt-2" />
    </div>

    <div>
        <x-input-label for="description" :value="__('Description')" class="{{ $labelClass }}" />
        <textarea id="description" name="description" rows="4" class="{{ $controlClass }}">{{ old('description', $mission->description ?? '') }}</textarea>
        <x-input-error :messages="$errors->get('description')" class="mt-2" />
    </div>

    <div>
        <x-input-label for="technicien_id" :value="__('Assigner à')" class="{{ $labelClass }}" />
        <select id="technicien_id" name="technicien_id" class="{{ $controlClass }}" data-tom-select data-placeholder="Rechercher un technicien..." required>
            <option value="" disabled {{ old('technicien_id', $currentTechnicienId ?? '') ? '' : 'selected' }}>Choisir un technicien...</option>
            @foreach ($techniciens as $t)
                <option value="{{ $t->id }}" {{ (string) old('technicien_id', $currentTechnicienId ?? '') === (string) $t->id ? 'selected' : '' }}>
                    {{ $t->prenom }} {{ $t->nom }}{{ $t->zone_intervention ? ' — '.$t->zone_intervention : '' }}
                </option>
            @endforeach
        </select>
        <x-input-error :messages="$errors->get('technicien_id')" class="mt-2" />
        <p class="mt-2 text-xs font-medium text-slate-500">Le technicien est obligatoire. Changer le technicien crée une nouvelle affectation (historique conservé).</p>
    </div>
</div>
