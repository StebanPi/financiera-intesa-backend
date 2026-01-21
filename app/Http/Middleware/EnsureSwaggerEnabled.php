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
        // config('app.swagger_enabled') lee de config/app.php que usa env('SWAGGER_ENABLED').
        // Con config:cache en producción, env() solo se evalúa al hacer config:cache;
        // por eso usamos config() para que el valor cacheado se respete.
        if (!config('app.swagger_enabled', false)) {
            abort(404, 'Not Found');
        }

        return $next($request);
    }
}
