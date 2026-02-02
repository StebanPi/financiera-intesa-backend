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
            'fecha_inicio' => 'nullable|date',
            'fecha_fin' => 'nullable|date|after_or_equal:fecha_inicio',
            'mes' => 'nullable|integer|min:1|max:12',
            'anio' => 'nullable|integer|min:2020|max:2100',
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
            'mes.integer' => 'El mes debe ser un número entero.',
            'mes.min' => 'El mes debe ser mayor o igual a 1.',
            'mes.max' => 'El mes debe ser menor o igual a 12.',
            'anio.integer' => 'El año debe ser un número entero.',
            'anio.min' => 'El año debe ser mayor o igual a 2020.',
            'anio.max' => 'El año debe ser menor o igual a 2100.',
        ];
    }
}
