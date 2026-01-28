<?php

namespace App\Http\Controllers\Api\V1;

use App\Http\Controllers\Controller;
use App\Http\Requests\Api\V1\ThirdReceiptStoreRequest;
use App\Http\Resources\V1\ThirdReceiptResource;
use App\Models\ThirdReceipts;
use App\Services\ThirdReceiptService;
use App\Support\ApiResponse;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use OpenApi\Attributes as OA;

class ThirdReceiptController extends Controller
{
    public function __construct(private ThirdReceiptService $thirdReceiptService) {}

    #[
        OA\Get(
            path: '/api/v1/third-receipts',
            summary: 'Listar recibos de terceros',
            description: 'Lista los recibos de terceros con filtros opcionales y paginación.',
            tags: ['Third Receipts'],
            security: [['bearerAuth' => []]],
            parameters: [
                new OA\Parameter(name: 'third', in: 'query', required: false, description: 'ID del tercero', schema: new OA\Schema(type: 'integer')),
                new OA\Parameter(name: 'from', in: 'query', required: false, description: 'Fecha desde (YYYY-MM-DD)', schema: new OA\Schema(type: 'string', format: 'date')),
                new OA\Parameter(name: 'to', in: 'query', required: false, description: 'Fecha hasta (YYYY-MM-DD)', schema: new OA\Schema(type: 'string', format: 'date')),
                new OA\Parameter(name: 'per_page', in: 'query', required: false, description: 'Elementos por página', schema: new OA\Schema(type: 'integer', example: 15)),
            ],
            responses: [
                new OA\Response(response: 200, description: 'Lista de recibos de terceros', content: new OA\JsonContent(ref: '#/components/schemas/SuccessResponse')),
                new OA\Response(response: 401, description: 'No autenticado', content: new OA\JsonContent(ref: '#/components/schemas/ErrorResponse')),
            ]
        )
    ]
    public function index(Request $request): JsonResponse
    {
        $q = ThirdReceipts::where('type', 'entry')->with(['thirdObject', 'conceptoObject', 'elaboradoObject']);
        if ($request->filled('third')) {
            $q->where('third', $request->third);
        }
        if ($request->filled('from')) {
            $q->whereDate('fecha_recibo', '>=', $request->from);
        }
        if ($request->filled('to')) {
            $q->whereDate('fecha_recibo', '<=', $request->to);
        }
        $perPage = min((int) $request->get('per_page', 15), 100);
        $paginator = $q->orderByRaw('COALESCE(fecha_recibo, created_at) DESC')->orderBy('no_recibo', 'desc')->paginate($perPage);
        return ApiResponse::success(
            ThirdReceiptResource::collection($paginator->items())->resolve(),
            null,
            ['current_page' => $paginator->currentPage(), 'per_page' => $paginator->perPage(), 'total' => $paginator->total(), 'last_page' => $paginator->lastPage()],
            200
        );
    }

    #[
        OA\Post(
            path: '/api/v1/third-receipts',
            summary: 'Crear recibo de tercero',
            description: 'Crea un nuevo recibo de tercero. Asigna automáticamente el número de recibo usando un consecutivo con lockForUpdate.',
            tags: ['Third Receipts'],
            security: [['bearerAuth' => []]],
            requestBody: new OA\RequestBody(
                required: true,
                content: new OA\JsonContent(
                    required: ['third', 'concepto', 'detalles', 'valor', 'debe', 'haber', 'elaborado_por', 'fecha_recibo'],
                    properties: [
                        new OA\Property(property: 'third', type: 'integer', example: 1),
                        new OA\Property(property: 'concepto', type: 'integer', example: 1),
                        new OA\Property(property: 'detalles', type: 'string', example: 'Detalles del recibo'),
                        new OA\Property(property: 'valor', type: 'number', format: 'float', example: 50000),
                        new OA\Property(property: 'debe', type: 'integer', example: 1),
                        new OA\Property(property: 'haber', type: 'integer', example: 1),
                        new OA\Property(property: 'elaborado_por', type: 'integer', example: 1),
                        new OA\Property(property: 'forma', type: 'string', enum: ['Efectivo', 'Bancos'], nullable: true),
                        new OA\Property(property: 'fecha_recibo', type: 'string', format: 'date', example: '2024-01-15'),
                    ]
                )
            ),
            responses: [
                new OA\Response(response: 201, description: 'Recibo de tercero creado exitosamente', content: new OA\JsonContent(ref: '#/components/schemas/SuccessResponse')),
                new OA\Response(response: 401, description: 'No autenticado', content: new OA\JsonContent(ref: '#/components/schemas/ErrorResponse')),
                new OA\Response(response: 422, description: 'Errores de validación', content: new OA\JsonContent(ref: '#/components/schemas/ValidationErrorResponse')),
            ]
        )
    ]
    public function store(ThirdReceiptStoreRequest $request): JsonResponse
    {
        $r = $this->thirdReceiptService->create($request->validated());
        return ApiResponse::success(new ThirdReceiptResource($r), 'Recibo de tercero creado.', null, 201);
    }

    #[
        OA\Get(
            path: '/api/v1/third-receipts/{id}',
            summary: 'Obtener recibo de tercero',
            description: 'Obtiene los datos de un recibo de tercero específico.',
            tags: ['Third Receipts'],
            security: [['bearerAuth' => []]],
            parameters: [
                new OA\Parameter(name: 'id', in: 'path', required: true, description: 'ID del recibo', schema: new OA\Schema(type: 'integer')),
            ],
            responses: [
                new OA\Response(response: 200, description: 'Datos del recibo', content: new OA\JsonContent(ref: '#/components/schemas/SuccessResponse')),
                new OA\Response(response: 401, description: 'No autenticado', content: new OA\JsonContent(ref: '#/components/schemas/ErrorResponse')),
                new OA\Response(response: 404, description: 'Recibo no encontrado', content: new OA\JsonContent(ref: '#/components/schemas/ErrorResponse')),
            ]
        )
    ]
    public function show(int $id): JsonResponse
    {
        $r = ThirdReceipts::where('type', 'entry')->with(['thirdObject', 'conceptoObject'])->findOrFail($id);
        return ApiResponse::success(new ThirdReceiptResource($r));
    }

    #[
        OA\Put(
            path: '/api/v1/third-receipts/{id}',
            summary: 'Actualizar recibo de tercero',
            description: 'Actualiza los datos de un recibo de tercero existente.',
            tags: ['Third Receipts'],
            security: [['bearerAuth' => []]],
            parameters: [
                new OA\Parameter(name: 'id', in: 'path', required: true, description: 'ID del recibo', schema: new OA\Schema(type: 'integer')),
            ],
            requestBody: new OA\RequestBody(
                required: false,
                content: new OA\JsonContent(
                    properties: [
                        new OA\Property(property: 'third', type: 'integer', example: 1, nullable: true),
                        new OA\Property(property: 'concepto', type: 'integer', example: 1, nullable: true),
                        new OA\Property(property: 'detalles', type: 'string', example: 'Detalles actualizados', nullable: true),
                        new OA\Property(property: 'valor', type: 'number', format: 'float', example: 60000, nullable: true),
                        new OA\Property(property: 'debe', type: 'integer', example: 1, nullable: true),
                        new OA\Property(property: 'haber', type: 'integer', example: 1, nullable: true),
                        new OA\Property(property: 'elaborado_por', type: 'integer', example: 1, nullable: true),
                        new OA\Property(property: 'forma', type: 'string', enum: ['Efectivo', 'Bancos'], nullable: true),
                        new OA\Property(property: 'fecha_recibo', type: 'string', format: 'date', example: '2024-01-16', nullable: true),
                    ]
                )
            ),
            responses: [
                new OA\Response(response: 200, description: 'Recibo actualizado exitosamente', content: new OA\JsonContent(ref: '#/components/schemas/SuccessResponse')),
                new OA\Response(response: 401, description: 'No autenticado', content: new OA\JsonContent(ref: '#/components/schemas/ErrorResponse')),
                new OA\Response(response: 404, description: 'Recibo no encontrado', content: new OA\JsonContent(ref: '#/components/schemas/ErrorResponse')),
                new OA\Response(response: 422, description: 'Errores de validación', content: new OA\JsonContent(ref: '#/components/schemas/ValidationErrorResponse')),
            ]
        )
    ]
    public function update(\App\Http\Requests\Api\V1\ThirdReceiptUpdateRequest $request, int $id): JsonResponse
    {
        $r = $this->thirdReceiptService->update($id, $request->validated());
        return ApiResponse::success(new ThirdReceiptResource($r), 'Recibo de tercero actualizado.', null, 200);
    }

    #[
        OA\Delete(
            path: '/api/v1/third-receipts/{id}',
            summary: 'Eliminar recibo de tercero',
            description: 'Elimina un recibo de tercero existente.',
            tags: ['Third Receipts'],
            security: [['bearerAuth' => []]],
            parameters: [
                new OA\Parameter(name: 'id', in: 'path', required: true, description: 'ID del recibo', schema: new OA\Schema(type: 'integer')),
            ],
            responses: [
                new OA\Response(response: 200, description: 'Recibo eliminado exitosamente', content: new OA\JsonContent(ref: '#/components/schemas/SuccessResponse')),
                new OA\Response(response: 401, description: 'No autenticado', content: new OA\JsonContent(ref: '#/components/schemas/ErrorResponse')),
                new OA\Response(response: 404, description: 'Recibo no encontrado', content: new OA\JsonContent(ref: '#/components/schemas/ErrorResponse')),
            ]
        )
    ]
    public function destroy(int $id): JsonResponse
    {
        $this->thirdReceiptService->delete($id);
        return ApiResponse::success(null, 'Eliminado.', null, 200);
    }
}
