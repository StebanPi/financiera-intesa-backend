<?php

namespace App\Http\Requests\Api\V1;

use Illuminate\Foundation\Http\FormRequest;

class ThirdEntryStoreRequest extends FormRequest
{
    public function authorize(): bool { return true; }

    /** @return array<string, mixed> */
    public function rules(): array
    {
        return [
            'cedula' => ['required', 'string', 'max:255', 'unique:third_entries,cedula'],
            'nombre' => ['required', 'string', 'max:255'],
            'direccion' => ['nullable', 'string'],
            'telefono' => ['nullable', 'string', 'max:255'],
            'actividad' => ['required', 'integer', 'exists:third_activities,id'],
            'mas' => ['nullable', 'string'],
        ];
    }
}
