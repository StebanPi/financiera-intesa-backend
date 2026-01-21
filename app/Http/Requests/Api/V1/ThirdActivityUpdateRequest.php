<?php

namespace App\Http\Requests\Api\V1;

use Illuminate\Foundation\Http\FormRequest;

class ThirdActivityUpdateRequest extends FormRequest
{
    public function authorize(): bool { return true; }

    /** @return array<string, mixed> */
    public function rules(): array
    {
        return [ 'nombre' => ['sometimes', 'required', 'string', 'max:255'] ];
    }
}
