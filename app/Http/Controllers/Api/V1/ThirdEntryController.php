<?php

namespace App\Http\Controllers\Api\V1;

use App\Http\Controllers\Controller;
use App\Http\Requests\Api\V1\ThirdEntryStoreRequest;
use App\Http\Requests\Api\V1\ThirdEntryUpdateRequest;
use App\Http\Resources\V1\ThirdEntryResource;
use App\Models\ThirdReceipts;
use App\Models\thirdEntry;
use App\Support\ApiResponse;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Validation\ValidationException;
use OpenApi\Attributes as OA;

class ThirdEntryController extends Controller
{
    #[
        OA\Get(
            path: '/api/v1/third-entries',
            summary: 'Listar terceros',
            description: 'Lista los terceros con paginación.',
            tags: ['Third Entries'],
            security: [['bearerAuth' => []]],
            parameters: [
                new OA\Parameter(name: 'per_page', in: 'query', required: false, description: 'Elementos por página', schema: new OA\Schema(type: 'integer', example: 15)),
                new OA\Parameter(name: 'cedula', in: 'query', required: false, description: 'Filtrar por cédula', schema: new OA\Schema(type: 'string')),
                new OA\Parameter(name: 'nombre', in: 'query', required: false, description: 'Filtrar por nombre', schema: new OA\Schema(type: 'string')),
            ],
            responses: [
                new OA\Response(response: 200, description: 'Lista de terceros', content: new OA\JsonContent(ref: '#/components/schemas/SuccessResponse')),
                new OA\Response(response: 401, description: 'No autenticado', content: new OA\JsonContent(ref: '#/components/schemas/ErrorResponse')),
            ]
        )
    ]
    public function index(Request $request): JsonResponse
    {
        $perPage = min((int) $request->get('per_page', 15), 100);
        $query = thirdEntry::with('thirdActivity')->orderBy('created_at', 'desc');

        if ($request->filled('cedula')) {
            $query->where('cedula', 'like', '%' . $request->cedula . '%');
        }

        if ($request->filled('nombre')) {
            $query->where('nombre', 'like', '%' . $request->nombre . '%');
        }

        $paginator = $query->paginate($perPage);

        return ApiResponse::success(
            ThirdEntryResource::collection($paginator->items())->resolve(),
            null,
            ['current_page' => $paginator->currentPage(), 'per_page' => $paginator->perPage(), 'total' => $paginator->total(), 'last_page' => $paginator->lastPage()],
            200
        );
    }

    #[
        OA\Post(
            path: '/api/v1/third-entries',
            summary: 'Crear tercero',
            description: 'Crea un nuevo tercero.',
            tags: ['Third Entries'],
            security: [['bearerAuth' => []]],
            requestBody: new OA\RequestBody(
                required: true,
                content: new OA\JsonContent(
                    required: ['nombre', 'actividad'],
                    properties: [
                        new OA\Property(property: 'cedula', type: 'string', example: '123456789'),
                        new OA\Property(property: 'nombre', type: 'string', example: 'Tercero Ejemplo'),
                        new OA\Property(property: 'direccion', type: 'string', example: 'Calle 123'),
                        new OA\Property(property: 'telefono', type: 'string', example: '5551234'),
                        new OA\Property(property: 'actividad', type: 'integer', example: 1),
                        new OA\Property(property: 'mas', type: 'string', example: 'Info adicional'),
                    ]
                )
            ),
            responses: [
                new OA\Response(response: 201, description: 'Tercero creado exitosamente', content: new OA\JsonContent(ref: '#/components/schemas/SuccessResponse')),
                new OA\Response(response: 401, description: 'No autenticado', content: new OA\JsonContent(ref: '#/components/schemas/ErrorResponse')),
                new OA\Response(response: 422, description: 'Errores de validación', content: new OA\JsonContent(ref: '#/components/schemas/ValidationErrorResponse')),
            ]
        )
    ]
    public function store(ThirdEntryStoreRequest $request): JsonResponse
    {
        $e = thirdEntry::create($request->validated());
        return ApiResponse::success(new ThirdEntryResource($e), 'Tercero creado.', null, 201);
    }

    #[
        OA\Get(
            path: '/api/v1/third-entries/{id}',
            summary: 'Obtener tercero',
            description: 'Obtiene los datos de un tercero específico.',
            tags: ['Third Entries'],
            security: [['bearerAuth' => []]],
            parameters: [
                new OA\Parameter(name: 'id', in: 'path', required: true, description: 'ID del tercero', schema: new OA\Schema(type: 'integer')),
            ],
            responses: [
                new OA\Response(response: 200, description: 'Datos del tercero', content: new OA\JsonContent(ref: '#/components/schemas/SuccessResponse')),
                new OA\Response(response: 401, description: 'No autenticado', content: new OA\JsonContent(ref: '#/components/schemas/ErrorResponse')),
                new OA\Response(response: 404, description: 'Tercero no encontrado', content: new OA\JsonContent(ref: '#/components/schemas/ErrorResponse')),
            ]
        )
    ]
    public function show(int $id): JsonResponse
    {
        $e = thirdEntry::with('thirdActivity')->findOrFail($id);
        return ApiResponse::success(new ThirdEntryResource($e));
    }

    #[
        OA\Put(
            path: '/api/v1/third-entries/{id}',
            summary: 'Actualizar tercero',
            description: 'Actualiza los datos de un tercero existente.',
            tags: ['Third Entries'],
            security: [['bearerAuth' => []]],
            parameters: [
                new OA\Parameter(name: 'id', in: 'path', required: true, description: 'ID del tercero', schema: new OA\Schema(type: 'integer')),
            ],
            requestBody: new OA\RequestBody(
                required: false,
                content: new OA\JsonContent(
                    properties: [
                        new OA\Property(property: 'cedula', type: 'string', nullable: true),
                        new OA\Property(property: 'nombre', type: 'string', nullable: true),
                        new OA\Property(property: 'direccion', type: 'string', nullable: true),
                        new OA\Property(property: 'telefono', type: 'string', nullable: true),
                        new OA\Property(property: 'actividad', type: 'integer', nullable: true),
                        new OA\Property(property: 'mas', type: 'string', nullable: true),
                    ]
                )
            ),
            responses: [
                new OA\Response(response: 200, description: 'Tercero actualizado exitosamente', content: new OA\JsonContent(ref: '#/components/schemas/SuccessResponse')),
                new OA\Response(response: 401, description: 'No autenticado', content: new OA\JsonContent(ref: '#/components/schemas/ErrorResponse')),
                new OA\Response(response: 404, description: 'Tercero no encontrado', content: new OA\JsonContent(ref: '#/components/schemas/ErrorResponse')),
                new OA\Response(response: 422, description: 'Errores de validación', content: new OA\JsonContent(ref: '#/components/schemas/ValidationErrorResponse')),
            ]
        )
    ]
    public function update(ThirdEntryUpdateRequest $request, int $id): JsonResponse
    {
        $e = thirdEntry::findOrFail($id);
        $e->update($request->validated());
        return ApiResponse::success(new ThirdEntryResource($e->fresh()), 'Tercero actualizado.', null, 200);
    }

    #[
        OA\Delete(
            path: '/api/v1/third-entries/{id}',
            summary: 'Eliminar tercero',
            description: 'Elimina un tercero existente. No se puede eliminar si tiene recibos asociados.',
            tags: ['Third Entries'],
            security: [['bearerAuth' => []]],
            parameters: [
                new OA\Parameter(name: 'id', in: 'path', required: true, description: 'ID del tercero', schema: new OA\Schema(type: 'integer')),
            ],
            responses: [
                new OA\Response(response: 200, description: 'Tercero eliminado exitosamente', content: new OA\JsonContent(ref: '#/components/schemas/SuccessResponse')),
                new OA\Response(response: 401, description: 'No autenticado', content: new OA\JsonContent(ref: '#/components/schemas/ErrorResponse')),
                new OA\Response(response: 404, description: 'Tercero no encontrado', content: new OA\JsonContent(ref: '#/components/schemas/ErrorResponse')),
                new OA\Response(response: 422, description: 'No se puede eliminar porque tiene recibos asociados', content: new OA\JsonContent(ref: '#/components/schemas/ValidationErrorResponse')),
            ]
        )
    ]
    public function destroy(int $id): JsonResponse
    {
        $e = thirdEntry::findOrFail($id);
        if (ThirdReceipts::where('third', $id)->exists()) {
            throw ValidationException::withMessages(['id' => ['No se puede eliminar el tercero porque tiene recibos asociados.']]);
        }
        $e->delete();
        return ApiResponse::success(null, 'Eliminado.', null, 200);
    }
}
