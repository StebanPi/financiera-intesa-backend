<?php

namespace App\Http\Controllers\Api\V1;

use App\Http\Controllers\Controller;
use App\Http\Requests\Api\V1\ConsecutiveStoreRequest;
use App\Http\Requests\Api\V1\ConsecutiveUpdateRequest;
use App\Http\Resources\V1\ConsecutiveResource;
use App\Models\consecutive;
use App\Support\ApiResponse;
use Illuminate\Http\JsonResponse;
use OpenApi\Attributes as OA;

class ConsecutiveController extends Controller
{
    /**
     * GET /consecutives — listar todos (suelen ser 2: entry, discharge).
     */
    #[
        OA\Get(
            path: '/api/v1/consecutives',
            summary: 'Listar consecutivos',
            description: 'Lista todos los consecutivos (suelen ser 2: entry, discharge).',
            tags: ['Consecutives'],
            security: [['bearerAuth' => []]],
            responses: [
                new OA\Response(response: 200, description: 'Lista de consecutivos', content: new OA\JsonContent(ref: '#/components/schemas/SuccessResponse')),
                new OA\Response(response: 401, description: 'No autenticado', content: new OA\JsonContent(ref: '#/components/schemas/ErrorResponse')),
            ]
        )
    ]
    public function index(): JsonResponse
    {
        $items = consecutive::orderBy('type')->get();

        return ApiResponse::success(ConsecutiveResource::collection($items)->resolve());
    }

    /**
     * POST /consecutives — crear (solo si el type no existe).
     */
    #[
        OA\Post(
            path: '/api/v1/consecutives',
            summary: 'Crear consecutivo',
            description: 'Crea un nuevo consecutivo. Solo si el type no existe.',
            tags: ['Consecutives'],
            security: [['bearerAuth' => []]],
            requestBody: new OA\RequestBody(
                required: true,
                content: new OA\JsonContent(
                    required: ['type', 'num_start'],
                    properties: [
                        new OA\Property(property: 'type', type: 'string', example: 'entry', description: 'Tipo de consecutivo (entry, discharge)'),
                        new OA\Property(property: 'num_start', type: 'integer', example: 1, description: 'Número inicial'),
                    ]
                )
            ),
            responses: [
                new OA\Response(response: 201, description: 'Consecutivo creado exitosamente', content: new OA\JsonContent(ref: '#/components/schemas/SuccessResponse')),
                new OA\Response(response: 401, description: 'No autenticado', content: new OA\JsonContent(ref: '#/components/schemas/ErrorResponse')),
                new OA\Response(response: 422, description: 'Errores de validación', content: new OA\JsonContent(ref: '#/components/schemas/ValidationErrorResponse')),
            ]
        )
    ]
    public function store(ConsecutiveStoreRequest $request): JsonResponse
    {
        $con = consecutive::create([
            'type' => $request->type,
            'num_start' => $request->num_start,
            'num_current' => $request->num_start,
        ]);

        return ApiResponse::success(new ConsecutiveResource($con), 'Consecutivo creado.', null, 201);
    }

    /**
     * GET /consecutives/{id}
     */
    #[
        OA\Get(
            path: '/api/v1/consecutives/{id}',
            summary: 'Obtener consecutivo',
            description: 'Obtiene los datos de un consecutivo específico.',
            tags: ['Consecutives'],
            security: [['bearerAuth' => []]],
            parameters: [
                new OA\Parameter(name: 'id', in: 'path', required: true, description: 'ID del consecutivo', schema: new OA\Schema(type: 'integer')),
            ],
            responses: [
                new OA\Response(response: 200, description: 'Datos del consecutivo', content: new OA\JsonContent(ref: '#/components/schemas/SuccessResponse')),
                new OA\Response(response: 401, description: 'No autenticado', content: new OA\JsonContent(ref: '#/components/schemas/ErrorResponse')),
                new OA\Response(response: 404, description: 'Consecutivo no encontrado', content: new OA\JsonContent(ref: '#/components/schemas/ErrorResponse')),
            ]
        )
    ]
    public function show(int $id): JsonResponse
    {
        $con = consecutive::findOrFail($id);

        return ApiResponse::success(new ConsecutiveResource($con));
    }

    /**
     * PUT/PATCH /consecutives/{id}
     */
    #[
        OA\Put(
            path: '/api/v1/consecutives/{id}',
            summary: 'Actualizar consecutivo',
            description: 'Actualiza los datos de un consecutivo existente.',
            tags: ['Consecutives'],
            security: [['bearerAuth' => []]],
            parameters: [
                new OA\Parameter(name: 'id', in: 'path', required: true, description: 'ID del consecutivo', schema: new OA\Schema(type: 'integer')),
            ],
            requestBody: new OA\RequestBody(
                required: false,
                content: new OA\JsonContent(
                    properties: [
                        new OA\Property(property: 'num_start', type: 'integer', nullable: true),
                        new OA\Property(property: 'num_current', type: 'integer', nullable: true),
                    ]
                )
            ),
            responses: [
                new OA\Response(response: 200, description: 'Consecutivo actualizado exitosamente', content: new OA\JsonContent(ref: '#/components/schemas/SuccessResponse')),
                new OA\Response(response: 401, description: 'No autenticado', content: new OA\JsonContent(ref: '#/components/schemas/ErrorResponse')),
                new OA\Response(response: 404, description: 'Consecutivo no encontrado', content: new OA\JsonContent(ref: '#/components/schemas/ErrorResponse')),
                new OA\Response(response: 422, description: 'Errores de validación', content: new OA\JsonContent(ref: '#/components/schemas/ValidationErrorResponse')),
            ]
        )
    ]
    public function update(ConsecutiveUpdateRequest $request, int $id): JsonResponse
    {
        $con = consecutive::findOrFail($id);
        $data = $request->validated();
        
        if (isset($data['num_start'])) {
            $data['num_current'] = $data['num_start'];
        }

        $con->update($data);

        return ApiResponse::success(new ConsecutiveResource($con->fresh()), 'Consecutivo actualizado.', null, 200);
    }

    /**
     * GET /consecutives/type/{type} — buscar por tipo (entry/discharge).
     */
    #[
        OA\Get(
            path: '/api/v1/consecutives/type/{type}',
            summary: 'Obtener consecutivo por tipo',
            description: 'Obtiene los datos de un consecutivo filtrando por su tipo (entry o discharge).',
            tags: ['Consecutives'],
            security: [['bearerAuth' => []]],
            parameters: [
                new OA\Parameter(name: 'type', in: 'path', required: true, description: 'Tipo de consecutivo', schema: new OA\Schema(type: 'string', enum: ['entry', 'discharge'])),
            ],
            responses: [
                new OA\Response(response: 200, description: 'Datos del consecutivo', content: new OA\JsonContent(ref: '#/components/schemas/SuccessResponse')),
                new OA\Response(response: 404, description: 'Consecutivo no encontrado', content: new OA\JsonContent(ref: '#/components/schemas/ErrorResponse')),
            ]
        )
    ]
    public function showByType(string $type): JsonResponse
    {
        $con = consecutive::where('type', $type)->firstOrFail();

        return ApiResponse::success(new ConsecutiveResource($con));
    }
}
