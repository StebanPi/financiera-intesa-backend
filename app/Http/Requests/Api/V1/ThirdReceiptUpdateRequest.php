<?php

namespace App\Http\Requests\Api\V1;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Support\Str;

class ThirdReceiptUpdateRequest extends FormRequest
{
    public function authorize(): bool { return true; }

    /** @return array<string, mixed> */
    public function rules(): array
    {
        return [
            'third' => ['sometimes', 'integer', 'exists:third_entries,id'],
            'concepto' => ['sometimes', 'integer', 'exists:concept_entry_receipts,id'],
            'detalles' => ['nullable', 'string'],
            'valor' => ['sometimes', 'numeric', 'min:0'],
            'debe' => ['sometimes', 'integer', 'exists:debes,id'],
            'haber' => ['sometimes', 'integer', 'exists:habers,id'],
            'elaborado_por' => ['sometimes', 'integer', 'exists:elaborados,id'],
            'forma' => ['nullable', 'string', 'in:Efectivo,Bancos,Consignación'],
            'fecha_recibo' => ['sometimes', 'date'],
        ];
    }

    protected function prepareForValidation(): void
    {
        if ($this->has('valor')) {
            $this->merge([
                'valor' => is_string($this->valor ?? null) ? Str::replace('.', '', $this->valor) : $this->valor,
            ]);
        }
    }
}
