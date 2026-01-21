<?php

namespace App\Http\Requests\Api\V1;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Contracts\Validation\Validator;

class AttendanceSheetGenerateRequest extends FormRequest
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
            'program_id' => 'required|exists:programs,id',
            'schedule_id' => 'required|exists:schedules,id',
            'group_id' => 'required|exists:groups,id',
            'teacher_id' => 'required|exists:teachers,id',
            'module_id' => 'required|exists:modules,id',
            'fecha_inicio' => 'required|date',
            'fecha_final' => 'required|date|after_or_equal:fecha_inicio',
            'fecha_clase' => 'required|date',
        ];
    }

    /**
     * @return array<string, string>
     */
    public function messages(): array
    {
        return [
            'program_id.required' => 'El programa es obligatorio.',
            'program_id.exists' => 'El programa seleccionado no es válido.',
            'schedule_id.required' => 'El horario es obligatorio.',
            'schedule_id.exists' => 'El horario seleccionado no es válido.',
            'group_id.required' => 'El grupo es obligatorio.',
            'group_id.exists' => 'El grupo seleccionado no es válido.',
            'teacher_id.required' => 'El docente es obligatorio.',
            'teacher_id.exists' => 'El docente seleccionado no es válido.',
            'module_id.required' => 'El módulo es obligatorio.',
            'module_id.exists' => 'El módulo seleccionado no es válido.',
            'fecha_inicio.required' => 'La fecha de inicio es obligatoria.',
            'fecha_inicio.date' => 'La fecha de inicio debe ser una fecha válida.',
            'fecha_final.required' => 'La fecha final es obligatoria.',
            'fecha_final.date' => 'La fecha final debe ser una fecha válida.',
            'fecha_final.after_or_equal' => 'La fecha final debe ser igual o posterior a la fecha de inicio.',
            'fecha_clase.required' => 'La fecha de clase es obligatoria.',
            'fecha_clase.date' => 'La fecha de clase debe ser una fecha válida.',
        ];
    }

    public function withValidator(Validator $validator): void
    {
        $validator->after(function (Validator $validator) {
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
