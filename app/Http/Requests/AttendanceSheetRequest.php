<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class AttendanceSheetRequest extends FormRequest
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
            'programa_id' => 'required|exists:programs,id',
            'horario_id' => 'required|exists:schedules,id',
            'grupo_id' => 'required|exists:groups,id',
            'docente_id' => 'required|exists:teachers,id',
            'modulo_id' => 'required|exists:modules,id',
            'fecha_inicio' => 'required|date',
            'fecha_final' => 'required|date|after_or_equal:fecha_inicio',
            'fecha_clase' => 'required|date',
        ];
    }

    /**
     * Get custom messages for validator errors.
     *
     * @return array
     */
    public function messages(): array
    {
        return [
            'programa_id.required' => 'El programa es obligatorio.',
            'programa_id.exists' => 'El programa seleccionado no es válido.',
            'horario_id.required' => 'El horario es obligatorio.',
            'horario_id.exists' => 'El horario seleccionado no es válido.',
            'grupo_id.required' => 'El grupo es obligatorio.',
            'grupo_id.exists' => 'El grupo seleccionado no es válido.',
            'docente_id.required' => 'El docente es obligatorio.',
            'docente_id.exists' => 'El docente seleccionado no es válido.',
            'modulo_id.required' => 'El módulo es obligatorio.',
            'modulo_id.exists' => 'El módulo seleccionado no es válido.',
            'fecha_inicio.required' => 'La fecha de inicio es obligatoria.',
            'fecha_inicio.date' => 'La fecha de inicio debe ser una fecha válida.',
            'fecha_final.required' => 'La fecha final es obligatoria.',
            'fecha_final.date' => 'La fecha final debe ser una fecha válida.',
            'fecha_final.after_or_equal' => 'La fecha final debe ser igual o posterior a la fecha de inicio.',
            'fecha_clase.required' => 'La fecha de clase es obligatoria.',
            'fecha_clase.date' => 'La fecha de clase debe ser una fecha válida.',
        ];
    }

    /**
     * Configure the validator instance.
     *
     * @param  \Illuminate\Validation\Validator  $validator
     * @return void
     */
    public function withValidator($validator)
    {
        $validator->after(function ($validator) {
            if ($this->fecha_inicio && $this->fecha_final && $this->fecha_clase) {
                $fechaInicio = \Carbon\Carbon::parse($this->fecha_inicio);
                $fechaFinal = \Carbon\Carbon::parse($this->fecha_final);
                $fechaClase = \Carbon\Carbon::parse($this->fecha_clase);

                if ($fechaClase->lt($fechaInicio) || $fechaClase->gt($fechaFinal)) {
                    $validator->errors()->add(
                        'fecha_clase',
                        'La fecha de clase debe estar dentro del rango de fechas de inicio y final.'
                    );
                }
            }
        });
    }
}
