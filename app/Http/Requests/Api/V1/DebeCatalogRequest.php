<?php

namespace App\Http\Requests\Api\V1;

use Illuminate\Foundation\Http\FormRequest;

class DebeCatalogRequest extends FormRequest
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
            'cuenta' => [ $isStore ? 'required' : 'sometimes', 'string' ],
            'nombre' => [ $isStore ? 'required' : 'sometimes', 'string' ],
        ];
    }
}
