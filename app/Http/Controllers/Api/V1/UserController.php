<?php

namespace App\Http\Controllers\Api\V1;

use App\Http\Controllers\Controller;
use App\Http\Resources\V1\UserResource;
use App\Models\User;
use App\Support\ApiResponse;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use OpenApi\Attributes as OA;

class UserController extends Controller
{
    /**
     * Listar usuarios (paginado). Requiere permission:users.manage.
     */
    #[
        OA\Get(
            path: '/api/v1/admin/users',
            summary: 'Listar usuarios',
            description: 'Lista los usuarios con paginación. Requiere permisos de administración de usuarios.',
            tags: ['Admin'],
            security: [['bearerAuth' => []]],
            parameters: [
                new OA\Parameter(name: 'per_page', in: 'query', required: false, description: 'Elementos por página', schema: new OA\Schema(type: 'integer', example: 15)),
            ],
            responses: [
                new OA\Response(response: 200, description: 'Lista de usuarios', content: new OA\JsonContent(ref: '#/components/schemas/SuccessResponse')),
                new OA\Response(response: 401, description: 'No autenticado', content: new OA\JsonContent(ref: '#/components/schemas/ErrorResponse')),
                new OA\Response(response: 403, description: 'Sin permisos', content: new OA\JsonContent(ref: '#/components/schemas/ErrorResponse')),
            ]
        )
    ]
    public function index(Request $request): JsonResponse
    {
        $perPage = min((int) $request->get('per_page', 15), 100);
        $users = User::with('roles')->orderBy('name')->paginate($perPage);

        return ApiResponse::success(
            UserResource::collection($users->items())->resolve(),
            null,
            [
                'current_page' => $users->currentPage(),
                'per_page' => $users->perPage(),
                'total' => $users->total(),
                'last_page' => $users->lastPage(),
            ],
            200
        );
    }

    /**
     * Ver un usuario. Requiere permission:users.manage.
     */
    #[
        OA\Get(
            path: '/api/v1/admin/users/{user}',
            summary: 'Obtener usuario',
            description: 'Obtiene los datos de un usuario específico incluyendo roles y permisos. Requiere permisos de administración de usuarios.',
            tags: ['Admin'],
            security: [['bearerAuth' => []]],
            parameters: [
                new OA\Parameter(name: 'user', in: 'path', required: true, description: 'ID del usuario', schema: new OA\Schema(type: 'integer')),
            ],
            responses: [
                new OA\Response(response: 200, description: 'Datos del usuario', content: new OA\JsonContent(ref: '#/components/schemas/SuccessResponse')),
                new OA\Response(response: 401, description: 'No autenticado', content: new OA\JsonContent(ref: '#/components/schemas/ErrorResponse')),
                new OA\Response(response: 403, description: 'Sin permisos', content: new OA\JsonContent(ref: '#/components/schemas/ErrorResponse')),
                new OA\Response(response: 404, description: 'Usuario no encontrado', content: new OA\JsonContent(ref: '#/components/schemas/ErrorResponse')),
            ]
        )
    ]
    public function show(User $user): JsonResponse
    {
        $user->load('roles.permissions');

        return ApiResponse::success(new UserResource($user));
    }
}
