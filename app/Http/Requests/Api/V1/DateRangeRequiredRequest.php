<?php

namespace App\Http\Requests\Api\V1;

use Illuminate\Foundation\Http\FormRequest;

/**
 * fecha_inicio y fecha_fin obligatorios para endpoints de descarga Excel.
 */
class DateRangeRequiredRequest extends FormRequest
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
            'fecha_inicio' => 'required|date',
            'fecha_fin' => 'required|date|after_or_equal:fecha_inicio',
        ];
    }

    /**
     * @return array<string, string>
     */
    public function messages(): array
    {
        return [
            'fecha_inicio.required' => 'fecha_inicio es obligatorio para la descarga.',
            'fecha_inicio.date' => 'fecha_inicio debe ser una fecha válida (YYYY-MM-DD).',
            'fecha_fin.required' => 'fecha_fin es obligatorio para la descarga.',
            'fecha_fin.date' => 'fecha_fin debe ser una fecha válida (YYYY-MM-DD).',
            'fecha_fin.after_or_equal' => 'fecha_fin debe ser igual o posterior a fecha_inicio.',
        ];
    }
}
