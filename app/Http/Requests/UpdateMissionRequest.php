<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class UpdateMissionRequest extends FormRequest
{
    public function authorize(): bool
    {
        return $this->user() !== null;
    }

    public function rules(): array
    {
        return [
            'reference_id' => ['required', 'integer', 'exists:reference_points,id'],
            'type_mission' => ['required', 'string', 'in:Branchement,Coupure,Réparation,Contrôle,Autre'],
            'priorite' => ['required', 'string', 'in:Basse,Normale,Haute,Urgente'],
            'description' => ['nullable', 'string'],
            'statut' => ['required', 'string', 'in:Créée,Assignée,En cours,Bloquée,Terminée,Annulée'],
            'due_at' => ['nullable', 'date'],
            'started_at' => ['nullable', 'date'],
            'completed_at' => ['nullable', 'date'],
            'technicien_id' => ['required', 'integer', 'exists:techniciens,id'],
        ];
    }

    public function messages(): array
    {
        return [
            'reference_id.required' => 'La reference est obligatoire.',
            'reference_id.exists' => 'La reference selectionnee est introuvable.',
            'type_mission.in' => 'Le type de mission selectionne est invalide.',
            'priorite.in' => 'La priorite selectionnee est invalide.',
            'statut.in' => 'Le statut selectionne est invalide.',
            'due_at.date' => 'La date d\'echeance est invalide.',
            'started_at.date' => 'La date de debut est invalide.',
            'completed_at.date' => 'La date de fin est invalide.',
            'technicien_id.required' => 'Le technicien est obligatoire.',
            'technicien_id.exists' => 'Le technicien selectionne est introuvable.',
        ];
    }

    public function attributes(): array
    {
        return [
            'reference_id' => 'reference',
            'type_mission' => 'type de mission',
            'priorite' => 'priorite',
            'description' => 'description',
            'statut' => 'statut',
            'due_at' => 'date d\'echeance',
            'started_at' => 'date de debut',
            'completed_at' => 'date de fin',
            'technicien_id' => 'technicien',
        ];
    }
}
