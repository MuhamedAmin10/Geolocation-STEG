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
            'type_mission' => ['required', 'string', 'max:255'],
            'priorite' => ['required', 'string', 'max:255'],
            'description' => ['nullable', 'string'],
            'statut' => ['required', 'string', 'max:255'],
            'due_at' => ['nullable', 'date'],
            'started_at' => ['nullable', 'date'],
            'completed_at' => ['nullable', 'date'],
            'technicien_id' => ['nullable', 'integer', 'exists:techniciens,id'],
        ];
    }
}
