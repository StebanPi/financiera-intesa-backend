<?php

namespace App\Exceptions;

use App\Support\ApiResponse;
use Illuminate\Auth\AuthenticationException;
use Illuminate\Auth\AuthorizationException;
use Illuminate\Foundation\Exceptions\Handler as ExceptionHandler;
use Illuminate\Http\Request;
use Illuminate\Support\Str;
use Illuminate\Validation\ValidationException;
use Symfony\Component\HttpKernel\Exception\HttpException;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;
use Symfony\Component\HttpKernel\Exception\TooManyRequestsHttpException;
use Throwable;

class Handler extends ExceptionHandler
{
    /**
     * A list of the exception types that are not reported.
     *
     * @var array<int, class-string<Throwable>>
     */
    protected $dontReport = [
        //
    ];

    /**
     * A list of the inputs that are never flashed for validation exceptions.
     *
     * @var array<int, string>
     */
    protected $dontFlash = [
        'current_password',
        'password',
        'password_confirmation',
    ];

    /**
     * Register the exception handling callbacks for the application.
     *
     * @return void
     */
    public function register()
    {
        $this->reportable(function (Throwable $e) {
            //
        });

        $this->renderable(function (Throwable $e, Request $request) {
            if (!$request->is('api/*') && !$request->expectsJson()) {
                return null;
            }

            if ($e instanceof ValidationException) {
                return ApiResponse::error(
                    'VALIDATION_ERROR',
                    $e->getMessage() ?: 'Los datos enviados no son válidos.',
                    $e->errors(),
                    422
                );
            }

            if ($e instanceof AuthenticationException) {
                return ApiResponse::error('UNAUTHENTICATED', 'No autenticado.', null, 401);
            }

            if ($e instanceof AuthorizationException) {
                return ApiResponse::error('FORBIDDEN', $e->getMessage() ?: 'No autorizado.', null, 403);
            }

            if ($e instanceof \Illuminate\Database\Eloquent\ModelNotFoundException) {
                return ApiResponse::error('NOT_FOUND', 'Recurso no encontrado.', null, 404);
            }

            if ($e instanceof NotFoundHttpException) {
                return ApiResponse::error('NOT_FOUND', $e->getMessage() ?: 'Recurso no encontrado.', null, 404);
            }

            if ($e instanceof TooManyRequestsHttpException) {
                return ApiResponse::error('TOO_MANY_REQUESTS', $e->getMessage() ?: 'Demasiadas peticiones.', null, 429);
            }

            if ($e instanceof HttpException) {
                $status = $e->getStatusCode();

                return ApiResponse::error(
                    ApiResponse::codeFromStatus($status),
                    $e->getMessage() ?: 'Error en la solicitud.',
                    null,
                    $status
                );
            }

            // Usar request_id del middleware RequestIdMiddleware si está disponible
            // Si no está (por algún motivo), generar uno como fallback
            $traceId = $request->attributes->get('request_id') ?? $request->header('X-Request-Id') ?? Str::uuid()->toString();

            return ApiResponse::error(
                'SERVER_ERROR',
                config('app.debug') ? $e->getMessage() : 'Error interno del servidor.',
                config('app.debug') ? ['exception' => get_class($e), 'file' => $e->getFile(), 'line' => $e->getLine()] : null,
                500,
                ['trace_id' => $traceId]
            );
        });
    }

    /**
     * Convertir AuthenticationException en 401 (por si no pasara por renderable en algunos flujos).
     */
    protected function unauthenticated($request, AuthenticationException $exception)
    {
        if ($request->is('api/*') || $request->expectsJson()) {
            return \App\Support\ApiResponse::error('UNAUTHENTICATED', 'No autenticado.', null, 401);
        }

        return redirect()->guest($exception->redirectTo($request) ?? route('login'));
    }
}
