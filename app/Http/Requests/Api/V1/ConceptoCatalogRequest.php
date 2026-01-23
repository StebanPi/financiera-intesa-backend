<?php

namespace App\Http\Requests\Api\V1;

use Illuminate\Foundation\Http\FormRequest;

class ConceptoCatalogRequest extends FormRequest
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
            'orderTable' => [ $isStore ? 'required' : 'sometimes', 'integer', 'in:0,1' ],
            'consecutivo' => [ $isStore ? 'required' : 'sometimes', 'integer', 'in:0,1' ],
        ];
    }
}
