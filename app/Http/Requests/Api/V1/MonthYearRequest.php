<?php

namespace App\Http\Requests\Api\V1;

use Illuminate\Contracts\Validation\Validator;
use Illuminate\Foundation\Http\FormRequest;

class MonthYearRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    /**
     * month_year (Y-m) o bien mes + anio. Debe proporcionarse uno de los dos formatos.
     * @return array<string, mixed>
     */
    public function rules(): array
    {
        return [
            'month_year' => 'nullable|date_format:Y-m',
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
            'month_year.date_format' => 'month_year debe tener formato YYYY-MM (ej. 2024-01).',
            'mes.integer' => 'mes debe ser un número entero.',
            'mes.min' => 'mes debe estar entre 1 y 12.',
            'mes.max' => 'mes debe estar entre 1 y 12.',
            'anio.integer' => 'anio debe ser un número entero.',
            'anio.min' => 'anio debe ser al menos 2020.',
            'anio.max' => 'anio debe ser como máximo 2100.',
        ];
    }

    public function withValidator(Validator $validator): void
    {
        $validator->after(function (Validator $validator) {
            $monthYear = $this->filled('month_year');
            $mesAnio = $this->filled('mes') && $this->filled('anio');

            if (!$monthYear && !$mesAnio) {
                $validator->errors()->add(
                    'month_year',
                    'Debe proporcionar month_year (YYYY-MM) o bien mes y anio.'
                );
            }
            if ($monthYear && $mesAnio) {
                // Si vienen ambos, se usará month_year (validado en el controlador)
            }
        });
    }

    /**
     * Devuelve [mes (int), anio (int)] para el servicio.
     */
    public function getMesAnio(): array
    {
        if ($this->filled('month_year')) {
            $p = explode('-', $this->month_year);
            return [(int) ($p[1] ?? 1), (int) ($p[0] ?? date('Y'))];
        }
        return [(int) $this->mes, (int) $this->anio];
    }
}
