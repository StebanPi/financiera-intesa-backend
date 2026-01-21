<?php

namespace App\Http\Requests\Api\V1;

use Illuminate\Foundation\Http\FormRequest;

class SingleDateRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    /**
     * @return array<string, mixed>
     */
    public function rules(): array
    {
        return [
            'fecha' => 'required|date',
        ];
    }

    /**
     * @return array<string, string>
     */
    public function messages(): array
    {
        return [
            'fecha.required' => 'El parámetro fecha es obligatorio (YYYY-MM-DD).',
            'fecha.date' => 'fecha debe ser una fecha válida (YYYY-MM-DD).',
        ];
    }
}
