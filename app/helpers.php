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
