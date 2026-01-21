<?php

namespace App\Http\Requests\Api\V1;

use Illuminate\Foundation\Http\FormRequest;

class SyncRolePermissionsRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    /**
     * permissions: array de slugs de permisos existentes.
     *
     * @return array<string, mixed>
     */
    public function rules(): array
    {
        return [
            'permissions' => ['required', 'array', 'min:0'],
            'permissions.*' => ['required', 'string', 'exists:permissions,slug'],
        ];
    }

    /**
     * @return array<string, string>
     */
    public function messages(): array
    {
        return [
            'permissions.required' => 'El campo permissions es obligatorio.',
            'permissions.*.exists' => 'Uno o más permisos no existen.',
        ];
    }
}
