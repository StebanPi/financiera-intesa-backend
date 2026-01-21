<?php

namespace App\Http\Controllers\Api\V1;

use App\Http\Controllers\Controller;
use App\Support\ApiResponse;
use Illuminate\Http\JsonResponse;
use OpenApi\Attributes as OA;

class HomeController extends Controller
{
    /**
     * GET /api/v1/home — Dashboard / Inicio.
     * JSON: message, server_time. No depende de Blade.
     */
    #[
        OA\Get(
            path: '/api/v1/home',
            summary: 'Home endpoint',
            description: 'Endpoint de inicio/dashboard. Devuelve un mensaje de bienvenida y la hora del servidor.',
            tags: ['Home'],
            security: [['bearerAuth' => []]],
            responses: [
                new OA\Response(
                    response: 200,
                    description: 'Respuesta exitosa',
                    content: new OA\JsonContent(
                        properties: [
                            new OA\Property(property: 'data', type: 'object', properties: [
                                new OA\Property(property: 'message', type: 'string', example: 'Bienvenido'),
                                new OA\Property(property: 'server_time', type: 'string', format: 'date-time', example: '2024-01-15T10:30:00+00:00'),
                            ]),
                        ]
                    )
                ),
                new OA\Response(
                    response: 401,
                    description: 'No autenticado',
                    content: new OA\JsonContent(ref: '#/components/schemas/ErrorResponse')
                ),
            ]
        )
    ]
    public function index(): JsonResponse
    {
        return ApiResponse::success([
            'message' => 'Bienvenido',
            'server_time' => now()->toIso8601String(),
        ]);
    }
}
