<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Str;
use Symfony\Component\HttpFoundation\Response;

class RequestIdMiddleware
{
    /**
     * Añade X-Request-Id a cada request/response y al contexto de logs.
     *
     * - Si llega header X-Request-Id, lo usa
     * - Si no llega, genera UUID
     * - Lo añade como attribute al request para uso en Handler/controladores
     * - Lo añade al header de respuesta
     * - Lo añade al contexto de logs con Log::withContext
     *
     * @param  \Closure(\Illuminate\Http\Request): (\Symfony\Component\HttpFoundation\Response)  $next
     */
    public function handle(Request $request, Closure $next): Response
    {
        // Obtener o generar request_id
        $requestId = $request->header('X-Request-Id') ?? Str::uuid()->toString();

        // Añadir como attribute al request para uso en Handler y controladores
        $request->attributes->set('request_id', $requestId);

        // Añadir al contexto de logs para que aparezca en todos los logs de esta request
        Log::withContext(['request_id' => $requestId]);

        // Procesar la request
        $response = $next($request);

        // Añadir el header X-Request-Id a la respuesta
        $response->headers->set('X-Request-Id', $requestId);

        return $response;
    }
}
