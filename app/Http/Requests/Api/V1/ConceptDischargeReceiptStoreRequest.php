<?php

namespace App\Http\Requests\Api\V1;

use Illuminate\Foundation\Http\FormRequest;

class ConceptDischargeReceiptStoreRequest extends FormRequest
{
    public function authorize(): bool { return true; }

    /** @return array<string, mixed> */
    public function rules(): array
    {
        return [
            'name' => ['required', 'string', 'max:255'],
            'debe' => ['required', 'integer', 'exists:debes,id'],
            'haber' => ['required', 'integer', 'exists:habers,id'],
            'state' => ['nullable', 'boolean'],
        ];
    }

    protected function prepareForValidation(): void
    {
        if ($this->has('state') && !is_bool($this->state)) {
            $this->merge(['state' => filter_var($this->state, FILTER_VALIDATE_BOOLEAN)]);
        }
    }
}
