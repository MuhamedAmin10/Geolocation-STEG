<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class UpdateMissionWorkRequest extends FormRequest
{
    public function authorize(): bool
    {
        return $this->user() !== null;
    }

    public function rules(): array
    {
        return [
            'statut' => ['required', 'string', 'in:En cours,Bloquée,Terminée'],
            'rapport' => ['nullable', 'string'],
        ];
    }
}
