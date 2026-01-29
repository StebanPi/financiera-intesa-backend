<?php

namespace App\Http\Requests\Api\V1;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Support\Str;

class DischargeUpdateRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    /** @return array<string, mixed> */
    public function rules(): array
    {
        return [
            'fecha_recibo' => ['sometimes', 'date'],
            'proveedor_id' => ['sometimes', 'integer', 'exists:egreso_providers,id'],
            'forma' => ['sometimes', 'string', 'in:Efectivo,Bancos'],
            'concepto' => ['sometimes', 'integer', 'exists:egreso_concepts,id'],
            'descripcion' => ['nullable', 'string'],
            'valor' => ['sometimes', 'numeric', 'min:0'],
            'elaborado_por' => ['sometimes', 'integer', 'exists:elaborados,id'],
        ];
    }

    protected function prepareForValidation(): void
    {
        if ($this->has('valor')) {
            $v = $this->valor;
            if (is_string($v)) {
                $this->merge(['valor' => Str::replace('.', '', $v)]);
            }
        }
    }
}
