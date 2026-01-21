<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Support\Str;

class CostRequest extends FormRequest
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
            'cod_alumno' => 'required',
            'valor_semestre' => 'required|min:0',
            'numero_semestre' => 'required|min:0',
            'valor_total_semestre' => 'nullable|min:0',
            'descuento' => 'required|min:0',
            'valor_neto' => 'nullable|min:0',
            'saldo_financiar' => 'nullable|min:0',
            'periodo' => 'required',
            'numero_cuotas' => 'required|min:0',
            'valor_cuotas' => 'nullable|min:0',
            'fecha_pago' => 'required|date'
        ];
    }

    protected function prepareForValidation(): void
    {
        // Limpiar puntos de los valores numéricos
        $valorSemestre = Str::replace('.','',$this->valor_semestre ?? '0');
        $numeroSemestre = $this->numero_semestre ?? 0;
        $descuento = Str::replace('.','',$this->descuento ?? '0');
        $numeroCuotas = $this->numero_cuotas ?? 0;
        
        // Calcular valor_total_semestre si no está presente o está vacío
        $valorTotalSemestre = Str::replace('.','',$this->valor_total_semestre ?? '');
        if (empty($valorTotalSemestre) && !empty($valorSemestre) && !empty($numeroSemestre)) {
            $valorTotalSemestre = (int)$valorSemestre * (int)$numeroSemestre;
        }
        
        // Calcular valor_neto y saldo_financiar si no están presentes o están vacíos
        $valorNeto = Str::replace('.','',$this->valor_neto ?? '');
        $saldoFinanciar = Str::replace('.','',$this->saldo_financiar ?? '');
        
        if (empty($valorNeto) && !empty($valorTotalSemestre)) {
            $valorNeto = max(0, (int)$valorTotalSemestre - (int)$descuento);
        }
        
        if (empty($saldoFinanciar) && !empty($valorNeto)) {
            $saldoFinanciar = $valorNeto;
        }
        
        // Calcular valor_cuotas si no está presente o está vacío
        $valorCuotas = Str::replace('.','',$this->valor_cuotas ?? '');
        if (empty($valorCuotas) && !empty($saldoFinanciar) && !empty($numeroCuotas) && (int)$numeroCuotas > 0) {
            $valorCuotas = (int)round((int)$saldoFinanciar / (int)$numeroCuotas);
        }
        
        $this->merge([
            'valor_semestre' => $valorSemestre,
            'valor_total_semestre' => $valorTotalSemestre ?: '0',
            'descuento' => $descuento,
            'valor_neto' => $valorNeto ?: '0',
            'saldo_financiar' => $saldoFinanciar ?: '0',
            'valor_cuotas' => $valorCuotas ?: '0',
        ]);
    }
}
