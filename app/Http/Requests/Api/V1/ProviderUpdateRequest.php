<?php

namespace App\Http\Requests\Api\V1;

use Illuminate\Foundation\Http\FormRequest;

class ProviderUpdateRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    /** @return array<string, mixed> */
    public function rules(): array
    {
        return [
            'cedula' => ['sometimes', 'nullable', 'string', 'max:255'],
            'nombre' => ['sometimes', 'required', 'string', 'max:255'],
            'direccion' => ['sometimes', 'nullable', 'string'],
            'telefono' => ['sometimes', 'nullable', 'string', 'max:255'],
        ];
    }
}
