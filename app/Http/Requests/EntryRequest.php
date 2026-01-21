<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Support\Str;

class EntryRequest extends FormRequest
{
    /**
     * Determine if the user is authorized to make this request.
     */
    public function authorize(): bool
    {
        return true;
    }

    /**
     * Get the validation rules that apply to the request.
     *
     * @return array<string, \Illuminate\Contracts\Validation\ValidationRule|array<mixed>|string>
     */
    public function rules(): array
    {
        return [
            'id_cost' => 'required',
            'concepto' => 'required',
            'descripcion' => 'required',
            'fecha_recibo' => 'required',
            'valor' => 'required',
            'elaborado_por' => 'required|exists:elaborados,id',
            'debe' => 'required|exists:debes,id',
            'haber' => 'required|exists:habers,id',
            'forma' => 'nullable|in:Efectivo,Bancos,Consignación'
        ];
    }

    protected function prepareForValidation(): void
    {
        $this->merge([
            'valor' => Str::replace('.','',$this->valor),
            'forma' => $this->forma ?? 'Efectivo' // Valor por defecto si no viene
        ]);
    }
}
