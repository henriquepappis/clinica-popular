<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class StorePriceRequest extends FormRequest
{
    public function authorize(): bool
    {
        return auth()->check();
    }

    public function rules(): array
    {
        return [
            'value' => 'required|numeric|min:0.01',
            'doctor_id' => 'nullable|uuid|exists:doctors,id',
            'specialty_id' => 'nullable|uuid|exists:specialties,id',
            'duration_minutes' => 'nullable|integer|min:1',
        ];
    }

    public function messages(): array
    {
        return [
            'value.required' => 'Valor é obrigatório',
            'value.numeric' => 'Valor deve ser numérico',
            'value.min' => 'Valor deve ser maior que zero',
            'doctor_id.exists' => 'Médico não encontrado',
            'specialty_id.exists' => 'Especialidade não encontrada',
            'duration_minutes.min' => 'Duração deve ser de pelo menos 1 minuto',
        ];
    }
}
