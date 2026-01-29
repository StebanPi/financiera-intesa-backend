<?php

namespace App\Http\Controllers\Api\V1;

use App\Http\Controllers\Controller;
use App\Http\Requests\Api\V1\DischargeStoreRequest;
use App\Http\Resources\V1\DischargeResource;
use App\Models\EgresoReceipt;
use App\Services\DischargeService;
use App\Support\ApiResponse;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use OpenApi\Attributes as OA;

class DischargeController extends Controller
{
    public function __construct(private DischargeService $dischargeService) {}

    #[
        OA\Get(
            path: '/api/v1/discharges',
            summary: 'Listar egresos',
            description: 'Lista los egresos con filtros opcionales y paginación.',
            tags: ['Discharges'],
            security: [['bearerAuth' => []]],
            parameters: [
                new OA\Parameter(name: 'proveedor_id', in: 'query', required: false, description: 'ID del proveedor', schema: new OA\Schema(type: 'integer')),
                new OA\Parameter(name: 'from', in: 'query', required: false, description: 'Fecha desde (YYYY-MM-DD)', schema: new OA\Schema(type: 'string', format: 'date')),
                new OA\Parameter(name: 'to', in: 'query', required: false, description: 'Fecha hasta (YYYY-MM-DD)', schema: new OA\Schema(type: 'string', format: 'date')),
                new OA\Parameter(name: 'per_page', in: 'query', required: false, description: 'Elementos por página', schema: new OA\Schema(type: 'integer', example: 15)),
            ],
            responses: [
                new OA\Response(response: 200, description: 'Lista de egresos', content: new OA\JsonContent(ref: '#/components/schemas/SuccessResponse')),
                new OA\Response(response: 401, description: 'No autenticado', content: new OA\JsonContent(ref: '#/components/schemas/ErrorResponse')),
            ]
        )
    ]
    public function index(Request $request): JsonResponse
    {
        $q = EgresoReceipt::query()->with(['provider', 'conceptoObject', 'elaboradoObject']);
        if ($request->filled('proveedor_id')) {
            $q->where('proveedor_id', $request->proveedor_id);
        }
        if ($request->filled('from')) {
            $q->whereDate('fecha_recibo', '>=', $request->from);
        }
        if ($request->filled('to')) {
            $q->whereDate('fecha_recibo', '<=', $request->to);
        }
        $perPage = min((int) $request->get('per_page', 15), 100);
        $paginator = $q->orderBy('fecha_recibo', 'desc')->paginate($perPage);
        return ApiResponse::success(
            DischargeResource::collection($paginator->items())->resolve(),
            null,
            ['current_page' => $paginator->currentPage(), 'per_page' => $paginator->perPage(), 'total' => $paginator->total(), 'last_page' => $paginator->lastPage()],
            200
        );
    }

    #[
        OA\Post(
            path: '/api/v1/discharges',
            summary: 'Crear egreso',
            description: 'Crea un nuevo egreso.',
            tags: ['Discharges'],
            security: [['bearerAuth' => []]],
            requestBody: new OA\RequestBody(
                required: true,
                content: new OA\JsonContent(
                    required: ['fecha_recibo', 'proveedor_id', 'forma', 'concepto', 'valor', 'elaborado_por'],
                    properties: [
                        new OA\Property(property: 'fecha_recibo', type: 'string', format: 'date', example: '2024-01-15'),
                        new OA\Property(property: 'proveedor_id', type: 'integer', example: 1),
                        new OA\Property(property: 'forma', type: 'string', enum: ['Efectivo', 'Bancos'], example: 'Efectivo'),
                        new OA\Property(property: 'concepto', type: 'integer', example: 1),
                        new OA\Property(property: 'descripcion', type: 'string', nullable: true),
                        new OA\Property(property: 'valor', type: 'number', format: 'float', example: 100000),
                        new OA\Property(property: 'elaborado_por', type: 'integer', example: 1),
                    ]
                )
            ),
            responses: [
                new OA\Response(response: 201, description: 'Egreso creado exitosamente', content: new OA\JsonContent(ref: '#/components/schemas/SuccessResponse')),
                new OA\Response(response: 401, description: 'No autenticado', content: new OA\JsonContent(ref: '#/components/schemas/ErrorResponse')),
                new OA\Response(response: 422, description: 'Errores de validación', content: new OA\JsonContent(ref: '#/components/schemas/ValidationErrorResponse')),
            ]
        )
    ]
    public function store(DischargeStoreRequest $request): JsonResponse
    {
        $r = $this->dischargeService->create($request->validated());
        return ApiResponse::success(new DischargeResource($r), 'Egreso creado.', null, 201);
    }

    #[
        OA\Get(
            path: '/api/v1/discharges/{id}',
            summary: 'Obtener egreso',
            description: 'Obtiene los datos de un egreso específico.',
            tags: ['Discharges'],
            security: [['bearerAuth' => []]],
            parameters: [
                new OA\Parameter(name: 'id', in: 'path', required: true, description: 'ID del egreso', schema: new OA\Schema(type: 'integer')),
            ],
            responses: [
                new OA\Response(response: 200, description: 'Datos del egreso', content: new OA\JsonContent(ref: '#/components/schemas/SuccessResponse')),
                new OA\Response(response: 401, description: 'No autenticado', content: new OA\JsonContent(ref: '#/components/schemas/ErrorResponse')),
                new OA\Response(response: 404, description: 'Egreso no encontrado', content: new OA\JsonContent(ref: '#/components/schemas/ErrorResponse')),
            ]
        )
    ]
    public function show(int $id): JsonResponse
    {
        $r = EgresoReceipt::with(['provider', 'conceptoObject', 'elaboradoObject'])->findOrFail($id);
        return ApiResponse::success(new DischargeResource($r));
    }

    #[
        OA\Put(
            path: '/api/v1/discharges/{id}',
            summary: 'Actualizar egreso',
            description: 'Actualiza un egreso existente.',
            tags: ['Discharges'],
            security: [['bearerAuth' => []]],
            parameters: [
                new OA\Parameter(name: 'id', in: 'path', required: true, description: 'ID del egreso', schema: new OA\Schema(type: 'integer')),
            ],
            requestBody: new OA\RequestBody(
                required: false,
                content: new OA\JsonContent(
                    properties: [
                        new OA\Property(property: 'fecha_recibo', type: 'string', format: 'date', example: '2024-01-15'),
                        new OA\Property(property: 'proveedor_id', type: 'integer', example: 1),
                        new OA\Property(property: 'forma', type: 'string', enum: ['Efectivo', 'Bancos'], example: 'Efectivo'),
                        new OA\Property(property: 'concepto', type: 'integer', example: 1),
                        new OA\Property(property: 'descripcion', type: 'string', nullable: true),
                        new OA\Property(property: 'valor', type: 'number', format: 'float', example: 100000),
                        new OA\Property(property: 'elaborado_por', type: 'integer', example: 1),
                    ]
                )
            ),
            responses: [
                new OA\Response(response: 200, description: 'Egreso actualizado exitosamente', content: new OA\JsonContent(ref: '#/components/schemas/SuccessResponse')),
                new OA\Response(response: 401, description: 'No autenticado', content: new OA\JsonContent(ref: '#/components/schemas/ErrorResponse')),
                new OA\Response(response: 404, description: 'Egreso no encontrado', content: new OA\JsonContent(ref: '#/components/schemas/ErrorResponse')),
                new OA\Response(response: 422, description: 'Errores de validación', content: new OA\JsonContent(ref: '#/components/schemas/ValidationErrorResponse')),
            ]
        )
    ]
    public function update(\App\Http\Requests\Api\V1\DischargeUpdateRequest $request, int $id): JsonResponse
    {
        $r = $this->dischargeService->update($id, $request->validated());
        return ApiResponse::success(new DischargeResource($r), 'Actualizado.');
    }

    #[
        OA\Delete(
            path: '/api/v1/discharges/{id}',
            summary: 'Eliminar egreso',
            description: 'Elimina un egreso existente.',
            tags: ['Discharges'],
            security: [['bearerAuth' => []]],
            parameters: [
                new OA\Parameter(name: 'id', in: 'path', required: true, description: 'ID del egreso', schema: new OA\Schema(type: 'integer')),
            ],
            responses: [
                new OA\Response(response: 200, description: 'Egreso eliminado exitosamente', content: new OA\JsonContent(ref: '#/components/schemas/SuccessResponse')),
                new OA\Response(response: 401, description: 'No autenticado', content: new OA\JsonContent(ref: '#/components/schemas/ErrorResponse')),
                new OA\Response(response: 404, description: 'Egreso no encontrado', content: new OA\JsonContent(ref: '#/components/schemas/ErrorResponse')),
            ]
        )
    ]
    public function destroy(int $id): JsonResponse
    {
        $this->dischargeService->delete($id);
        return ApiResponse::success(null, 'Eliminado.', null, 200);
    }
}
