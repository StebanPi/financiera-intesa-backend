<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Matricula extends Model
{
    use HasFactory;

    protected $fillable = [
        'cod_alumno',
        'photo_path',
        'nombre_completo',
        'numero_documento',
        'lugar_expedicion_documento',
        'tipo_documento',
        'fecha_nacimiento',
        'direccion_barrio',
        'ciudad_residencia',
        'departamento',
        'correo_gmail',
        'telefono_personal',
        'telefono_emergencia',
        'estado_civil',
        'estrato',
        'nivel_sisben',
        'eps',
        'grupo_sanguineo',
        'nivel_formacion',
        'ocupacion',
        'tiene_discapacidad',
        'tipo_discapacidad',
        'discapacidad_descripcion',
        'programa',
        'horario',
        'sede',
        'estado_estudiante',
        'contraseña_plataforma',
        'talla_uniforme',
        'semestre_actual',
        'anio',
        'numero_grupo',
        'modalidad'
    ];

    /**
     * Get the attributes that should be cast.
     *
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'fecha_nacimiento' => 'date',
        ];
    }
}
