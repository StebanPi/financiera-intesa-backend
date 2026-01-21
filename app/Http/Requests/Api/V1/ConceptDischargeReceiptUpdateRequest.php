<?php

namespace App\Http\Requests\Api\V1;

use Illuminate\Foundation\Http\FormRequest;

class ConceptDischargeReceiptUpdateRequest extends FormRequest
{
    public function authorize(): bool { return true; }

    /** @return array<string, mixed> */
    public function rules(): array
    {
        return [
            'name' => ['sometimes', 'required', 'string', 'max:255'],
            'debe' => ['sometimes', 'required', 'integer', 'exists:debes,id'],
            'haber' => ['sometimes', 'required', 'integer', 'exists:habers,id'],
            'state' => ['nullable', 'boolean'],
        ];
    }
}
