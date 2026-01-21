<?php

namespace App\Http\Requests\Api\V1;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Support\Str;
use Illuminate\Validation\Rule;

class CostStoreRequest extends FormRequest
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
            'cod_alumno' => ['required', 'string', 'max:255', 'exists:matriculas,cod_alumno', Rule::unique('costs', 'cod_alumno')],
            'valor_semestre' => ['required', 'numeric', 'min:0'],
            'numero_semestre' => ['required', 'integer', 'min:0'],
            'valor_total_semestre' => ['nullable', 'numeric', 'min:0'],
            'descuento' => ['required', 'numeric', 'min:0'],
            'valor_neto' => ['nullable', 'numeric', 'min:0'],
            'saldo_financiar' => ['nullable', 'numeric', 'min:0'],
            'periodo' => ['required', 'string', 'max:255'],
            'numero_cuotas' => ['required', 'integer', 'min:0'],
            'valor_cuotas' => ['nullable', 'numeric', 'min:0'],
            'fecha_pago' => ['required', 'date'],
            'detalles' => ['nullable', 'string'],
        ];
    }

    protected function prepareForValidation(): void
    {
        $valorSemestre = (string) Str::replace('.', '', $this->valor_semestre ?? '0');
        $numeroSemestre = (int) ($this->numero_semestre ?? 0);
        $descuento = (string) Str::replace('.', '', $this->descuento ?? '0');
        $numeroCuotas = (int) ($this->numero_cuotas ?? 0);

        $valorTotalSemestre = (string) Str::replace('.', '', $this->valor_total_semestre ?? '');
        if ($valorTotalSemestre === '' && $valorSemestre !== '' && $numeroSemestre > 0) {
            $valorTotalSemestre = (string) ((int) $valorSemestre * $numeroSemestre);
        }

        $valorNeto = (string) Str::replace('.', '', $this->valor_neto ?? '');
        $saldoFinanciar = (string) Str::replace('.', '', $this->saldo_financiar ?? '');
        if ($valorNeto === '' && $valorTotalSemestre !== '') {
            $valorNeto = (string) max(0, (int) $valorTotalSemestre - (int) $descuento);
        }
        if ($saldoFinanciar === '' && $valorNeto !== '') {
            $saldoFinanciar = $valorNeto;
        }

        $valorCuotas = (string) Str::replace('.', '', $this->valor_cuotas ?? '');
        if ($valorCuotas === '' && $saldoFinanciar !== '' && $numeroCuotas > 0) {
            $valorCuotas = (string) (int) round((int) $saldoFinanciar / $numeroCuotas);
        }

        $this->merge([
            'valor_semestre' => $valorSemestre ?: '0',
            'valor_total_semestre' => $valorTotalSemestre ?: '0',
            'descuento' => $descuento ?: '0',
            'valor_neto' => $valorNeto ?: '0',
            'saldo_financiar' => $saldoFinanciar ?: '0',
            'valor_cuotas' => $valorCuotas ?: '0',
        ]);
    }
}
