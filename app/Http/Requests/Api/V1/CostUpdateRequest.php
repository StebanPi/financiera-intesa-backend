<?php

namespace App\Http\Requests\Api\V1;

use Illuminate\Foundation\Http\FormRequest;

class CostUpdateRequest extends FormRequest
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
            'valor_semestre' => ['sometimes', 'numeric', 'min:0'],
            'numero_semestre' => ['sometimes', 'integer', 'min:0'],
            'valor_total_semestre' => ['nullable', 'numeric', 'min:0'],
            'descuento' => ['sometimes', 'numeric', 'min:0'],
            'valor_neto' => ['nullable', 'numeric', 'min:0'],
            'saldo_financiar' => ['nullable', 'numeric', 'min:0'],
            'periodo' => ['sometimes', 'string', 'max:255'],
            'numero_cuotas' => ['sometimes', 'integer', 'min:0'],
            'valor_cuotas' => ['nullable', 'numeric', 'min:0'],
            'fecha_pago' => ['sometimes', 'date'],
            'detalles' => ['nullable', 'string'],
        ];
    }
}
