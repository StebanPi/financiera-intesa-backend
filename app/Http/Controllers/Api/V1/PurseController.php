<?php

namespace App\Http\Controllers\Api\V1;

use App\Http\Controllers\Controller;
use App\Http\Requests\Api\V1\PurseIndexRequest;
use App\Http\Resources\V1\HistoryPurseResource;
use App\Http\Resources\V1\PurseResource;
use App\Models\Cost;
use App\Models\historyPurse;
use App\Models\Purse;
use App\Support\ApiResponse;
use Illuminate\Http\JsonResponse;
use OpenApi\Attributes as OA;

class PurseController extends Controller
{
    /**
     * GET /purses?cod_alumno=COD&per_page=15&page=1
     * cod_alumno obligatorio (422 si falta).
     */
    #[
        OA\Get(
            path: '/api/v1/purses',
            summary: 'Listar carteras',
            description: 'Lista las carteras de un estudiante específico. Requiere cod_alumno obligatorio.',
            tags: ['Purses'],
            security: [['bearerAuth' => []]],
            parameters: [
                new OA\Parameter(name: 'cod_alumno', in: 'query', required: true, description: 'Código del alumno', schema: new OA\Schema(type: 'string', example: '12345678')),
                new OA\Parameter(name: 'per_page', in: 'query', required: false, description: 'Elementos por página', schema: new OA\Schema(type: 'integer', example: 15)),
            ],
            responses: [
                new OA\Response(response: 200, description: 'Lista de carteras', content: new OA\JsonContent(ref: '#/components/schemas/SuccessResponse')),
                new OA\Response(response: 401, description: 'No autenticado', content: new OA\JsonContent(ref: '#/components/schemas/ErrorResponse')),
                new OA\Response(response: 422, description: 'cod_alumno es obligatorio', content: new OA\JsonContent(ref: '#/components/schemas/ValidationErrorResponse')),
            ]
        )
    ]
    public function index(PurseIndexRequest $request): JsonResponse
    {
        $cost = Cost::where('cod_alumno', $request->cod_alumno)->first();
        if (! $cost) {
            return ApiResponse::success([], null, ['current_page' => 1, 'per_page' => (int) $request->get('per_page', 15), 'total' => 0, 'last_page' => 1], 200);
        }

        $perPage = min((int) $request->get('per_page', 15), 100);
        $paginator = Purse::where('id_cost', $cost->id)->with('cost')->orderBy('fecha_pago')->paginate($perPage);

        return ApiResponse::success(
            PurseResource::collection($paginator->items())->resolve(),
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
     * GET /purses/{id}
     */
    #[
        OA\Get(
            path: '/api/v1/purses/{id}',
            summary: 'Obtener cartera',
            description: 'Obtiene los datos de una cartera específica.',
            tags: ['Purses'],
            security: [['bearerAuth' => []]],
            parameters: [
                new OA\Parameter(name: 'id', in: 'path', required: true, description: 'ID de la cartera', schema: new OA\Schema(type: 'integer')),
            ],
            responses: [
                new OA\Response(response: 200, description: 'Datos de la cartera', content: new OA\JsonContent(ref: '#/components/schemas/SuccessResponse')),
                new OA\Response(response: 401, description: 'No autenticado', content: new OA\JsonContent(ref: '#/components/schemas/ErrorResponse')),
                new OA\Response(response: 404, description: 'Cartera no encontrada', content: new OA\JsonContent(ref: '#/components/schemas/ErrorResponse')),
            ]
        )
    ]
    public function show(int $id): JsonResponse
    {
        $purse = Purse::with('cost')->findOrFail($id);

        return ApiResponse::success(new PurseResource($purse));
    }

    /**
     * GET /purses/{id}/history — HistoryPurse paginado.
     */
    #[
        OA\Get(
            path: '/api/v1/purses/{id}/history',
            summary: 'Obtener historial de cartera',
            description: 'Obtiene el historial de una cartera específica con paginación.',
            tags: ['Purses'],
            security: [['bearerAuth' => []]],
            parameters: [
                new OA\Parameter(name: 'id', in: 'path', required: true, description: 'ID de la cartera', schema: new OA\Schema(type: 'integer')),
                new OA\Parameter(name: 'per_page', in: 'query', required: false, description: 'Elementos por página', schema: new OA\Schema(type: 'integer', example: 15)),
            ],
            responses: [
                new OA\Response(response: 200, description: 'Historial de la cartera', content: new OA\JsonContent(ref: '#/components/schemas/SuccessResponse')),
                new OA\Response(response: 401, description: 'No autenticado', content: new OA\JsonContent(ref: '#/components/schemas/ErrorResponse')),
                new OA\Response(response: 404, description: 'Cartera no encontrada', content: new OA\JsonContent(ref: '#/components/schemas/ErrorResponse')),
            ]
        )
    ]
    public function history(int $id): JsonResponse
    {
        Purse::findOrFail($id);

        $perPage = min((int) request()->get('per_page', 15), 100);
        $paginator = historyPurse::where('id_purse', $id)->orderBy('id')->paginate($perPage);

        return ApiResponse::success(
            HistoryPurseResource::collection($paginator->items())->resolve(),
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
}
