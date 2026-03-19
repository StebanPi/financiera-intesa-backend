<?php

namespace App\Http\Controllers\Api\V1;

use App\Http\Controllers\Controller;
use App\Http\Requests\Api\V1\DischargeConceptStoreRequest;
use App\Http\Requests\Api\V1\DischargeConceptUpdateRequest;
use App\Http\Resources\V1\DischargeConceptResource;
use App\Models\EgresoConcept;
use App\Support\ApiResponse;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use OpenApi\Attributes as OA;

class DischargeConceptController extends Controller
{
    #[
        OA\Get(
            path: '/api/v1/discharge-concepts',
            summary: 'Listar conceptos de egreso',
            description: 'Lista los conceptos de egreso con paginación.',
            tags: ['Discharge Concepts'],
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
        $perPage = (int) $request->get('per_page', 100000);
        $paginator = EgresoConcept::query()
            ->where('sede', $request->get('sede_activa', 'BARRANCABERMEJA'))
            ->with(['debeObject', 'haberObject'])
            ->orderBy('nombre')
            ->paginate($perPage);
        return ApiResponse::success(
            DischargeConceptResource::collection($paginator->items())->resolve(),
            null,
            ['current_page' => $paginator->currentPage(), 'per_page' => $paginator->perPage(), 'total' => $paginator->total(), 'last_page' => $paginator->lastPage()],
            200
        );
    }

    #[
        OA\Post(
            path: '/api/v1/discharge-concepts',
            summary: 'Crear concepto de egreso',
            description: 'Crea un nuevo concepto de egreso.',
            tags: ['Discharge Concepts'],
            security: [['bearerAuth' => []]],
            requestBody: new OA\RequestBody(
                required: true,
                content: new OA\JsonContent(
                    required: ['nombre', 'debe', 'haber'],
                    properties: [
                        new OA\Property(property: 'nombre', type: 'string', example: 'Concepto Ejemplo'),
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
    public function store(DischargeConceptStoreRequest $request): JsonResponse
    {
        $data = $request->validated();
        $data['state'] = $request->boolean('state', false);
        $data['sede'] = $request->get('sede_activa', 'BARRANCABERMEJA');
        $c = EgresoConcept::create($data);
        return ApiResponse::success(new DischargeConceptResource($c), 'Concepto de egreso creado.', null, 201);
    }

    #[
        OA\Get(
            path: '/api/v1/discharge-concepts/{id}',
            summary: 'Obtener concepto de egreso',
            description: 'Obtiene los datos de un concepto de egreso específico.',
            tags: ['Discharge Concepts'],
            security: [['bearerAuth' => []]],
            parameters: [
                new OA\Parameter(name: 'id', in: 'path', required: true, description: 'ID del concepto de egreso', schema: new OA\Schema(type: 'integer')),
            ],
            responses: [
                new OA\Response(response: 200, description: 'Datos del concepto de egreso', content: new OA\JsonContent(ref: '#/components/schemas/SuccessResponse')),
                new OA\Response(response: 401, description: 'No autenticado', content: new OA\JsonContent(ref: '#/components/schemas/ErrorResponse')),
                new OA\Response(response: 404, description: 'Concepto de egreso no encontrado', content: new OA\JsonContent(ref: '#/components/schemas/ErrorResponse')),
            ]
        )
    ]
    public function show(int $id): JsonResponse
    {
        $c = EgresoConcept::with(['debeObject', 'haberObject'])->findOrFail($id);
        return ApiResponse::success(new DischargeConceptResource($c));
    }

    #[
        OA\Put(
            path: '/api/v1/discharge-concepts/{id}',
            summary: 'Actualizar concepto de egreso',
            description: 'Actualiza los datos de un concepto de egreso existente.',
            tags: ['Discharge Concepts'],
            security: [['bearerAuth' => []]],
            parameters: [
                new OA\Parameter(name: 'id', in: 'path', required: true, description: 'ID del concepto de egreso', schema: new OA\Schema(type: 'integer')),
            ],
            requestBody: new OA\RequestBody(
                required: false,
                content: new OA\JsonContent(
                    properties: [
                        new OA\Property(property: 'nombre', type: 'string', nullable: true),
                        new OA\Property(property: 'debe', type: 'integer', nullable: true),
                        new OA\Property(property: 'haber', type: 'integer', nullable: true),
                        new OA\Property(property: 'state', type: 'boolean', nullable: true),
                    ]
                )
            ),
            responses: [
                new OA\Response(response: 200, description: 'Concepto de egreso actualizado exitosamente', content: new OA\JsonContent(ref: '#/components/schemas/SuccessResponse')),
                new OA\Response(response: 401, description: 'No autenticado', content: new OA\JsonContent(ref: '#/components/schemas/ErrorResponse')),
                new OA\Response(response: 404, description: 'Concepto de egreso no encontrado', content: new OA\JsonContent(ref: '#/components/schemas/ErrorResponse')),
                new OA\Response(response: 422, description: 'Errores de validación', content: new OA\JsonContent(ref: '#/components/schemas/ValidationErrorResponse')),
            ]
        )
    ]
    public function update(DischargeConceptUpdateRequest $request, int $id): JsonResponse
    {
        $c = EgresoConcept::findOrFail($id);
        $data = $request->validated();
        if (array_key_exists('state', $data)) {
            $data['state'] = filter_var($data['state'], FILTER_VALIDATE_BOOLEAN);
        }
        $c->update($data);
        return ApiResponse::success(new DischargeConceptResource($c->fresh()), 'Concepto de egreso actualizado.', null, 200);
    }

    #[
        OA\Delete(
            path: '/api/v1/discharge-concepts/{id}',
            summary: 'Eliminar concepto de egreso',
            description: 'Elimina un concepto de egreso existente.',
            tags: ['Discharge Concepts'],
            security: [['bearerAuth' => []]],
            parameters: [
                new OA\Parameter(name: 'id', in: 'path', required: true, description: 'ID del concepto de egreso', schema: new OA\Schema(type: 'integer')),
            ],
            responses: [
                new OA\Response(response: 200, description: 'Concepto de egreso eliminado exitosamente', content: new OA\JsonContent(ref: '#/components/schemas/SuccessResponse')),
                new OA\Response(response: 401, description: 'No autenticado', content: new OA\JsonContent(ref: '#/components/schemas/ErrorResponse')),
                new OA\Response(response: 404, description: 'Concepto de egreso no encontrado', content: new OA\JsonContent(ref: '#/components/schemas/ErrorResponse')),
            ]
        )
    ]
    public function destroy(int $id): JsonResponse
    {
        $c = EgresoConcept::findOrFail($id);
        $c->delete();
        return ApiResponse::success(null, 'Eliminado.', null, 200);
    }
}
