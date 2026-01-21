<?php

namespace App\Http\Controllers\Api\V1;

use App\Http\Controllers\Controller;
use App\Http\Resources\V1\PermissionResource;
use App\Models\Permission;
use App\Support\ApiResponse;
use Illuminate\Http\JsonResponse;
use OpenApi\Attributes as OA;

class PermissionController extends Controller
{
    /**
     * Lista todos los permisos (sin paginar; suelen ser pocos). Read-only.
     */
    #[
        OA\Get(
            path: '/api/v1/admin/permissions',
            summary: 'Listar permisos',
            description: 'Lista todos los permisos disponibles (sin paginar; suelen ser pocos). Solo lectura. Requiere permisos de administración de roles.',
            tags: ['Admin'],
            security: [['bearerAuth' => []]],
            responses: [
                new OA\Response(response: 200, description: 'Lista de permisos', content: new OA\JsonContent(ref: '#/components/schemas/SuccessResponse')),
                new OA\Response(response: 401, description: 'No autenticado', content: new OA\JsonContent(ref: '#/components/schemas/ErrorResponse')),
                new OA\Response(response: 403, description: 'Sin permisos', content: new OA\JsonContent(ref: '#/components/schemas/ErrorResponse')),
            ]
        )
    ]
    public function index(): JsonResponse
    {
        $permissions = Permission::orderBy('name')->get();

        return ApiResponse::success(PermissionResource::collection($permissions)->resolve());
    }
}
