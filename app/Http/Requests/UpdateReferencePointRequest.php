<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class UpdateReferencePointRequest extends FormRequest
{
    public function authorize(): bool
    {
        return $this->user() !== null;
    }

    public function rules(): array
    {
        return [
            'latitude' => ['required', 'numeric', 'between:-90,90'],
            'longitude' => ['required', 'numeric', 'between:-180,180'],
            'adresse' => ['nullable', 'string'],
            'gouvernorat' => ['nullable', 'string', 'max:255'],
            'delegation' => ['nullable', 'string', 'max:255'],
            'precision_m' => ['nullable', 'integer', 'min:0'],
            'statut' => ['required', 'string', 'in:à vérifier,validé,rejeté'],
        ];
    }
}
