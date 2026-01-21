<?php

namespace App\Http\Resources\V1;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;
use Illuminate\Support\Facades\Storage;

class MatriculaResource extends JsonResource
{
    /**
     * @return array<string, mixed>
     */
    public function toArray(Request $request): array
    {
        $photoUrl = null;
        if ($this->photo_path && Storage::disk('public')->exists($this->photo_path)) {
            $photoUrl = Storage::url($this->photo_path);
        }

        return [
            'id' => $this->id,
            'cod_alumno' => $this->cod_alumno,
            'photo_path' => $this->photo_path,
            'photo_url' => $photoUrl,
            'nombre_completo' => $this->nombre_completo,
            'numero_documento' => $this->numero_documento,
            'lugar_expedicion_documento' => $this->lugar_expedicion_documento,
            'tipo_documento' => $this->tipo_documento,
            'fecha_nacimiento' => $this->fecha_nacimiento?->format('Y-m-d'),
            'direccion_barrio' => $this->direccion_barrio,
            'ciudad_residencia' => $this->ciudad_residencia,
            'departamento' => $this->departamento,
            'correo_gmail' => $this->correo_gmail,
            'telefono_personal' => $this->telefono_personal,
            'telefono_emergencia' => $this->telefono_emergencia,
            'estado_civil' => $this->estado_civil,
            'estrato' => $this->estrato,
            'nivel_sisben' => $this->nivel_sisben,
            'eps' => $this->eps,
            'grupo_sanguineo' => $this->grupo_sanguineo,
            'nivel_formacion' => $this->nivel_formacion,
            'ocupacion' => $this->ocupacion,
            'tiene_discapacidad' => $this->tiene_discapacidad,
            'tipo_discapacidad' => $this->tipo_discapacidad,
            'discapacidad_descripcion' => $this->discapacidad_descripcion,
            'programa' => $this->programa,
            'horario' => $this->horario,
            'sede' => $this->sede,
            'estado_estudiante' => $this->estado_estudiante,
            'contraseña_plataforma' => $this->contraseña_plataforma,
            'talla_uniforme' => $this->talla_uniforme,
            'semestre_actual' => $this->semestre_actual,
            'anio' => $this->anio,
            'numero_grupo' => $this->numero_grupo,
            'created_at' => $this->created_at?->toIso8601String(),
            'updated_at' => $this->updated_at?->toIso8601String(),
        ];
    }
}
