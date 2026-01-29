<?php

namespace App\Http\Requests\Api\V1;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Support\Str;

class OtherEntryStoreRequest extends FormRequest
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
            'id_cost' => ['required', 'integer', 'exists:costs,id'],
            'concepto' => ['required', 'integer', 'exists:otros_conceptos,id'],
            'descripcion' => ['required', 'string'],
            'fecha_recibo' => ['required', 'date'],
            'valor' => ['required', 'numeric', 'min:0'],
            'elaborado_por' => ['required', 'integer', 'exists:elaborados,id'],
            'debe' => ['required', 'integer', 'exists:debes,id'],
            'haber' => ['required', 'integer', 'exists:habers,id'],
            'forma' => ['nullable', 'string', 'in:Efectivo,Bancos,Consignación'],
            'sede' => ['nullable', 'string'],
        ];
    }

    protected function prepareForValidation(): void
    {
        $valor = is_string($this->valor ?? null) ? Str::replace('.', '', $this->valor) : $this->valor;
        $this->merge([
            'valor' => $valor ?? '0',
            'forma' => $this->forma ?? 'Efectivo',
        ]);
    }
}
