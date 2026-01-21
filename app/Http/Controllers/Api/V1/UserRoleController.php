<?php

namespace App\Http\Controllers\Api\V1;

use App\Http\Controllers\Controller;
use App\Http\Requests\Api\V1\SyncUserRolesRequest;
use App\Http\Resources\V1\UserResource;
use App\Models\User;
use App\Support\ApiResponse;
use Illuminate\Http\JsonResponse;
use OpenApi\Attributes as OA;

class UserRoleController extends Controller
{
    /**
     * Sincroniza los roles del usuario. Reemplaza todos los roles actuales.
     */
    #[
        OA\Post(
            path: '/api/v1/admin/users/{user}/roles',
            summary: 'Sincronizar roles del usuario',
            description: 'Sincroniza los roles del usuario. Reemplaza todos los roles actuales por los especificados. Requiere permisos de administración de roles.',
            tags: ['Admin'],
            security: [['bearerAuth' => []]],
            parameters: [
                new OA\Parameter(name: 'user', in: 'path', required: true, description: 'ID del usuario', schema: new OA\Schema(type: 'integer')),
            ],
            requestBody: new OA\RequestBody(
                required: true,
                content: new OA\JsonContent(
                    required: ['roles'],
                    properties: [
                        new OA\Property(property: 'roles', type: 'array', items: new OA\Items(type: 'string'), example: ['secretaria', 'admin']),
                    ]
                )
            ),
            responses: [
                new OA\Response(response: 200, description: 'Roles sincronizados exitosamente', content: new OA\JsonContent(ref: '#/components/schemas/SuccessResponse')),
                new OA\Response(response: 401, description: 'No autenticado', content: new OA\JsonContent(ref: '#/components/schemas/ErrorResponse')),
                new OA\Response(response: 403, description: 'Sin permisos', content: new OA\JsonContent(ref: '#/components/schemas/ErrorResponse')),
                new OA\Response(response: 404, description: 'Usuario no encontrado', content: new OA\JsonContent(ref: '#/components/schemas/ErrorResponse')),
                new OA\Response(response: 422, description: 'Errores de validación', content: new OA\JsonContent(ref: '#/components/schemas/ValidationErrorResponse')),
            ]
        )
    ]
    public function syncRoles(SyncUserRolesRequest $request, User $user): JsonResponse
    {
        $user->syncRoles($request->validated('roles'));
        $user->load('roles.permissions');

        return ApiResponse::success(
            new UserResource($user),
            'Roles del usuario sincronizados.',
            null,
            200
        );
    }
}
