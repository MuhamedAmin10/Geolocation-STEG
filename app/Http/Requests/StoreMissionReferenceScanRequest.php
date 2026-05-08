<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class StoreMissionReferenceScanRequest extends FormRequest
{
    public function authorize(): bool
    {
        return $this->user() !== null;
    }

    public function rules(): array
    {
        return [
            'qr_code' => ['required', 'string', 'max:255'],
            'compteur_type' => ['required', 'string', 'in:mechanique,electrique,autre'],
            'latitude' => ['required', 'numeric', 'between:-90,90'],
            'longitude' => ['required', 'numeric', 'between:-180,180'],
            'accuracy_m' => ['nullable', 'numeric', 'min:0', 'max:100000'],
            'notes' => ['nullable', 'string', 'max:1000'],
        ];
    }
}