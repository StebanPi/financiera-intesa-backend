<?php

namespace App\Http\Middleware;

use Illuminate\Auth\Middleware\Authenticate as Middleware;
use Illuminate\Http\Request;

class Authenticate extends Middleware
{
    /**
     * Get the path the user should be redirected to when they are not authenticated.
     * 
     * En /api/* nunca debe haber redirects, incluso si no tiene Accept: application/json
     * (por ejemplo descargas PDF/XLSX con Accept: * / * deben devolver 401 JSON, no 302).
     */
    protected function redirectTo(Request $request): ?string
    {
        // SIEMPRE devolver null para /api/*, sin depender de expectsJson()
        if ($request->is('api/*')) {
            return null;
        }

        // Para web, verificar expectsJson para peticiones AJAX
        if ($request->expectsJson()) {
            return null;
        }

        return route('login');
    }
}
