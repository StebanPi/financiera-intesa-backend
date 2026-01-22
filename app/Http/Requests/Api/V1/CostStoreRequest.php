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
            'cod_alumno' => ['required', 'string', 'max:255', 'exists:matriculas,cod_alumno'],
            'semestres' => ['required', 'array', 'min:1'],
            'semestres.*.numero_semestre' => ['required', 'integer', 'min:1'],
            'semestres.*.valor_semestre' => ['required', 'numeric', 'min:0'],
            'semestres.*.descuento' => ['required', 'numeric', 'min:0'],
            'semestres.*.periodo' => ['required', 'string', 'max:255'],
            'semestres.*.numero_cuotas' => ['required', 'integer', 'min:0'],
            'semestres.*.fecha_pago' => ['required', 'date'],
            'semestres.*.detalles' => ['nullable', 'string'],
        ];
    }

    protected function prepareForValidation(): void
    {
        $semestres = $this->semestres;
        if (is_array($semestres)) {
            foreach ($semestres as $key => $sem) {
                $valorSemestre = (string) Str::replace('.', '', $sem['valor_semestre'] ?? '0');
                $descuento = (string) Str::replace('.', '', $sem['descuento'] ?? '0');
                $numeroCuotas = (int) ($sem['numero_cuotas'] ?? 0);

                $valorNeto = (string) max(0, (int)$valorSemestre - (int)$descuento);
                $valorCuotas = $numeroCuotas > 0 ? (string)(int)round((int)$valorNeto / $numeroCuotas) : '0';

                $semestres[$key]['valor_semestre'] = $valorSemestre;
                $semestres[$key]['valor_total_semestre'] = $valorSemestre; // Ahora es igual al valor_semestre
                $semestres[$key]['descuento'] = $descuento;
                $semestres[$key]['valor_neto'] = $valorNeto;
                $semestres[$key]['saldo_financiar'] = $valorNeto;
                $semestres[$key]['valor_cuotas'] = $valorCuotas;
            }
            $this->merge(['semestres' => $semestres]);
        }
    }
}
