<?php

namespace App\Http\Middleware;

use App\Support\ApiResponse;
use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class CheckPermission
{
    /**
     * Handle an incoming request.
     *
     * @param  \Closure(\Illuminate\Http\Request): (\Symfony\Component\HttpFoundation\Response)  $next
     * @param  string  $permission  El slug del permiso requerido
     */
    public function handle(Request $request, Closure $next, string $permission): Response
    {
        if (!auth()->check()) {
            // SIEMPRE devolver JSON para /api/*, sin depender de expectsJson()
            // (descargas PDF/XLSX pueden tener Accept: */* pero igual deben devolver 401 JSON, no 302)
            if ($request->is('api/*')) {
                return ApiResponse::error('UNAUTHENTICATED', 'No autenticado.', null, 401);
            }
            if ($request->expectsJson()) {
                return ApiResponse::error('UNAUTHENTICATED', 'No autenticado.', null, 401);
            }
            return redirect()->route('login');
        }

        if (!auth()->user()->hasPermission($permission)) {
            // SIEMPRE devolver JSON para /api/*, sin depender de expectsJson()
            if ($request->is('api/*')) {
                return ApiResponse::error('FORBIDDEN', 'No tienes permiso para acceder a esta sección.', null, 403);
            }
            if ($request->expectsJson()) {
                return ApiResponse::error('FORBIDDEN', 'No tienes permiso para acceder a esta sección.', null, 403);
            }
            // Si el usuario es contador y está intentando acceder a /home, redirigir a contabilidad
            if (auth()->user()->hasRole('contador') && $request->is('home')) {
                return redirect()->route('accounting.index');
            }
            abort(403, 'No tienes permiso para acceder a esta sección.');
        }

        return $next($request);
    }
}
