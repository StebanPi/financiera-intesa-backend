<?php

namespace App\Http\Controllers\Api\V1;

use App\Http\Controllers\Controller;
use App\Http\Resources\V1\UserResource;
use App\Models\Role;
use App\Models\User;
use App\Support\ApiResponse;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Illuminate\Validation\Rules;
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
                new OA\Parameter(name: 'search', in: 'query', required: false, description: 'Búsqueda por nombre o email', schema: new OA\Schema(type: 'string')),
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
        $query = User::with('roles')->orderBy('name');

        if ($request->filled('search')) {
            $search = $request->search;
            $query->where(function ($q) use ($search) {
                $q->where('name', 'like', "%{$search}%")
                    ->orWhere('email', 'like', "%{$search}%");
            });
        }

        $users = $query->paginate($perPage);

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

    /**
     * Crear un nuevo usuario. Requiere permission:users.manage.
     */
    public function store(Request $request): JsonResponse
    {
        $request->validate([
            'name' => ['required', 'string', 'max:255'],
            'email' => ['required', 'string', 'email', 'max:255', 'unique:users'],
            'password' => ['required', 'confirmed', Rules\Password::defaults()],
            'roles' => ['nullable', 'array'],
            'roles.*' => ['exists:roles,id'],
        ]);

        $user = User::create([
            'name' => $request->name,
            'email' => $request->email,
            'password' => Hash::make($request->password),
        ]);

        if ($request->has('roles')) {
            $roleSlugs = Role::whereIn('id', $request->roles)->pluck('slug')->toArray();
            $user->syncRoles($roleSlugs);
        } else {
            $user->assignRole('secretaria');
        }

        return ApiResponse::success(new UserResource($user->load('roles')), 'Usuario creado exitosamente.', null, 201);
    }

    /**
     * Actualizar un usuario. Requiere permission:users.manage.
     */
    public function update(Request $request, User $user): JsonResponse
    {
        $request->validate([
            'name' => ['required', 'string', 'max:255'],
            'email' => ['required', 'string', 'email', 'max:255', 'unique:users,email,' . $user->id],
            'password' => ['nullable', 'confirmed', Rules\Password::defaults()],
            'roles' => ['nullable', 'array'],
            'roles.*' => ['exists:roles,id'],
        ]);

        $user->update([
            'name' => $request->name,
            'email' => $request->email,
        ]);

        if ($request->filled('password')) {
            $user->update([
                'password' => Hash::make($request->password),
            ]);
        }

        if ($request->has('roles')) {
            $roleSlugs = Role::whereIn('id', $request->roles)->pluck('slug')->toArray();
            $user->syncRoles($roleSlugs);
        }

        return ApiResponse::success(new UserResource($user->load('roles')), 'Usuario actualizado exitosamente.');
    }

    /**
     * Eliminar un usuario. Requiere permission:users.manage.
     */
    public function destroy(User $user): JsonResponse
    {
        // No permitir eliminar al último super-admin
        if ($user->hasRole('super-admin')) {
            $superAdminCount = User::whereHas('roles', function ($query) {
                $query->where('slug', 'super-admin');
            })->count();

            if ($superAdminCount <= 1) {
                return ApiResponse::error('FORBIDDEN', 'No se puede eliminar al último Super Administrador.', null, 403);
            }
        }

        $user->delete();

        return ApiResponse::success(null, 'Usuario eliminado exitosamente.');
    }
}
