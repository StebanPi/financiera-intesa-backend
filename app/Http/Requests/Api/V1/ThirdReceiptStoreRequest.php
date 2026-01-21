<?php

namespace App\Http\Requests\Api\V1;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Support\Str;

class ThirdReceiptStoreRequest extends FormRequest
{
    public function authorize(): bool { return true; }

    /** @return array<string, mixed> */
    public function rules(): array
    {
        return [
            'third' => ['required', 'integer', 'exists:third_entries,id'],
            'concepto' => ['required', 'integer', 'exists:concept_entry_receipts,id'],
            'detalles' => ['nullable', 'string'],
            'valor' => ['required', 'numeric', 'min:0'],
            'debe' => ['required', 'integer', 'exists:debes,id'],
            'haber' => ['required', 'integer', 'exists:habers,id'],
            'elaborado_por' => ['required', 'integer', 'exists:elaborados,id'],
            'forma' => ['nullable', 'string', 'in:Efectivo,Bancos,Consignación'],
            'fecha_recibo' => ['required', 'date'],
        ];
    }

    protected function prepareForValidation(): void
    {
        $this->merge([
            'valor' => is_string($this->valor ?? null) ? Str::replace('.', '', $this->valor) : $this->valor,
            'forma' => $this->forma ?? 'Efectivo',
        ]);
    }
}
