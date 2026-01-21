<?php

namespace App\Http\Requests\Api\V1;

use Illuminate\Foundation\Http\FormRequest;

class DateRangeRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    /**
     * fecha_inicio y fecha_fin: si se envía uno, el otro es obligatorio. Si se omiten ambos, el reporte usa todo el rango.
     * @return array<string, mixed>
     */
    public function rules(): array
    {
        return [
            'fecha_inicio' => 'required_with:fecha_fin|nullable|date',
            'fecha_fin' => 'required_with:fecha_inicio|nullable|date|after_or_equal:fecha_inicio',
        ];
    }

    /**
     * @return array<string, string>
     */
    public function messages(): array
    {
        return [
            'fecha_inicio.required_with' => 'fecha_inicio es obligatorio cuando se envía fecha_fin.',
            'fecha_inicio.date' => 'fecha_inicio debe ser una fecha válida (YYYY-MM-DD).',
            'fecha_fin.required_with' => 'fecha_fin es obligatorio cuando se envía fecha_inicio.',
            'fecha_fin.date' => 'fecha_fin debe ser una fecha válida (YYYY-MM-DD).',
            'fecha_fin.after_or_equal' => 'fecha_fin debe ser igual o posterior a fecha_inicio.',
        ];
    }
}
