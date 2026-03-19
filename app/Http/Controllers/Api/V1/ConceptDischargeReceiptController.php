<?php

namespace App\Http\Controllers\Api\V1;

use App\Http\Controllers\Controller;
use App\Http\Requests\Api\V1\ConceptDischargeReceiptStoreRequest;
use App\Http\Requests\Api\V1\ConceptDischargeReceiptUpdateRequest;
use App\Http\Resources\V1\ConceptDischargeReceiptResource;
use App\Models\ConceptDischargeReceipt;
use App\Support\ApiResponse;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use OpenApi\Attributes as OA;

class ConceptDischargeReceiptController extends Controller
{
    #[
        OA\Get(
            path: '/api/v1/concept-discharge-receipts',
            summary: 'Listar conceptos de egreso',
            description: 'Lista los conceptos de egreso con paginación.',
            tags: ['Concept Discharge Receipts'],
            security: [['bearerAuth' => []]],
            parameters: [
                new OA\Parameter(name: 'per_page', in: 'query', required: false, description: 'Elementos por página', schema: new OA\Schema(type: 'integer', example: 15)),
            ],
            responses: [
                new OA\Response(response: 200, description: 'Lista de conceptos de egreso', content: new OA\JsonContent(ref: '#/components/schemas/SuccessResponse')),
                new OA\Response(response: 401, description: 'No autenticado', content: new OA\JsonContent(ref: '#/components/schemas/ErrorResponse')),
            ]
        )
    ]
    public function index(Request $request): JsonResponse
    {
        $perPage = min((int) $request->get('per_page', 15), 100);
        $paginator = ConceptDischargeReceipt::query()
            ->where('sede', $request->get('sede_activa', 'BARRANCABERMEJA'))
            ->with(['debeObject', 'haberObject'])
            ->orderBy('created_at', 'desc')
            ->paginate($perPage);
        return ApiResponse::success(
            ConceptDischargeReceiptResource::collection($paginator->items())->resolve(),
            null,
            ['current_page' => $paginator->currentPage(), 'per_page' => $paginator->perPage(), 'total' => $paginator->total(), 'last_page' => $paginator->lastPage()],
            200
        );
    }

    #[
        OA\Post(
            path: '/api/v1/concept-discharge-receipts',
            summary: 'Crear concepto de egreso',
            description: 'Crea un nuevo concepto de egreso.',
            tags: ['Concept Discharge Receipts'],
            security: [['bearerAuth' => []]],
            requestBody: new OA\RequestBody(
                required: true,
                content: new OA\JsonContent(
                    required: ['name', 'debe', 'haber'],
                    properties: [
                        new OA\Property(property: 'name', type: 'string', example: 'Concepto Ejemplo'),
                        new OA\Property(property: 'debe', type: 'integer', example: 1),
                        new OA\Property(property: 'haber', type: 'integer', example: 1),
                        new OA\Property(property: 'state', type: 'boolean', nullable: true, example: false),
                    ]
                )
            ),
            responses: [
                new OA\Response(response: 201, description: 'Concepto de egreso creado exitosamente', content: new OA\JsonContent(ref: '#/components/schemas/SuccessResponse')),
                new OA\Response(response: 401, description: 'No autenticado', content: new OA\JsonContent(ref: '#/components/schemas/ErrorResponse')),
                new OA\Response(response: 422, description: 'Errores de validación', content: new OA\JsonContent(ref: '#/components/schemas/ValidationErrorResponse')),
            ]
        )
    ]
    public function store(ConceptDischargeReceiptStoreRequest $request): JsonResponse
    {
        $data = $request->validated();
        $data['state'] = $request->boolean('state', false);
        $data['sede'] = $request->get('sede_activa', 'BARRANCABERMEJA');
        $c = ConceptDischargeReceipt::create($data);
        return ApiResponse::success(new ConceptDischargeReceiptResource($c), 'Concepto de egreso (terceros) creado.', null, 201);
    }

    #[
        OA\Get(
            path: '/api/v1/concept-discharge-receipts/{id}',
            summary: 'Obtener concepto de egreso',
            description: 'Obtiene los datos de un concepto de egreso específico.',
            tags: ['Concept Discharge Receipts'],
            security: [['bearerAuth' => []]],
            parameters: [
                new OA\Parameter(name: 'id', in: 'path', required: true, description: 'ID del concepto', schema: new OA\Schema(type: 'integer')),
            ],
            responses: [
                new OA\Response(response: 200, description: 'Datos del concepto', content: new OA\JsonContent(ref: '#/components/schemas/SuccessResponse')),
                new OA\Response(response: 401, description: 'No autenticado', content: new OA\JsonContent(ref: '#/components/schemas/ErrorResponse')),
                new OA\Response(response: 404, description: 'Concepto no encontrado', content: new OA\JsonContent(ref: '#/components/schemas/ErrorResponse')),
            ]
        )
    ]
    public function show(int $id): JsonResponse
    {
        $c = ConceptDischargeReceipt::with(['debeObject', 'haberObject'])->findOrFail($id);
        return ApiResponse::success(new ConceptDischargeReceiptResource($c));
    }

    #[
        OA\Put(
            path: '/api/v1/concept-discharge-receipts/{id}',
            summary: 'Actualizar concepto de egreso',
            description: 'Actualiza los datos de un concepto de egreso existente.',
            tags: ['Concept Discharge Receipts'],
            security: [['bearerAuth' => []]],
            parameters: [
                new OA\Parameter(name: 'id', in: 'path', required: true, description: 'ID del concepto', schema: new OA\Schema(type: 'integer')),
            ],
            requestBody: new OA\RequestBody(
                required: false,
                content: new OA\JsonContent(
                    properties: [
                        new OA\Property(property: 'name', type: 'string', nullable: true),
                        new OA\Property(property: 'debe', type: 'integer', nullable: true),
                        new OA\Property(property: 'haber', type: 'integer', nullable: true),
                        new OA\Property(property: 'state', type: 'boolean', nullable: true),
                    ]
                )
            ),
            responses: [
                new OA\Response(response: 200, description: 'Concepto actualizado exitosamente', content: new OA\JsonContent(ref: '#/components/schemas/SuccessResponse')),
                new OA\Response(response: 401, description: 'No autenticado', content: new OA\JsonContent(ref: '#/components/schemas/ErrorResponse')),
                new OA\Response(response: 404, description: 'Concepto no encontrado', content: new OA\JsonContent(ref: '#/components/schemas/ErrorResponse')),
                new OA\Response(response: 422, description: 'Errores de validación', content: new OA\JsonContent(ref: '#/components/schemas/ValidationErrorResponse')),
            ]
        )
    ]
    public function update(ConceptDischargeReceiptUpdateRequest $request, int $id): JsonResponse
    {
        $c = ConceptDischargeReceipt::findOrFail($id);
        $data = $request->validated();
        if (array_key_exists('state', $data)) {
            $data['state'] = filter_var($data['state'], FILTER_VALIDATE_BOOLEAN);
        }
        $c->update($data);
        return ApiResponse::success(new ConceptDischargeReceiptResource($c->fresh()), 'Concepto de egreso (terceros) actualizado.', null, 200);
    }

    #[
        OA\Delete(
            path: '/api/v1/concept-discharge-receipts/{id}',
            summary: 'Eliminar concepto de egreso',
            description: 'Elimina un concepto de egreso existente.',
            tags: ['Concept Discharge Receipts'],
            security: [['bearerAuth' => []]],
            parameters: [
                new OA\Parameter(name: 'id', in: 'path', required: true, description: 'ID del concepto', schema: new OA\Schema(type: 'integer')),
            ],
            responses: [
                new OA\Response(response: 200, description: 'Concepto eliminado exitosamente', content: new OA\JsonContent(ref: '#/components/schemas/SuccessResponse')),
                new OA\Response(response: 401, description: 'No autenticado', content: new OA\JsonContent(ref: '#/components/schemas/ErrorResponse')),
                new OA\Response(response: 404, description: 'Concepto no encontrado', content: new OA\JsonContent(ref: '#/components/schemas/ErrorResponse')),
            ]
        )
    ]
    public function destroy(int $id): JsonResponse
    {
        $c = ConceptDischargeReceipt::findOrFail($id);
        $c->delete();
        return ApiResponse::success(null, 'Eliminado.', null, 200);
    }
}
