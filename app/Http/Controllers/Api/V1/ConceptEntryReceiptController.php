<?php

namespace App\Http\Controllers\Api\V1;

use App\Http\Controllers\Controller;
use App\Http\Requests\Api\V1\ConceptEntryReceiptStoreRequest;
use App\Http\Requests\Api\V1\ConceptEntryReceiptUpdateRequest;
use App\Http\Resources\V1\ConceptEntryReceiptResource;
use App\Models\ConceptEntryReceipt;
use App\Support\ApiResponse;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use OpenApi\Attributes as OA;

class ConceptEntryReceiptController extends Controller
{
    #[
        OA\Get(
            path: '/api/v1/concept-entry-receipts',
            summary: 'Listar conceptos de ingreso',
            description: 'Lista los conceptos de ingreso con paginación.',
            tags: ['Concept Entry Receipts'],
            security: [['bearerAuth' => []]],
            parameters: [
                new OA\Parameter(name: 'per_page', in: 'query', required: false, description: 'Elementos por página', schema: new OA\Schema(type: 'integer', example: 15)),
            ],
            responses: [
                new OA\Response(response: 200, description: 'Lista de conceptos de ingreso', content: new OA\JsonContent(ref: '#/components/schemas/SuccessResponse')),
                new OA\Response(response: 401, description: 'No autenticado', content: new OA\JsonContent(ref: '#/components/schemas/ErrorResponse')),
            ]
        )
    ]
    public function index(Request $request): JsonResponse
    {
        $perPage = min((int) $request->get('per_page', 15), 100);
        $paginator = ConceptEntryReceipt::query()
            ->where('sede', $request->get('sede_activa', 'BARRANCABERMEJA'))
            ->with(['debeObject', 'haberObject'])
            ->orderBy('created_at', 'desc')
            ->paginate($perPage);
        return ApiResponse::success(
            ConceptEntryReceiptResource::collection($paginator->items())->resolve(),
            null,
            ['current_page' => $paginator->currentPage(), 'per_page' => $paginator->perPage(), 'total' => $paginator->total(), 'last_page' => $paginator->lastPage()],
            200
        );
    }

    #[
        OA\Post(
            path: '/api/v1/concept-entry-receipts',
            summary: 'Crear concepto de ingreso',
            description: 'Crea un nuevo concepto de ingreso.',
            tags: ['Concept Entry Receipts'],
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
                new OA\Response(response: 201, description: 'Concepto de ingreso creado exitosamente', content: new OA\JsonContent(ref: '#/components/schemas/SuccessResponse')),
                new OA\Response(response: 401, description: 'No autenticado', content: new OA\JsonContent(ref: '#/components/schemas/ErrorResponse')),
                new OA\Response(response: 422, description: 'Errores de validación', content: new OA\JsonContent(ref: '#/components/schemas/ValidationErrorResponse')),
            ]
        )
    ]
    public function store(ConceptEntryReceiptStoreRequest $request): JsonResponse
    {
        $data = $request->validated();
        $data['state'] = $request->boolean('state', false);
        $data['sede'] = $request->get('sede_activa', 'BARRANCABERMEJA');
        $c = ConceptEntryReceipt::create($data);
        return ApiResponse::success(new ConceptEntryReceiptResource($c), 'Concepto de ingreso creado.', null, 201);
    }

    #[
        OA\Get(
            path: '/api/v1/concept-entry-receipts/{id}',
            summary: 'Obtener concepto de ingreso',
            description: 'Obtiene los datos de un concepto de ingreso específico.',
            tags: ['Concept Entry Receipts'],
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
        $c = ConceptEntryReceipt::with(['debeObject', 'haberObject'])->findOrFail($id);
        return ApiResponse::success(new ConceptEntryReceiptResource($c));
    }

    #[
        OA\Put(
            path: '/api/v1/concept-entry-receipts/{id}',
            summary: 'Actualizar concepto de ingreso',
            description: 'Actualiza los datos de un concepto de ingreso existente.',
            tags: ['Concept Entry Receipts'],
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
    public function update(ConceptEntryReceiptUpdateRequest $request, int $id): JsonResponse
    {
        $c = ConceptEntryReceipt::findOrFail($id);
        $data = $request->validated();
        if (array_key_exists('state', $data)) {
            $data['state'] = filter_var($data['state'], FILTER_VALIDATE_BOOLEAN);
        }
        $c->update($data);
        return ApiResponse::success(new ConceptEntryReceiptResource($c->fresh()), 'Concepto de ingreso actualizado.', null, 200);
    }

    #[
        OA\Delete(
            path: '/api/v1/concept-entry-receipts/{id}',
            summary: 'Eliminar concepto de ingreso',
            description: 'Elimina un concepto de ingreso existente.',
            tags: ['Concept Entry Receipts'],
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
        $c = ConceptEntryReceipt::findOrFail($id);
        $c->delete();
        return ApiResponse::success(null, 'Eliminado.', null, 200);
    }
}
