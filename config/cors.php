<?php

/*
|--------------------------------------------------------------------------
| Cross-Origin Resource Sharing (CORS) Configuration
|--------------------------------------------------------------------------
|
| Configuración dinámica: los orígenes se leen SIEMPRE del .env para
| que nunca sea necesario modificar este archivo en producción.
|
| Variables de entorno (en orden de prioridad):
|   CORS_ALLOWED_ORIGINS  →  lista separada por comas
|   FRONTEND_URL          →  fallback si CORS_ALLOWED_ORIGINS no existe
|
| Ejemplo .env local:
|   CORS_ALLOWED_ORIGINS=http://localhost:3000,http://127.0.0.1:3000
|
| Ejemplo .env producción:
|   CORS_ALLOWED_ORIGINS=https://financiera.institutointesa.edu.co
|
*/

$allowedOrigins = (function (): array {
    // 1. Fuente principal: CORS_ALLOWED_ORIGINS (varios orígenes, coma)
    $raw = env('CORS_ALLOWED_ORIGINS', '');

    // 2. Fallback: FRONTEND_URL (un solo origen)
    if (empty($raw)) {
        $raw = env('FRONTEND_URL', 'http://localhost:3000');
    }

    // Limpiar, separar y filtrar vacíos
    $origins = array_values(array_filter(array_map('trim', explode(',', (string) $raw))));

    // Garantizar que siempre haya al menos un origen
    return $origins ?: ['http://localhost:3000'];
})();

return [

    /*
    |--------------------------------------------------------------------------
    | Rutas que aceptan CORS
    |--------------------------------------------------------------------------
    */
    'paths' => ['api/*', 'sanctum/csrf-cookie'],

    /*
    |--------------------------------------------------------------------------
    | Métodos HTTP permitidos
    |--------------------------------------------------------------------------
    */
    'allowed_methods' => ['*'],

    /*
    |--------------------------------------------------------------------------
    | Orígenes permitidos (dinámicos desde .env)
    |--------------------------------------------------------------------------
    */
    'allowed_origins' => $allowedOrigins,

    /*
    |--------------------------------------------------------------------------
    | Patrones de orígenes (regex) — vacío por defecto
    |--------------------------------------------------------------------------
    */
    'allowed_origins_patterns' => [],

    /*
    |--------------------------------------------------------------------------
    | Headers permitidos
    |--------------------------------------------------------------------------
    */
    'allowed_headers' => ['*'],

    /*
    |--------------------------------------------------------------------------
    | Headers expuestos al navegador
    |--------------------------------------------------------------------------
    | Content-Disposition: necesario para descargas de PDF/XLSX.
    */
    'exposed_headers' => ['Content-Disposition'],

    /*
    |--------------------------------------------------------------------------
    | Preflight cache (segundos)
    |--------------------------------------------------------------------------
    | 7200 = 2 horas. Reduce las peticiones OPTIONS repetitivas.
    */
    'max_age' => 7200,

    /*
    |--------------------------------------------------------------------------
    | Credenciales (cookies, Authorization headers)
    |--------------------------------------------------------------------------
    | true porque usamos tokens Bearer y Sanctum con withCredentials.
    */
    'supports_credentials' => true,

];
