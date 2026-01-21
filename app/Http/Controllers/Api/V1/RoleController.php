<?php

namespace App\Http\Controllers\Api\V1;

use App\Http\Controllers\Controller;
use App\Http\Requests\Api\V1\RoleStoreRequest;
use App\Http\Requests\Api\V1\RoleUpdateRequest;
use App\Http\Requests\Api\V1\SyncRolePermissionsRequest;
use App\Http\Resources\V1\RoleResource;
use App\Models\Permission;
use App\Models\Role;
use App\Support\ApiResponse;
use Illuminate\Http\JsonResponse;
use OpenApi\Attributes as OA;

class RoleController extends Controller
{
    /**
     * Lista roles con sus permisos.
     */
    #[
        OA\Get(
            path: '/api/v1/admin/roles',
            summary: 'Listar roles',
            description: 'Lista todos los roles con sus permisos asociados. Requiere permisos de administración de roles.',
            tags: ['Admin'],
            security: [['bearerAuth' => []]],
            responses: [
                new OA\Response(response: 200, description: 'Lista de roles', content: new OA\JsonContent(ref: '#/components/schemas/SuccessResponse')),
                new OA\Response(response: 401, description: 'No autenticado', content: new OA\JsonContent(ref: '#/components/schemas/ErrorResponse')),
                new OA\Response(response: 403, description: 'Sin permisos', content: new OA\JsonContent(ref: '#/components/schemas/ErrorResponse')),
            ]
        )
    ]
    public function index(): JsonResponse
    {
        $roles = Role::with('permissions')->orderBy('name')->get();

        return ApiResponse::success(RoleResource::collection($roles)->resolve());
    }

    /**
     * Crea un rol.
     */
    #[
        OA\Post(
            path: '/api/v1/admin/roles',
            summary: 'Crear rol',
            description: 'Crea un nuevo rol. Requiere permisos de administración de roles.',
            tags: ['Admin'],
            security: [['bearerAuth' => []]],
            requestBody: new OA\RequestBody(
                required: true,
                content: new OA\JsonContent(
                    required: ['name', 'slug'],
                    properties: [
                        new OA\Property(property: 'name', type: 'string', example: 'Secretaria'),
                        new OA\Property(property: 'slug', type: 'string', example: 'secretaria'),
                    ]
                )
            ),
            responses: [
                new OA\Response(response: 201, description: 'Rol creado exitosamente', content: new OA\JsonContent(ref: '#/components/schemas/SuccessResponse')),
                new OA\Response(response: 401, description: 'No autenticado', content: new OA\JsonContent(ref: '#/components/schemas/ErrorResponse')),
                new OA\Response(response: 403, description: 'Sin permisos', content: new OA\JsonContent(ref: '#/components/schemas/ErrorResponse')),
                new OA\Response(response: 422, description: 'Errores de validación', content: new OA\JsonContent(ref: '#/components/schemas/ValidationErrorResponse')),
            ]
        )
    ]
    public function store(RoleStoreRequest $request): JsonResponse
    {
        $role = Role::create($request->validated());

        return ApiResponse::success(new RoleResource($role->load('permissions')), 'Rol creado.', null, 201);
    }

    /**
     * Muestra un rol con sus permisos.
     */
    #[
        OA\Get(
            path: '/api/v1/admin/roles/{role}',
            summary: 'Obtener rol',
            description: 'Obtiene los datos de un rol específico incluyendo sus permisos. Requiere permisos de administración de roles.',
            tags: ['Admin'],
            security: [['bearerAuth' => []]],
            parameters: [
                new OA\Parameter(name: 'role', in: 'path', required: true, description: 'ID del rol', schema: new OA\Schema(type: 'integer')),
            ],
            responses: [
                new OA\Response(response: 200, description: 'Datos del rol', content: new OA\JsonContent(ref: '#/components/schemas/SuccessResponse')),
                new OA\Response(response: 401, description: 'No autenticado', content: new OA\JsonContent(ref: '#/components/schemas/ErrorResponse')),
                new OA\Response(response: 403, description: 'Sin permisos', content: new OA\JsonContent(ref: '#/components/schemas/ErrorResponse')),
                new OA\Response(response: 404, description: 'Rol no encontrado', content: new OA\JsonContent(ref: '#/components/schemas/ErrorResponse')),
            ]
        )
    ]
    public function show(Role $role): JsonResponse
    {
        $role->load('permissions');

        return ApiResponse::success(new RoleResource($role));
    }

    /**
     * Actualiza un rol.
     */
    #[
        OA\Put(
            path: '/api/v1/admin/roles/{role}',
            summary: 'Actualizar rol',
            description: 'Actualiza los datos de un rol existente. Requiere permisos de administración de roles.',
            tags: ['Admin'],
            security: [['bearerAuth' => []]],
            parameters: [
                new OA\Parameter(name: 'role', in: 'path', required: true, description: 'ID del rol', schema: new OA\Schema(type: 'integer')),
            ],
            requestBody: new OA\RequestBody(
                required: false,
                content: new OA\JsonContent(
                    properties: [
                        new OA\Property(property: 'name', type: 'string', nullable: true),
                        new OA\Property(property: 'slug', type: 'string', nullable: true),
                    ]
                )
            ),
            responses: [
                new OA\Response(response: 200, description: 'Rol actualizado exitosamente', content: new OA\JsonContent(ref: '#/components/schemas/SuccessResponse')),
                new OA\Response(response: 401, description: 'No autenticado', content: new OA\JsonContent(ref: '#/components/schemas/ErrorResponse')),
                new OA\Response(response: 403, description: 'Sin permisos', content: new OA\JsonContent(ref: '#/components/schemas/ErrorResponse')),
                new OA\Response(response: 404, description: 'Rol no encontrado', content: new OA\JsonContent(ref: '#/components/schemas/ErrorResponse')),
                new OA\Response(response: 422, description: 'Errores de validación', content: new OA\JsonContent(ref: '#/components/schemas/ValidationErrorResponse')),
            ]
        )
    ]
    public function update(RoleUpdateRequest $request, Role $role): JsonResponse
    {
        $role->update($request->validated());

        return ApiResponse::success(new RoleResource($role->load('permissions')), 'Rol actualizado.', null, 200);
    }

    /**
     * Elimina un rol.
     */
    #[
        OA\Delete(
            path: '/api/v1/admin/roles/{role}',
            summary: 'Eliminar rol',
            description: 'Elimina un rol existente. Requiere permisos de administración de roles.',
            tags: ['Admin'],
            security: [['bearerAuth' => []]],
            parameters: [
                new OA\Parameter(name: 'role', in: 'path', required: true, description: 'ID del rol', schema: new OA\Schema(type: 'integer')),
            ],
            responses: [
                new OA\Response(response: 200, description: 'Rol eliminado exitosamente', content: new OA\JsonContent(ref: '#/components/schemas/SuccessResponse')),
                new OA\Response(response: 401, description: 'No autenticado', content: new OA\JsonContent(ref: '#/components/schemas/ErrorResponse')),
                new OA\Response(response: 403, description: 'Sin permisos', content: new OA\JsonContent(ref: '#/components/schemas/ErrorResponse')),
                new OA\Response(response: 404, description: 'Rol no encontrado', content: new OA\JsonContent(ref: '#/components/schemas/ErrorResponse')),
            ]
        )
    ]
    public function destroy(Role $role): JsonResponse
    {
        $role->delete();

        return ApiResponse::success(null, 'Rol eliminado.', null, 200);
    }

    /**
     * Sincroniza los permisos del rol. Reemplaza todos los permisos actuales.
     */
    #[
        OA\Post(
            path: '/api/v1/admin/roles/{role}/permissions',
            summary: 'Sincronizar permisos del rol',
            description: 'Sincroniza los permisos del rol. Reemplaza todos los permisos actuales por los especificados. Requiere permisos de administración de roles.',
            tags: ['Admin'],
            security: [['bearerAuth' => []]],
            parameters: [
                new OA\Parameter(name: 'role', in: 'path', required: true, description: 'ID del rol', schema: new OA\Schema(type: 'integer')),
            ],
            requestBody: new OA\RequestBody(
                required: true,
                content: new OA\JsonContent(
                    required: ['permissions'],
                    properties: [
                        new OA\Property(property: 'permissions', type: 'array', items: new OA\Items(type: 'string'), example: ['access.core', 'users.manage']),
                    ]
                )
            ),
            responses: [
                new OA\Response(response: 200, description: 'Permisos sincronizados exitosamente', content: new OA\JsonContent(ref: '#/components/schemas/SuccessResponse')),
                new OA\Response(response: 401, description: 'No autenticado', content: new OA\JsonContent(ref: '#/components/schemas/ErrorResponse')),
                new OA\Response(response: 403, description: 'Sin permisos', content: new OA\JsonContent(ref: '#/components/schemas/ErrorResponse')),
                new OA\Response(response: 404, description: 'Rol no encontrado', content: new OA\JsonContent(ref: '#/components/schemas/ErrorResponse')),
                new OA\Response(response: 422, description: 'Errores de validación', content: new OA\JsonContent(ref: '#/components/schemas/ValidationErrorResponse')),
            ]
        )
    ]
    public function syncPermissions(SyncRolePermissionsRequest $request, Role $role): JsonResponse
    {
        $ids = Permission::whereIn('slug', $request->validated('permissions'))->pluck('id');
        $role->permissions()->sync($ids);

        return ApiResponse::success(
            new RoleResource($role->load('permissions')),
            'Permisos del rol sincronizados.',
            null,
            200
        );
    }
}
