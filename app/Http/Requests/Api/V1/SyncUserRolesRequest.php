<?php

namespace App\Http\Requests\Api\V1;

use Illuminate\Foundation\Http\FormRequest;

class SyncUserRolesRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    /**
     * roles: array de slugs de roles existentes.
     *
     * @return array<string, mixed>
     */
    public function rules(): array
    {
        return [
            'roles' => ['required', 'array', 'min:0'],
            'roles.*' => ['required', 'string', 'exists:roles,slug'],
        ];
    }

    /**
     * @return array<string, string>
     */
    public function messages(): array
    {
        return [
            'roles.required' => 'El campo roles es obligatorio.',
            'roles.*.exists' => 'Uno o más roles no existen.',
        ];
    }
}
