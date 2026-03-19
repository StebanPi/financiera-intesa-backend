<?php

namespace App\Http\Requests\Api\V1;

use App\Models\Group;
use App\Models\Program;
use App\Models\Schedule;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class MatriculaUpdateRequest extends FormRequest
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
        $cod = $this->route('cod_alumno');
        $validPrograms = Program::where('active', true)->pluck('name')->toArray();
        $validSchedules = Schedule::where('active', true)->pluck('name')->toArray();
        $validGroups = Group::where('active', true)->pluck('name')->toArray();

        return [
            'nombre_completo' => ['sometimes', 'string', 'max:255'],
            'numero_documento' => ['sometimes', 'string', 'max:255', Rule::unique('matriculas', 'numero_documento')->ignore($cod, 'cod_alumno')->where('sede', $this->sede)],
            'lugar_expedicion_documento' => ['nullable', 'string', 'max:255'],
            'tipo_documento' => ['sometimes', 'in:CC,TI,PPT'],
            'fecha_nacimiento' => ['sometimes', 'nullable', 'date'],
            'direccion_barrio' => ['nullable', 'string', 'max:255'],
            'ciudad_residencia' => ['nullable', 'string', 'max:255'],
            'departamento' => ['nullable', 'string', 'max:255'],
            'correo_gmail' => ['nullable', 'email', 'max:255'],
            'telefono_personal' => ['nullable', 'string', 'max:255'],
            'telefono_emergencia' => ['nullable', 'string', 'max:255'],
            'estado_civil' => ['nullable', 'string', 'max:255'],
            'estrato' => ['nullable', 'string', 'max:255'],
            'nivel_sisben' => ['nullable', 'string', 'max:255'],
            'eps' => ['nullable', 'string', 'max:255'],
            'grupo_sanguineo' => ['nullable', 'string', 'max:255'],
            'nivel_formacion' => ['nullable', 'string', 'max:255'],
            'ocupacion' => ['nullable', 'string', 'max:255'],
            'tiene_discapacidad' => ['nullable', 'in:No,Sí,Prefiero no decir'],
            'tipo_discapacidad' => ['nullable', 'string', 'max:255'],
            'discapacidad_descripcion' => ['nullable', 'string', 'max:1000'],
            'programa' => ['sometimes', 'string', 'max:255', Rule::in($validPrograms)],
            'sede' => ['sometimes', 'in:Barrancabermeja,Aguachica'],
            'estado_estudiante' => ['sometimes', 'in:Activo,Inactivo,Por Certificar,Certificado,Retirado,Suspendido,Todos'],
            'horario' => ['sometimes', 'string', 'max:255', Rule::in($validSchedules)],
            'talla_uniforme' => ['nullable', 'in:XS,S,M,L,XL,XXL,XXXL'],
            'semestre_actual' => ['sometimes', 'in:I,II,Ninguno (curso)'],
            'anio' => ['sometimes', 'string', 'max:255'],
            'numero_grupo' => ['sometimes', 'string', 'max:255', Rule::in($validGroups)],
            'contraseña_plataforma' => ['nullable', 'string', 'max:255'],
            'modalidad' => ['sometimes', 'in:presencial,virtual'],
            'observaciones' => ['nullable', 'string'],
            'fecha_matricula' => ['nullable', 'date'],
            'fecha_inicio' => ['nullable', 'date'],
        ];
    }
}
