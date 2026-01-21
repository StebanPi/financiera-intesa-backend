<?php

namespace App\Http\Requests\Api\V1;

use Illuminate\Foundation\Http\FormRequest;

class DischargeConceptUpdateRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    /** @return array<string, mixed> */
    public function rules(): array
    {
        return [
            'nombre' => ['sometimes', 'required', 'string', 'max:255'],
            'descripcion' => ['nullable', 'string'],
            'state' => ['nullable', 'boolean'],
            'debe' => ['sometimes', 'required', 'integer', 'exists:debes,id'],
            'haber' => ['sometimes', 'required', 'integer', 'exists:habers,id'],
        ];
    }

    protected function prepareForValidation(): void
    {
        if ($this->has('state') && !is_bool($this->state)) {
            $this->merge(['state' => filter_var($this->state, FILTER_VALIDATE_BOOLEAN)]);
        }
    }
}
