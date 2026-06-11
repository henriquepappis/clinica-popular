<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class StoreWaitingListRequest extends FormRequest
{
    public function authorize(): bool
    {
        return auth()->check();
    }

    public function rules(): array
    {
        return [
            'patient_id' => 'required|uuid|exists:patients,id',
            'specialty_id' => 'required|uuid|exists:specialties,id',
            'priority' => 'nullable|integer|between:1,3',
            'reason' => 'nullable|string|max:500',
        ];
    }

    public function messages(): array
    {
        return [
            'patient_id.required' => 'Paciente é obrigatório',
            'patient_id.exists' => 'Paciente não encontrado',
            'specialty_id.required' => 'Especialidade é obrigatória',
            'specialty_id.exists' => 'Especialidade não encontrada',
            'priority.between' => 'Prioridade deve ser entre 1 e 3',
        ];
    }
}
