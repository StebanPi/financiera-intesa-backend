<?php

namespace App\Http\Requests\Api\V1;

use Illuminate\Foundation\Http\FormRequest;

class ModuleCatalogRequest extends FormRequest
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
            'name' => [ $isStore ? 'required' : 'sometimes', 'string', 'max:255' ],
            'code' => [ 'nullable', 'string', 'max:255' ],
            'active' => [ 'nullable', 'integer', 'in:0,1' ],
        ];
    }
}
