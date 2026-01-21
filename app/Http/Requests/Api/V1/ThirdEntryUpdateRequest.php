<?php

namespace App\Http\Requests\Api\V1;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class ThirdEntryUpdateRequest extends FormRequest
{
    public function authorize(): bool { return true; }

    /** @return array<string, mixed> */
    public function rules(): array
    {
        $id = (int) $this->route('id');
        return [
            'cedula' => ['sometimes', 'string', 'max:255', Rule::unique('third_entries', 'cedula')->ignore($id)],
            'nombre' => ['sometimes', 'required', 'string', 'max:255'],
            'direccion' => ['nullable', 'string'],
            'telefono' => ['nullable', 'string', 'max:255'],
            'actividad' => ['sometimes', 'required', 'integer', 'exists:third_activities,id'],
            'mas' => ['nullable', 'string'],
        ];
    }
}
