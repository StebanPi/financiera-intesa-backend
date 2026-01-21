<?php

namespace App\Http\Controllers\Api\V1;

use App\Http\Controllers\Controller;
use App\Http\Requests\Api\V1\EntryStoreRequest;
use App\Http\Resources\V1\EntryResource;
use App\Models\Cost;
use App\Models\Entry;
use App\Services\EntryService;
use App\Support\ApiResponse;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use OpenApi\Attributes as OA;

class EntryController extends Controller
{
    public function __construct(
        private EntryService $entryService
    ) {}

    /**
     * GET /entries?cod_alumno=...&id_cost=...&from=YYYY-MM-DD&to=YYYY-MM-DD
     */
    #[
        OA\Get(
            path: '/api/v1/entries',
            summary: 'Listar abonos',
            description: 'Lista los abonos (entries) con filtros opcionales y paginación.',
            tags: ['Entries'],
            security: [['bearerAuth' => []]],
            parameters: [
                new OA\Parameter(name: 'cod_alumno', in: 'query', required: false, description: 'Código del alumno', schema: new OA\Schema(type: 'string')),
                new OA\Parameter(name: 'id_cost', in: 'query', required: false, description: 'ID del costo', schema: new OA\Schema(type: 'integer')),
                new OA\Parameter(name: 'from', in: 'query', required: false, description: 'Fecha desde (YYYY-MM-DD)', schema: new OA\Schema(type: 'string', format: 'date')),
                new OA\Parameter(name: 'to', in: 'query', required: false, description: 'Fecha hasta (YYYY-MM-DD)', schema: new OA\Schema(type: 'string', format: 'date')),
                new OA\Parameter(name: 'per_page', in: 'query', required: false, description: 'Elementos por página', schema: new OA\Schema(type: 'integer', example: 15)),
            ],
            responses: [
                new OA\Response(response: 200, description: 'Lista de abonos', content: new OA\JsonContent(ref: '#/components/schemas/SuccessResponse')),
                new OA\Response(response: 401, description: 'No autenticado', content: new OA\JsonContent(ref: '#/components/schemas/ErrorResponse')),
            ]
        )
    ]
    public function index(Request $request): JsonResponse
    {
        $query = Entry::query();

        if ($request->filled('cod_alumno')) {
            $cost = Cost::where('cod_alumno', $request->cod_alumno)->first();
            if ($cost) {
                $query->where('id_cost', $cost->id);
            } else {
                $query->whereRaw('1 = 0');
            }
        }
        if ($request->filled('id_cost')) {
            $query->where('id_cost', $request->id_cost);
        }
        if ($request->filled('from')) {
            $query->whereDate('fecha_recibo', '>=', $request->from);
        }
        if ($request->filled('to')) {
            $query->whereDate('fecha_recibo', '<=', $request->to);
        }

        $perPage = min((int) $request->get('per_page', 15), 100);
        $paginator = $query->orderBy('id')->paginate($perPage);

        return ApiResponse::success(
            EntryResource::collection($paginator->items())->resolve(),
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
     * POST /entries — asigna no_recibo con consecutivo (lockForUpdate).
     */
    #[
        OA\Post(
            path: '/api/v1/entries',
            summary: 'Crear abono',
            description: 'Crea un nuevo abono (entry). Asigna automáticamente el número de recibo usando un consecutivo con lockForUpdate.',
            tags: ['Entries'],
            security: [['bearerAuth' => []]],
            requestBody: new OA\RequestBody(
                required: true,
                content: new OA\JsonContent(
                    required: ['id_cost', 'concepto', 'descripcion', 'fecha_recibo', 'valor', 'elaborado_por', 'debe', 'haber'],
                    properties: [
                        new OA\Property(property: 'id_cost', type: 'integer', example: 1),
                        new OA\Property(property: 'concepto', type: 'integer', example: 1),
                        new OA\Property(property: 'descripcion', type: 'string', example: 'Abono semestral'),
                        new OA\Property(property: 'fecha_recibo', type: 'string', format: 'date', example: '2024-01-15'),
                        new OA\Property(property: 'valor', type: 'number', format: 'float', example: 500000),
                        new OA\Property(property: 'elaborado_por', type: 'integer', example: 1),
                        new OA\Property(property: 'debe', type: 'integer', example: 1),
                        new OA\Property(property: 'haber', type: 'integer', example: 1),
                        new OA\Property(property: 'forma', type: 'string', enum: ['Efectivo', 'Bancos', 'Consignación'], nullable: true, example: 'Efectivo'),
                    ]
                )
            ),
            responses: [
                new OA\Response(response: 201, description: 'Abono creado exitosamente', content: new OA\JsonContent(ref: '#/components/schemas/SuccessResponse')),
                new OA\Response(response: 401, description: 'No autenticado', content: new OA\JsonContent(ref: '#/components/schemas/ErrorResponse')),
                new OA\Response(response: 422, description: 'Errores de validación', content: new OA\JsonContent(ref: '#/components/schemas/ValidationErrorResponse')),
            ]
        )
    ]
    public function store(EntryStoreRequest $request): JsonResponse
    {
        $entry = $this->entryService->create($request->validated());

        return ApiResponse::success(new EntryResource($entry), 'Abono creado.', null, 201);
    }

    /**
     * GET /entries/{id}
     */
    #[
        OA\Get(
            path: '/api/v1/entries/{id}',
            summary: 'Obtener abono',
            description: 'Obtiene los datos de un abono específico.',
            tags: ['Entries'],
            security: [['bearerAuth' => []]],
            parameters: [
                new OA\Parameter(name: 'id', in: 'path', required: true, description: 'ID del abono', schema: new OA\Schema(type: 'integer')),
            ],
            responses: [
                new OA\Response(response: 200, description: 'Datos del abono', content: new OA\JsonContent(ref: '#/components/schemas/SuccessResponse')),
                new OA\Response(response: 401, description: 'No autenticado', content: new OA\JsonContent(ref: '#/components/schemas/ErrorResponse')),
                new OA\Response(response: 404, description: 'Abono no encontrado', content: new OA\JsonContent(ref: '#/components/schemas/ErrorResponse')),
            ]
        )
    ]
    public function show(int $id): JsonResponse
    {
        $entry = Entry::findOrFail($id);

        return ApiResponse::success(new EntryResource($entry));
    }

    /**
     * DELETE /entries/{id}
     */
    #[
        OA\Delete(
            path: '/api/v1/entries/{id}',
            summary: 'Eliminar abono',
            description: 'Elimina un abono existente.',
            tags: ['Entries'],
            security: [['bearerAuth' => []]],
            parameters: [
                new OA\Parameter(name: 'id', in: 'path', required: true, description: 'ID del abono', schema: new OA\Schema(type: 'integer')),
            ],
            responses: [
                new OA\Response(response: 200, description: 'Abono eliminado exitosamente', content: new OA\JsonContent(ref: '#/components/schemas/SuccessResponse')),
                new OA\Response(response: 401, description: 'No autenticado', content: new OA\JsonContent(ref: '#/components/schemas/ErrorResponse')),
                new OA\Response(response: 404, description: 'Abono no encontrado', content: new OA\JsonContent(ref: '#/components/schemas/ErrorResponse')),
            ]
        )
    ]
    public function destroy(int $id): JsonResponse
    {
        $this->entryService->delete($id);

        return ApiResponse::success(null, 'Eliminado.', null, 200);
    }
}
