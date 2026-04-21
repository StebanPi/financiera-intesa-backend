<?php

namespace App\Http\Requests\Api\V1;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Support\Str;

class EntryStoreRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'id_cost' => ['nullable', 'integer', 'exists:costs,id'],
            'cod_alumno' => ['required'],
            'concepto' => ['required', 'integer', 'exists:conceptos,id'],
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
