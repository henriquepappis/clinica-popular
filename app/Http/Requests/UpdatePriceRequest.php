<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class UpdatePriceRequest extends FormRequest
{
    public function authorize(): bool
    {
        return auth()->check();
    }

    public function rules(): array
    {
        return [
            'value' => 'required|numeric|min:0.01',
        ];
    }

    public function messages(): array
    {
        return [
            'value.required' => 'Valor é obrigatório',
            'value.numeric' => 'Valor deve ser numérico',
            'value.min' => 'Valor deve ser maior que zero',
        ];
    }
}
