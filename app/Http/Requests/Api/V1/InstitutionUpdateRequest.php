<?php

namespace App\Http\Requests\Api\V1;

use Illuminate\Foundation\Http\FormRequest;

class InstitutionUpdateRequest extends FormRequest
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
        return [
            'name' => [ 'sometimes', 'string', 'max:255' ],
            'logo_path' => [ 'nullable', 'string', 'max:255' ],
            'institucion_subtitulo' => [ 'nullable', 'string', 'max:255' ],
            'sede' => [ 'nullable', 'string', 'max:255' ],
            'nit' => [ 'nullable', 'string', 'max:255' ],
            'address' => [ 'nullable', 'string' ],
            'phone' => [ 'nullable', 'string', 'max:255' ],
            'telefono2' => [ 'nullable', 'string', 'max:255' ],
            'telefono3' => [ 'nullable', 'string', 'max:255' ],
            'email' => [ 'nullable', 'email', 'max:255' ],
            'website' => [ 'nullable', 'string', 'max:255' ],
            'footer_licencia_texto' => [ 'nullable', 'string' ],
            'footer_ciudad' => [ 'nullable', 'string', 'max:255' ],
            'footer_mostrar_ubicacion_fecha' => [ 'nullable', 'boolean' ],
            'footer_firma' => [ 'nullable', 'string', 'max:255' ],
        ];
    }
}
