<?php

namespace App\Support;

use Illuminate\Http\JsonResponse;

class ApiResponse
{
    /**
     * Respuesta de éxito estándar.
     *
     * @param  mixed  $data
     * @param  string|null  $message
     * @param  array<string, mixed>|null  $meta
     * @param  int  $status
     */
    public static function success($data = null, ?string $message = null, ?array $meta = null, int $status = 200): JsonResponse
    {
        $body = ['data' => $data];
        if ($message !== null) {
            $body['message'] = $message;
        }
        if ($meta !== null && $meta !== []) {
            $body['meta'] = $meta;
        }

        return response()->json($body, $status);
    }

    /**
     * Respuesta de error estándar.
     *
     * @param  string  $code  Código: VALIDATION_ERROR, UNAUTHENTICATED, FORBIDDEN, NOT_FOUND, TOO_MANY_REQUESTS, SERVER_ERROR, etc.
     * @param  string  $message
     * @param  array<string, mixed>|null  $details  Errores de validación u otros detalles
     * @param  int  $status
     * @param  array<string, mixed>  $extra  Claves adicionales en el objeto error (ej. trace_id)
     */
    public static function error(string $code, string $message, ?array $details = null, int $status = 400, array $extra = []): JsonResponse
    {
        $error = array_merge([
            'code' => $code,
            'message' => $message,
            'details' => $details,
        ], $extra);

        return response()->json(['error' => $error], $status);
    }

    /**
     * Mapeo de códigos HTTP a códigos de error internos.
     */
    public static function codeFromStatus(int $status): string
    {
        return match (true) {
            $status === 401 => 'UNAUTHENTICATED',
            $status === 403 => 'FORBIDDEN',
            $status === 404 => 'NOT_FOUND',
            $status === 429 => 'TOO_MANY_REQUESTS',
            $status >= 500 => 'SERVER_ERROR',
            $status >= 400 => 'BAD_REQUEST',
            default => 'ERROR',
        };
    }
}
