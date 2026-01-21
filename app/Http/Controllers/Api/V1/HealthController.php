<?php

namespace App\Http\Controllers\Api\V1;

use App\Http\Controllers\Controller;
use App\Support\ApiResponse;
use Illuminate\Http\JsonResponse;
use OpenApi\Attributes as OA;

class HealthController extends Controller
{
    /**
     * GET /api/v1/health — Endpoint para infraestructura (load balancer, k8s, monitoreo).
     * Sin autenticación. JSON: status, server_time, app_env.
     */
    #[
        OA\Get(
            path: '/api/v1/health',
            summary: 'Health check endpoint',
            description: 'Endpoint para verificar el estado de la API. Usado por infraestructura (load balancer, k8s, monitoreo). No requiere autenticación.',
            tags: ['Health'],
            security: [],
            responses: [
                new OA\Response(
                    response: 200,
                    description: 'Health check exitoso',
                    content: new OA\JsonContent(
                        properties: [
                            new OA\Property(property: 'data', type: 'object', properties: [
                                new OA\Property(property: 'status', type: 'string', example: 'ok'),
                                new OA\Property(property: 'server_time', type: 'string', format: 'date-time', example: '2024-01-15T10:30:00+00:00'),
                                new OA\Property(property: 'app_env', type: 'string', example: 'local'),
                            ]),
                            new OA\Property(property: 'message', type: 'string', example: 'OK'),
                        ]
                    )
                ),
            ]
        )
    ]
    public function index(): JsonResponse
    {
        return ApiResponse::success([
            'status' => 'ok',
            'server_time' => now()->toIso8601String(),
            'app_env' => config('app.env'),
        ], 'OK');
    }
}
