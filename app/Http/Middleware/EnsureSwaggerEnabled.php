<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class EnsureSwaggerEnabled
{
    /**
     * Handle an incoming request.
     *
     * @param  \Closure(\Illuminate\Http\Request): (\Symfony\Component\HttpFoundation\Response)  $next
     */
    public function handle(Request $request, Closure $next): Response
    {
        // Verificar SWAGGER_ENABLED desde env (no usar config cache para esta variable)
        $swaggerEnabled = filter_var(env('SWAGGER_ENABLED', false), FILTER_VALIDATE_BOOLEAN);
        
        if (!$swaggerEnabled) {
            abort(404, 'Not Found');
        }

        return $next($request);
    }
}
