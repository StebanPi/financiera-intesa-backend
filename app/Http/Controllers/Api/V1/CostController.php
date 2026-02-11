<?php

namespace App\Http\Controllers\Api\V1;

use App\Http\Controllers\Controller;
use App\Http\Requests\Api\V1\CostStoreRequest;
use App\Http\Requests\Api\V1\CostUpdateRequest;
use App\Http\Resources\V1\CostResource;
use App\Services\CostService;
use App\Support\ApiResponse;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use OpenApi\Attributes as OA;

class CostController extends Controller
{
    public function __construct(
        private CostService $costService
    ) {}

    /**
     * GET /costs?cod_alumno=...&per_page=15
     */
    #[
        OA\Get(
            path: '/api/v1/costs',
            summary: 'Listar costos',
            description: 'Lista los costos con filtros opcionales y paginación.',
            tags: ['Costs'],
            security: [['bearerAuth' => []]],
            parameters: [
                new OA\Parameter(name: 'cod_alumno', in: 'query', required: false, description: 'Código del alumno', schema: new OA\Schema(type: 'string')),
                new OA\Parameter(name: 'per_page', in: 'query', required: false, description: 'Elementos por página', schema: new OA\Schema(type: 'integer', example: 15)),
            ],
            responses: [
                new OA\Response(response: 200, description: 'Lista de costos', content: new OA\JsonContent(ref: '#/components/schemas/SuccessResponse')),
                new OA\Response(response: 401, description: 'No autenticado', content: new OA\JsonContent(ref: '#/components/schemas/ErrorResponse')),
            ]
        )
    ]
    public function index(Request $request): JsonResponse
    {
        $paginator = $this->costService->list($request);

        return ApiResponse::success(
            CostResource::collection($paginator->items())->resolve(),
            null,
            [
                'current_page' => $paginator->currentPage(),
                'per_page' => $paginator->perPage(),
                'total' => $paginator->total(),
                'last_page' => $paginator->lastPage(),
            ],
            200
        );
    }

    /**
     * POST /costs
     */
    #[
        OA\Post(
            path: '/api/v1/costs',
            summary: 'Crear costo',
            description: 'Crea un nuevo costo para un estudiante.',
            tags: ['Costs'],
            security: [['bearerAuth' => []]],
            requestBody: new OA\RequestBody(
                required: true,
                content: new OA\JsonContent(
                    required: ['cod_alumno', 'semestres'],
                    properties: [
                        new OA\Property(property: 'cod_alumno', type: 'string', example: '12345678'),
                        new OA\Property(
                            property: 'semestres',
                            type: 'array',
                            items: new OA\Items(
                                required: ['numero_semestre', 'valor_semestre', 'descuento', 'periodo', 'numero_cuotas', 'fecha_pago'],
                                properties: [
                                    new OA\Property(property: 'numero_semestre', type: 'integer', example: 1),
                                    new OA\Property(property: 'valor_semestre', type: 'number', format: 'float', example: 1000000),
                                    new OA\Property(property: 'descuento', type: 'number', format: 'float', example: 0),
                                    new OA\Property(property: 'periodo', type: 'string', example: 'Mensual'),
                                    new OA\Property(property: 'numero_cuotas', type: 'integer', example: 6),
                                    new OA\Property(property: 'fecha_pago', type: 'string', format: 'date', example: '2024-01-15'),
                                ]
                            )
                        ),
                    ]
                )
            ),
            responses: [
                new OA\Response(response: 201, description: 'Costo creado exitosamente', content: new OA\JsonContent(ref: '#/components/schemas/SuccessResponse')),
                new OA\Response(response: 401, description: 'No autenticado', content: new OA\JsonContent(ref: '#/components/schemas/ErrorResponse')),
                new OA\Response(response: 422, description: 'Errores de validación', content: new OA\JsonContent(ref: '#/components/schemas/ValidationErrorResponse')),
            ]
        )
    ]
    public function store(Request $request): JsonResponse
    {
        $cod_alumno = $request->cod_alumno;
        $semestres = $request->input('semestres', []);

        if (empty($semestres)) {
            return ApiResponse::error('Debe enviar al menos un semestre.', 422);
        }

        $this->costService->syncStudentCosts($cod_alumno, $semestres);

        $costs = \App\Models\Cost::where('cod_alumno', $cod_alumno)->orderBy('numero_semestre')->get();

        return ApiResponse::success(CostResource::collection($costs)->resolve(), 'Costos sincronizados.', null, 200);
    }

    /**
     * GET /costs/{id}
     */
    #[
        OA\Get(
            path: '/api/v1/costs/{id}',
            summary: 'Obtener costo',
            description: 'Obtiene los datos de un costo específico.',
            tags: ['Costs'],
            security: [['bearerAuth' => []]],
            parameters: [
                new OA\Parameter(name: 'id', in: 'path', required: true, description: 'ID del costo', schema: new OA\Schema(type: 'integer')),
            ],
            responses: [
                new OA\Response(response: 200, description: 'Datos del costo', content: new OA\JsonContent(ref: '#/components/schemas/SuccessResponse')),
                new OA\Response(response: 401, description: 'No autenticado', content: new OA\JsonContent(ref: '#/components/schemas/ErrorResponse')),
                new OA\Response(response: 404, description: 'Costo no encontrado', content: new OA\JsonContent(ref: '#/components/schemas/ErrorResponse')),
            ]
        )
    ]
    public function show(int $id): JsonResponse
    {
        $cost = $this->costService->getById($id);

        return ApiResponse::success(new CostResource($cost));
    }

    /**
     * GET /costs/student/{cod_alumno}
     */
    #[
        OA\Get(
            path: '/api/v1/costs/student/{cod_alumno}',
            summary: 'Obtener costos de un estudiante',
            description: 'Obtiene todos los costos configurados para un estudiante específico, ordenados por semestre.',
            tags: ['Costs'],
            security: [['bearerAuth' => []]],
            parameters: [
                new OA\Parameter(name: 'cod_alumno', in: 'path', required: true, description: 'Código del alumno', schema: new OA\Schema(type: 'string')),
            ],
            responses: [
                new OA\Response(response: 200, description: 'Lista de costos del estudiante', content: new OA\JsonContent(ref: '#/components/schemas/SuccessResponse')),
                new OA\Response(response: 401, description: 'No autenticado', content: new OA\JsonContent(ref: '#/components/schemas/ErrorResponse')),
            ]
        )
    ]
    public function showByStudent(string $cod_alumno): JsonResponse
    {
        $costs = $this->costService->getByStudent($cod_alumno);

        return ApiResponse::success(CostResource::collection($costs)->resolve());
    }

    /**
     * PUT/PATCH /costs/{id}
     */
    #[
        OA\Put(
            path: '/api/v1/costs/{id}',
            summary: 'Actualizar costo',
            description: 'Actualiza los datos de un costo existente.',
            tags: ['Costs'],
            security: [['bearerAuth' => []]],
            parameters: [
                new OA\Parameter(name: 'id', in: 'path', required: true, description: 'ID del costo', schema: new OA\Schema(type: 'integer')),
            ],
            requestBody: new OA\RequestBody(
                required: false,
                content: new OA\JsonContent(
                    properties: [
                        new OA\Property(property: 'valor_semestre', type: 'number', format: 'float', nullable: true),
                        new OA\Property(property: 'numero_semestre', type: 'integer', nullable: true),
                        new OA\Property(property: 'descuento', type: 'number', format: 'float', nullable: true),
                        new OA\Property(property: 'periodo', type: 'string', nullable: true),
                        new OA\Property(property: 'numero_cuotas', type: 'integer', nullable: true),
                        new OA\Property(property: 'fecha_pago', type: 'string', format: 'date', nullable: true),
                    ]
                )
            ),
            responses: [
                new OA\Response(response: 200, description: 'Costo actualizado exitosamente', content: new OA\JsonContent(ref: '#/components/schemas/SuccessResponse')),
                new OA\Response(response: 401, description: 'No autenticado', content: new OA\JsonContent(ref: '#/components/schemas/ErrorResponse')),
                new OA\Response(response: 404, description: 'Costo no encontrado', content: new OA\JsonContent(ref: '#/components/schemas/ErrorResponse')),
                new OA\Response(response: 422, description: 'Errores de validación', content: new OA\JsonContent(ref: '#/components/schemas/ValidationErrorResponse')),
            ]
        )
    ]
    public function update(CostUpdateRequest $request, int $id): JsonResponse
    {
        $cost = $this->costService->update($id, $request->validated());

        return ApiResponse::success(new CostResource($cost), 'Costo actualizado.', null, 200);
    }

    /**
     * DELETE /costs/{id}
     */
    #[
        OA\Delete(
            path: '/api/v1/costs/{id}',
            summary: 'Eliminar costo',
            description: 'Elimina un costo existente.',
            tags: ['Costs'],
            security: [['bearerAuth' => []]],
            parameters: [
                new OA\Parameter(name: 'id', in: 'path', required: true, description: 'ID del costo', schema: new OA\Schema(type: 'integer')),
            ],
            responses: [
                new OA\Response(response: 200, description: 'Costo eliminado exitosamente', content: new OA\JsonContent(ref: '#/components/schemas/SuccessResponse')),
                new OA\Response(response: 401, description: 'No autenticado', content: new OA\JsonContent(ref: '#/components/schemas/ErrorResponse')),
                new OA\Response(response: 404, description: 'Costo no encontrado', content: new OA\JsonContent(ref: '#/components/schemas/ErrorResponse')),
            ]
        )
    ]
    public function destroy(int $id): JsonResponse
    {
        if (!auth()->user()->hasPermission('records.delete')) {
            return ApiResponse::error('No tienes permiso para eliminar registros financieros.', 403);
        }

        $this->costService->delete($id);

        return ApiResponse::success(null, 'Eliminado.', null, 200);
    }

    /**
     * DELETE /costs/student/{cod_alumno}
     */
    #[
        OA\Delete(
            path: '/api/v1/costs/student/{cod_alumno}',
            summary: 'Eliminar todos los costos de un estudiante',
            description: 'Elimina todos los costos, cuotas y abonos asociados a un estudiante (Hard Reset). Requiere permisos especiales.',
            tags: ['Costs'],
            security: [['bearerAuth' => []]],
            parameters: [
                new OA\Parameter(name: 'cod_alumno', in: 'path', required: true, description: 'Código del alumno', schema: new OA\Schema(type: 'string')),
            ],
            responses: [
                new OA\Response(response: 200, description: 'Costos eliminados exitosamente', content: new OA\JsonContent(ref: '#/components/schemas/SuccessResponse')),
                new OA\Response(response: 401, description: 'No autenticado', content: new OA\JsonContent(ref: '#/components/schemas/ErrorResponse')),
                new OA\Response(response: 403, description: 'No autorizado', content: new OA\JsonContent(ref: '#/components/schemas/ErrorResponse')),
            ]
        )
    ]
    public function destroyByStudent(string $cod_alumno): JsonResponse
    {
        if (!auth()->user()->hasPermission('records.delete')) {
            return ApiResponse::error('No tienes permiso para eliminar registros financieros.', 403);
        }

        $stats = $this->costService->deleteAllForStudent($cod_alumno);

        return ApiResponse::success($stats, 'Todos los costos del estudiante han sido eliminados.', null, 200);
    }
}
