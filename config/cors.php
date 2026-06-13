<?php

/*
|--------------------------------------------------------------------------
| CORS allowed_origins (configurable por entorno)
|--------------------------------------------------------------------------
| Origen: FRONTEND_URLS (varios, coma) > CORS_ALLOWED_ORIGINS > FRONTEND_URL.
| Si FRONTEND_URL está definido, se incluye siempre en la lista.
| Evitar '*'; en local sin variables se usa http://localhost:3000.
*/
// $corsAllowedOrigins = (function () {
//     $fe = trim((string) (env('FRONTEND_URL') ?? ''));
//     $src = env('FRONTEND_URLS') ?: env('CORS_ALLOWED_ORIGINS') ?: ($fe !== '' ? $fe : 'http://localhost:3000');
//     $list = array_filter(array_map('trim', explode(',', (string) $src)));
//     if ($fe !== '' && ! in_array($fe, $list)) {
//         $list[] = $fe;
//     }

//     return $list ?: ['http://localhost:3000'];
// })();

return [

    /*
    |--------------------------------------------------------------------------
    | Cross-Origin Resource Sharing (CORS) Configuration
    |--------------------------------------------------------------------------
    |
    | CORS para /api/*: Postman, debug y posibles llamadas directas desde el
    | navegador. Con Next como BFF la mayoría de peticiones son server-to-server.
    | supports_credentials=false (no usamos cookies directas browser→Laravel).
    |
    */

    'paths' => ['api/*', 'sanctum/csrf-cookie'],

    'allowed_methods' => ['*'],

    'allowed_origins' => [
        'https://financiera.institutointesa.edu.co',
        'http://localhost:3000'
    ],

    'allowed_origins_patterns' => [],

    'allowed_headers' => ['*'],

    'exposed_headers' => ['Content-Disposition'],

    'max_age' => 0,

    'supports_credentials' => true,

];
