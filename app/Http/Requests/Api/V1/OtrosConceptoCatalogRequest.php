<?php

namespace App\Http\Requests\Api\V1;

use Illuminate\Foundation\Http\FormRequest;

class OtrosConceptoCatalogRequest extends FormRequest
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
        return static::getRules($this->isMethod('post'));
    }

    /** @return array<string, mixed> */
    public static function getRules(bool $isStore): array
    {
        return [
            'nombre' => [ $isStore ? 'required' : 'sometimes', 'string', 'max:255' ],
            'estado' => [ $isStore ? 'required' : 'sometimes', 'integer', 'in:0,1' ],
            'debe' => [ $isStore ? 'required' : 'nullable', 'integer', 'exists:debes,id' ],
            'haber' => [ $isStore ? 'required' : 'nullable', 'integer', 'exists:habers,id' ],
        ];
    }
}
