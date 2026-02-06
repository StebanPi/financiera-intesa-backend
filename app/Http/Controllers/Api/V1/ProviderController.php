<?php

namespace App\Http\Controllers\Api\V1;

use App\Http\Controllers\Controller;
use App\Http\Requests\Api\V1\ProviderStoreRequest;
use App\Http\Requests\Api\V1\ProviderUpdateRequest;
use App\Http\Resources\V1\ProviderResource;
use App\Models\EgresoProvider;
use App\Support\ApiResponse;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use OpenApi\Attributes as OA;

class ProviderController extends Controller
{
    #[
        OA\Get(
            path: '/api/v1/providers',
            summary: 'Listar proveedores',
            description: 'Lista los proveedores con paginación.',
            tags: ['Providers'],
            security: [['bearerAuth' => []]],
            parameters: [
                new OA\Parameter(name: 'per_page', in: 'query', required: false, description: 'Elementos por página', schema: new OA\Schema(type: 'integer', example: 15)),
            ],
            responses: [
                new OA\Response(response: 200, description: 'Lista de proveedores', content: new OA\JsonContent(ref: '#/components/schemas/SuccessResponse')),
                new OA\Response(response: 401, description: 'No autenticado', content: new OA\JsonContent(ref: '#/components/schemas/ErrorResponse')),
            ]
        )
    ]
    public function index(Request $request): JsonResponse
    {
        $perPage = (int) $request->get('per_page', 100000);
        $q = EgresoProvider::query()->orderBy('nombre');
        $paginator = $q->paginate($perPage);
        return ApiResponse::success(
            ProviderResource::collection($paginator->items())->resolve(),
            null,
            ['current_page' => $paginator->currentPage(), 'per_page' => $paginator->perPage(), 'total' => $paginator->total(), 'last_page' => $paginator->lastPage()],
            200
        );
    }

    #[
        OA\Post(
            path: '/api/v1/providers',
            summary: 'Crear proveedor',
            description: 'Crea un nuevo proveedor.',
            tags: ['Providers'],
            security: [['bearerAuth' => []]],
            requestBody: new OA\RequestBody(
                required: true,
                content: new OA\JsonContent(
                    required: ['nombre'],
                    properties: [
                        new OA\Property(property: 'nombre', type: 'string', example: 'Proveedor Ejemplo'),
                    ]
                )
            ),
            responses: [
                new OA\Response(response: 201, description: 'Proveedor creado exitosamente', content: new OA\JsonContent(ref: '#/components/schemas/SuccessResponse')),
                new OA\Response(response: 401, description: 'No autenticado', content: new OA\JsonContent(ref: '#/components/schemas/ErrorResponse')),
                new OA\Response(response: 422, description: 'Errores de validación', content: new OA\JsonContent(ref: '#/components/schemas/ValidationErrorResponse')),
            ]
        )
    ]
    public function store(ProviderStoreRequest $request): JsonResponse
    {
        $p = EgresoProvider::create($request->validated());
        return ApiResponse::success(new ProviderResource($p), 'Proveedor creado.', null, 201);
    }

    #[
        OA\Get(
            path: '/api/v1/providers/{id}',
            summary: 'Obtener proveedor',
            description: 'Obtiene los datos de un proveedor específico.',
            tags: ['Providers'],
            security: [['bearerAuth' => []]],
            parameters: [
                new OA\Parameter(name: 'id', in: 'path', required: true, description: 'ID del proveedor', schema: new OA\Schema(type: 'integer')),
            ],
            responses: [
                new OA\Response(response: 200, description: 'Datos del proveedor', content: new OA\JsonContent(ref: '#/components/schemas/SuccessResponse')),
                new OA\Response(response: 401, description: 'No autenticado', content: new OA\JsonContent(ref: '#/components/schemas/ErrorResponse')),
                new OA\Response(response: 404, description: 'Proveedor no encontrado', content: new OA\JsonContent(ref: '#/components/schemas/ErrorResponse')),
            ]
        )
    ]
    public function show(int $id): JsonResponse
    {
        $p = EgresoProvider::findOrFail($id);
        return ApiResponse::success(new ProviderResource($p));
    }

    #[
        OA\Put(
            path: '/api/v1/providers/{id}',
            summary: 'Actualizar proveedor',
            description: 'Actualiza los datos de un proveedor existente.',
            tags: ['Providers'],
            security: [['bearerAuth' => []]],
            parameters: [
                new OA\Parameter(name: 'id', in: 'path', required: true, description: 'ID del proveedor', schema: new OA\Schema(type: 'integer')),
            ],
            requestBody: new OA\RequestBody(
                required: false,
                content: new OA\JsonContent(
                    properties: [
                        new OA\Property(property: 'nombre', type: 'string', nullable: true),
                    ]
                )
            ),
            responses: [
                new OA\Response(response: 200, description: 'Proveedor actualizado exitosamente', content: new OA\JsonContent(ref: '#/components/schemas/SuccessResponse')),
                new OA\Response(response: 401, description: 'No autenticado', content: new OA\JsonContent(ref: '#/components/schemas/ErrorResponse')),
                new OA\Response(response: 404, description: 'Proveedor no encontrado', content: new OA\JsonContent(ref: '#/components/schemas/ErrorResponse')),
                new OA\Response(response: 422, description: 'Errores de validación', content: new OA\JsonContent(ref: '#/components/schemas/ValidationErrorResponse')),
            ]
        )
    ]
    public function update(ProviderUpdateRequest $request, int $id): JsonResponse
    {
        $p = EgresoProvider::findOrFail($id);
        $p->update($request->validated());
        return ApiResponse::success(new ProviderResource($p->fresh()), 'Proveedor actualizado.', null, 200);
    }

    #[
        OA\Delete(
            path: '/api/v1/providers/{id}',
            summary: 'Eliminar proveedor',
            description: 'Elimina un proveedor existente.',
            tags: ['Providers'],
            security: [['bearerAuth' => []]],
            parameters: [
                new OA\Parameter(name: 'id', in: 'path', required: true, description: 'ID del proveedor', schema: new OA\Schema(type: 'integer')),
            ],
            responses: [
                new OA\Response(response: 200, description: 'Proveedor eliminado exitosamente', content: new OA\JsonContent(ref: '#/components/schemas/SuccessResponse')),
                new OA\Response(response: 401, description: 'No autenticado', content: new OA\JsonContent(ref: '#/components/schemas/ErrorResponse')),
                new OA\Response(response: 404, description: 'Proveedor no encontrado', content: new OA\JsonContent(ref: '#/components/schemas/ErrorResponse')),
            ]
        )
    ]
    public function destroy(int $id): JsonResponse
    {
        $p = EgresoProvider::findOrFail($id);
        try {
            $p->delete();
            return ApiResponse::success(null, 'Eliminado.', null, 200);
        } catch (\Illuminate\Database\QueryException $e) {
            if ($e->getCode() == 23000) {
                return ApiResponse::error('No se puede eliminar el proveedor porque tiene recibos asociados.', 409);
            }
            throw $e;
        }
    }
}
