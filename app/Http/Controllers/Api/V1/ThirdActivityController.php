<?php

namespace App\Http\Controllers\Api\V1;

use App\Http\Controllers\Controller;
use App\Http\Requests\Api\V1\ThirdActivityStoreRequest;
use App\Http\Requests\Api\V1\ThirdActivityUpdateRequest;
use App\Http\Resources\V1\ThirdActivityResource;
use App\Models\thirdActivity;
use App\Models\thirdEntry;
use App\Support\ApiResponse;
use Illuminate\Validation\ValidationException;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use OpenApi\Attributes as OA;

class ThirdActivityController extends Controller
{
    #[
        OA\Get(
            path: '/api/v1/third-activities',
            summary: 'Listar actividades de terceros',
            description: 'Lista las actividades de terceros con paginación.',
            tags: ['Third Activities'],
            security: [['bearerAuth' => []]],
            parameters: [
                new OA\Parameter(name: 'per_page', in: 'query', required: false, description: 'Elementos por página', schema: new OA\Schema(type: 'integer', example: 15)),
            ],
            responses: [
                new OA\Response(response: 200, description: 'Lista de actividades', content: new OA\JsonContent(ref: '#/components/schemas/SuccessResponse')),
                new OA\Response(response: 401, description: 'No autenticado', content: new OA\JsonContent(ref: '#/components/schemas/ErrorResponse')),
            ]
        )
    ]
    public function index(Request $request): JsonResponse
    {
        $perPage = min((int) $request->get('per_page', 15), 100);
        $paginator = thirdActivity::query()->orderBy('created_at', 'desc')->paginate($perPage);
        return ApiResponse::success(
            ThirdActivityResource::collection($paginator->items())->resolve(),
            null,
            ['current_page' => $paginator->currentPage(), 'per_page' => $paginator->perPage(), 'total' => $paginator->total(), 'last_page' => $paginator->lastPage()],
            200
        );
    }

    #[
        OA\Post(
            path: '/api/v1/third-activities',
            summary: 'Crear actividad de tercero',
            description: 'Crea una nueva actividad de tercero.',
            tags: ['Third Activities'],
            security: [['bearerAuth' => []]],
            requestBody: new OA\RequestBody(
                required: true,
                content: new OA\JsonContent(
                    required: ['nombre'],
                    properties: [
                        new OA\Property(property: 'nombre', type: 'string', example: 'Actividad Ejemplo'),
                    ]
                )
            ),
            responses: [
                new OA\Response(response: 201, description: 'Actividad creada exitosamente', content: new OA\JsonContent(ref: '#/components/schemas/SuccessResponse')),
                new OA\Response(response: 401, description: 'No autenticado', content: new OA\JsonContent(ref: '#/components/schemas/ErrorResponse')),
                new OA\Response(response: 422, description: 'Errores de validación', content: new OA\JsonContent(ref: '#/components/schemas/ValidationErrorResponse')),
            ]
        )
    ]
    public function store(ThirdActivityStoreRequest $request): JsonResponse
    {
        $a = thirdActivity::create($request->validated());
        return ApiResponse::success(new ThirdActivityResource($a), 'Actividad creada.', null, 201);
    }

    #[
        OA\Get(
            path: '/api/v1/third-activities/{id}',
            summary: 'Obtener actividad de tercero',
            description: 'Obtiene los datos de una actividad de tercero específica.',
            tags: ['Third Activities'],
            security: [['bearerAuth' => []]],
            parameters: [
                new OA\Parameter(name: 'id', in: 'path', required: true, description: 'ID de la actividad', schema: new OA\Schema(type: 'integer')),
            ],
            responses: [
                new OA\Response(response: 200, description: 'Datos de la actividad', content: new OA\JsonContent(ref: '#/components/schemas/SuccessResponse')),
                new OA\Response(response: 401, description: 'No autenticado', content: new OA\JsonContent(ref: '#/components/schemas/ErrorResponse')),
                new OA\Response(response: 404, description: 'Actividad no encontrada', content: new OA\JsonContent(ref: '#/components/schemas/ErrorResponse')),
            ]
        )
    ]
    public function show(int $id): JsonResponse
    {
        $a = thirdActivity::findOrFail($id);
        return ApiResponse::success(new ThirdActivityResource($a));
    }

    #[
        OA\Put(
            path: '/api/v1/third-activities/{id}',
            summary: 'Actualizar actividad de tercero',
            description: 'Actualiza los datos de una actividad de tercero existente.',
            tags: ['Third Activities'],
            security: [['bearerAuth' => []]],
            parameters: [
                new OA\Parameter(name: 'id', in: 'path', required: true, description: 'ID de la actividad', schema: new OA\Schema(type: 'integer')),
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
                new OA\Response(response: 200, description: 'Actividad actualizada exitosamente', content: new OA\JsonContent(ref: '#/components/schemas/SuccessResponse')),
                new OA\Response(response: 401, description: 'No autenticado', content: new OA\JsonContent(ref: '#/components/schemas/ErrorResponse')),
                new OA\Response(response: 404, description: 'Actividad no encontrada', content: new OA\JsonContent(ref: '#/components/schemas/ErrorResponse')),
                new OA\Response(response: 422, description: 'Errores de validación', content: new OA\JsonContent(ref: '#/components/schemas/ValidationErrorResponse')),
            ]
        )
    ]
    public function update(ThirdActivityUpdateRequest $request, int $id): JsonResponse
    {
        $a = thirdActivity::findOrFail($id);
        $a->update($request->validated());
        return ApiResponse::success(new ThirdActivityResource($a->fresh()), 'Actividad actualizada.', null, 200);
    }

    #[
        OA\Delete(
            path: '/api/v1/third-activities/{id}',
            summary: 'Eliminar actividad de tercero',
            description: 'Elimina una actividad de tercero existente. No se puede eliminar si hay terceros asociados.',
            tags: ['Third Activities'],
            security: [['bearerAuth' => []]],
            parameters: [
                new OA\Parameter(name: 'id', in: 'path', required: true, description: 'ID de la actividad', schema: new OA\Schema(type: 'integer')),
            ],
            responses: [
                new OA\Response(response: 200, description: 'Actividad eliminada exitosamente', content: new OA\JsonContent(ref: '#/components/schemas/SuccessResponse')),
                new OA\Response(response: 401, description: 'No autenticado', content: new OA\JsonContent(ref: '#/components/schemas/ErrorResponse')),
                new OA\Response(response: 404, description: 'Actividad no encontrada', content: new OA\JsonContent(ref: '#/components/schemas/ErrorResponse')),
                new OA\Response(response: 422, description: 'No se puede eliminar porque hay terceros asociados', content: new OA\JsonContent(ref: '#/components/schemas/ValidationErrorResponse')),
            ]
        )
    ]
    public function destroy(int $id): JsonResponse
    {
        $a = thirdActivity::findOrFail($id);
        if (thirdEntry::where('actividad', $id)->exists()) {
            throw ValidationException::withMessages(['id' => ['No se puede eliminar la actividad: hay terceros asociados.']]);
        }
        $a->delete();
        return ApiResponse::success(null, 'Eliminado.', null, 200);
    }
}
