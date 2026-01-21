<?php

namespace App\Http\Requests\Api\V1;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Support\Str;

class DischargeStoreRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    /** @return array<string, mixed> */
    public function rules(): array
    {
        return [
            'fecha_recibo' => ['required', 'date'],
            'proveedor_id' => ['required', 'integer', 'exists:egreso_providers,id'],
            'forma' => ['required', 'string', 'in:Efectivo,Bancos'],
            'concepto' => ['required', 'integer', 'exists:egreso_concepts,id'],
            'descripcion' => ['nullable', 'string'],
            'valor' => ['required', 'numeric', 'min:0'],
            'elaborado_por' => ['required', 'integer', 'exists:elaborados,id'],
        ];
    }

    protected function prepareForValidation(): void
    {
        $v = $this->valor;
        if (is_string($v)) {
            $this->merge(['valor' => Str::replace('.', '', $v)]);
        }
    }
}
