<?php

namespace App\Http\Requests\Api\V1;

use App\Models\Group;
use App\Models\Program;
use App\Models\Schedule;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class MatriculaStoreRequest extends FormRequest
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
        $validPrograms = Program::where('active', true)->pluck('name')->toArray();
        $validSchedules = Schedule::where('active', true)->pluck('name')->toArray();
        $validGroups = Group::where('active', true)->pluck('name')->toArray();

        return [
            'nombre_completo' => ['required', 'string', 'max:255'],
            'numero_documento' => ['required', 'string', 'max:255', 'unique:matriculas,numero_documento'],
            'tipo_documento' => ['required', 'in:CC,TI,PPT'],
            'departamento' => ['nullable', 'string', 'max:255'],
            'estado_civil' => ['nullable', 'string', 'max:255'],
            'ocupacion' => ['nullable', 'string', 'max:255'],
            'nivel_formacion' => ['nullable', 'string', 'max:255'],
            'tiene_discapacidad' => ['nullable', 'in:No,Sí,Prefiero no decir'],
            'programa' => ['required', 'string', 'max:255', Rule::in($validPrograms)],
            'sede' => ['required', 'in:Barrancabermeja,Aguachica,Virtual'],
            'estado_estudiante' => ['required', 'in:Activo,Inactivo,Por Certificar,Certificado,Retirado,Suspendido,Todos'],
            'horario' => ['required', 'string', 'max:255', Rule::in($validSchedules)],
            'talla_uniforme' => ['nullable', 'in:XS,S,M,L,XL,XXL,XXXL'],
            'semestre_actual' => ['required', 'in:I,II,Ninguno (curso)'],
            'anio' => ['required', 'string', 'max:255'],
            'numero_grupo' => ['required', 'string', 'max:255', Rule::in($validGroups)],
            'contraseña_plataforma' => ['nullable', 'string', 'max:255'],
            'tipo_discapacidad' => ['required_if:tiene_discapacidad,Sí', 'nullable', 'string', 'max:255'],
        ];
    }

    /**
     * @return array<string, string>
     */
    public function messages(): array
    {
        return [
            'nombre_completo.required' => 'El nombre completo es obligatorio.',
            'numero_documento.required' => 'El número de documento es obligatorio.',
            'numero_documento.unique' => 'Este número de documento ya está registrado.',
            'tipo_documento.required' => 'El tipo de documento es obligatorio.',
            'tipo_documento.in' => 'El tipo de documento debe ser CC, TI o PPT.',
            'tiene_discapacidad.in' => 'La opción de discapacidad no es válida.',
            'tipo_discapacidad.required_if' => 'Debe indicar el tipo de discapacidad.',
            'programa.required' => 'El programa es obligatorio.',
            'programa.in' => 'El programa seleccionado no es válido o no está activo.',
            'sede.required' => 'La sede es obligatoria.',
            'sede.in' => 'La sede no es válida.',
            'estado_estudiante.required' => 'El estado del estudiante es obligatorio.',
            'estado_estudiante.in' => 'El estado del estudiante no es válido.',
            'horario.required' => 'El horario es obligatorio.',
            'horario.in' => 'El horario seleccionado no es válido o no está activo.',
            'talla_uniforme.in' => 'La talla del uniforme no es válida.',
            'semestre_actual.required' => 'El semestre actual es obligatorio.',
            'semestre_actual.in' => 'El semestre actual debe ser I, II o Ninguno (curso).',
            'anio.required' => 'El año es obligatorio.',
            'numero_grupo.required' => 'El número de grupo es obligatorio.',
            'numero_grupo.in' => 'El grupo seleccionado no es válido o no está activo.',
        ];
    }
}
