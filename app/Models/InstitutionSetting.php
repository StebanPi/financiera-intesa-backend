<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class InstitutionSetting extends Model
{
    protected $fillable = [
        'name',
        'logo_path',
        'institucion_subtitulo',
        'sede',
        'nit',
        'address',
        'phone',
        'telefono2',
        'telefono3',
        'email',
        'website',
        'footer_licencia_texto',
        'footer_ciudad',
        'footer_mostrar_ubicacion_fecha',
        'footer_firma',
    ];

    /**
     * Obtener o crear el registro único de configuración
     */
    public static function getSettings()
    {
        return static::firstOrCreate(
            ['id' => 1],
            [
                'name' => 'Nombre de la Institución',
                'logo_path' => null,
                'institucion_subtitulo' => null,
                'sede' => null,
                'nit' => '',
                'address' => '',
                'phone' => '',
                'telefono2' => null,
                'telefono3' => null,
                'email' => '',
                'website' => '',
                'footer_licencia_texto' => 'Licencia de Funcionamiento según Resolución No. 3021 del 15 de diciembre de 2015',
                'footer_ciudad' => 'Barrancabermeja - Santander',
                'footer_mostrar_ubicacion_fecha' => true,
                'footer_firma' => null,
            ]
        );
    }
}
