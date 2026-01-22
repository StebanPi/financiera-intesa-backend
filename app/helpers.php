<?php

if (!function_exists('asset_versioned')) {
    /**
     * Generate a versioned asset URL with timestamp to prevent caching.
     *
     * @param string $path
     * @return string
     */
    function asset_versioned($path)
    {
        $filePath = public_path($path);
        $version = file_exists($filePath) ? filemtime($filePath) : time();
        return asset($path) . '?v=' . $version;
    }
}

if (!function_exists('institution_settings')) {
    /**
     * Obtener configuración de la institución de forma reutilizable con cache.
     *
     * @return \App\Models\InstitutionSetting
     */
    function institution_settings()
    {
        return \Illuminate\Support\Facades\Cache::remember('institution_settings', 3600, function () {
            return \App\Models\InstitutionSetting::getSettings();
        });
    }
}

if (!function_exists('numero_a_romano')) {
    /**
     * Convertir un número a número romano.
     *
     * @param int $numero
     * @return string
     */
    function numero_a_romano($numero)
    {
        $numero = (int) $numero;
        if ($numero <= 0 || $numero > 3999) {
            return (string) $numero;
        }
        
        $valores = [
            1000 => 'M', 900 => 'CM', 500 => 'D', 400 => 'CD',
            100 => 'C', 90 => 'XC', 50 => 'L', 40 => 'XL',
            10 => 'X', 9 => 'IX', 5 => 'V', 4 => 'IV', 1 => 'I'
        ];
        
        $romano = '';
        foreach ($valores as $valor => $letra) {
            $cantidad = intval($numero / $valor);
            $romano .= str_repeat($letra, $cantidad);
            $numero = $numero % $valor;
        }
        
        return $romano;
    }
}
