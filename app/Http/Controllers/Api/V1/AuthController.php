<?php

namespace App\Http\Controllers\Api\V1;

use App\Http\Controllers\Controller;
use App\Http\Requests\Api\V1\ForgotPasswordRequest;
use App\Http\Requests\Api\V1\LoginRequest;
use App\Http\Requests\Api\V1\RegisterRequest;
use App\Http\Requests\Api\V1\ResendVerificationRequest;
use App\Http\Requests\Api\V1\ResetPasswordRequest;
use App\Http\Requests\Api\V1\VerifyEmailRequest;
use App\Http\Resources\V1\UserResource;
use App\Models\Role;
use App\Models\User;
use App\Support\ApiResponse;
use Illuminate\Auth\Events\Verified;
use Illuminate\Http\JsonResponse;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Password;
use OpenApi\Attributes as OA;

class AuthController extends Controller
{
    /**
     * Login con email y contraseña. Devuelve token Bearer y usuario.
     */
    #[
        OA\Post(
            path: '/api/v1/auth/login',
            summary: 'Login',
            description: 'Autentica un usuario con email y contraseña. Devuelve un token Bearer y los datos del usuario.',
            tags: ['Auth'],
            security: [],
            requestBody: new OA\RequestBody(
                required: true,
                content: new OA\JsonContent(
                    required: ['email', 'password'],
                    properties: [
                        new OA\Property(property: 'email', type: 'string', format: 'email', example: 'user@example.com'),
                        new OA\Property(property: 'password', type: 'string', format: 'password', example: 'password'),
                    ]
                )
            ),
            responses: [
                new OA\Response(
                    response: 200,
                    description: 'Login exitoso',
                    content: new OA\JsonContent(
                        properties: [
                            new OA\Property(property: 'data', type: 'object', properties: [
                                new OA\Property(property: 'token', type: 'string', example: '1|abc123def456...'),
                                new OA\Property(property: 'user', type: 'object', properties: [
                                    new OA\Property(property: 'id', type: 'integer', example: 1),
                                    new OA\Property(property: 'name', type: 'string', example: 'Usuario Ejemplo'),
                                    new OA\Property(property: 'email', type: 'string', format: 'email', example: 'user@example.com'),
                                    new OA\Property(property: 'email_verified_at', type: 'string', format: 'date-time', nullable: true),
                                    new OA\Property(property: 'roles', type: 'array', items: new OA\Items(type: 'object', properties: [
                                        new OA\Property(property: 'id', type: 'integer', example: 1),
                                        new OA\Property(property: 'name', type: 'string', example: 'Secretaria'),
                                        new OA\Property(property: 'slug', type: 'string', example: 'secretaria'),
                                    ])),
                                    new OA\Property(property: 'permissions', type: 'array', items: new OA\Items(type: 'string', example: 'access.core')),
                                ]),
                            ]),
                        ]
                    )
                ),
                new OA\Response(
                    response: 401,
                    description: 'Credenciales incorrectas',
                    content: new OA\JsonContent(ref: '#/components/schemas/ErrorResponse')
                ),
                new OA\Response(
                    response: 422,
                    description: 'Errores de validación',
                    content: new OA\JsonContent(ref: '#/components/schemas/ValidationErrorResponse')
                ),
            ]
        )
    ]
    public function login(LoginRequest $request): JsonResponse
    {
        if (!Auth::attempt($request->only('email', 'password'))) {
            return ApiResponse::error('UNAUTHORIZED', 'Credenciales incorrectas.', null, 401);
        }

        $user = Auth::user();
        $user->load(['roles.permissions']);
        $token = $user->createToken('auth')->plainTextToken;

        return ApiResponse::success([
            'token' => $token,
            'user' => new UserResource($user),
        ], null, null, 200);
    }

    /**
     * Cerrar sesión: revoca el token actual.
     */
    #[
        OA\Post(
            path: '/api/v1/auth/logout',
            summary: 'Logout',
            description: 'Cierra la sesión del usuario revocando el token Bearer actual.',
            tags: ['Auth'],
            security: [['bearerAuth' => []]],
            responses: [
                new OA\Response(
                    response: 200,
                    description: 'Sesión cerrada exitosamente',
                    content: new OA\JsonContent(
                        properties: [
                            new OA\Property(property: 'data', nullable: true),
                            new OA\Property(property: 'message', type: 'string', example: 'Sesión cerrada.'),
                        ]
                    )
                ),
                new OA\Response(response: 401, description: 'No autenticado', content: new OA\JsonContent(ref: '#/components/schemas/ErrorResponse')),
            ]
        )
    ]
    public function logout(\Illuminate\Http\Request $request): JsonResponse
    {
        $request->user()?->currentAccessToken()?->delete();

        return ApiResponse::success(null, 'Sesión cerrada.', null, 200);
    }

    /**
     * Perfil del usuario autenticado (roles y permisos).
     */
    #[
        OA\Get(
            path: '/api/v1/auth/me',
            summary: 'Obtener perfil del usuario',
            description: 'Devuelve los datos del usuario autenticado incluyendo roles y permisos.',
            tags: ['Auth'],
            security: [['bearerAuth' => []]],
            responses: [
                new OA\Response(
                    response: 200,
                    description: 'Perfil del usuario',
                    content: new OA\JsonContent(
                        properties: [
                            new OA\Property(property: 'data', type: 'object', properties: [
                                new OA\Property(property: 'id', type: 'integer', example: 1),
                                new OA\Property(property: 'name', type: 'string', example: 'Usuario Ejemplo'),
                                new OA\Property(property: 'email', type: 'string', format: 'email', example: 'user@example.com'),
                                new OA\Property(property: 'email_verified_at', type: 'string', format: 'date-time', nullable: true),
                                new OA\Property(property: 'roles', type: 'array', items: new OA\Items(type: 'object', properties: [
                                    new OA\Property(property: 'id', type: 'integer', example: 1),
                                    new OA\Property(property: 'name', type: 'string', example: 'Secretaria'),
                                    new OA\Property(property: 'slug', type: 'string', example: 'secretaria'),
                                ])),
                                new OA\Property(property: 'permissions', type: 'array', items: new OA\Items(type: 'string', example: 'access.core')),
                            ]),
                        ]
                    )
                ),
                new OA\Response(response: 401, description: 'No autenticado', content: new OA\JsonContent(ref: '#/components/schemas/ErrorResponse')),
            ]
        )
    ]
    public function me(\Illuminate\Http\Request $request): JsonResponse
    {
        $user = $request->user()->load(['roles.permissions']);

        return ApiResponse::success(new UserResource($user));
    }

    /**
     * Registro. Crea usuario, asigna rol (super-admin si no hay ninguno, si no secretaria). Devuelve token + user.
     */
    #[
        OA\Post(
            path: '/api/v1/auth/register',
            summary: 'Registro de usuario',
            description: 'Crea un nuevo usuario. Si no existe ningún super-admin, asigna el rol super-admin. Si no, asigna el rol secretaria. Devuelve un token Bearer y los datos del usuario.',
            tags: ['Auth'],
            security: [],
            requestBody: new OA\RequestBody(
                required: true,
                content: new OA\JsonContent(
                    required: ['name', 'email', 'password', 'password_confirmation'],
                    properties: [
                        new OA\Property(property: 'name', type: 'string', example: 'Usuario Ejemplo'),
                        new OA\Property(property: 'email', type: 'string', format: 'email', example: 'user@example.com'),
                        new OA\Property(property: 'password', type: 'string', format: 'password', example: 'password'),
                        new OA\Property(property: 'password_confirmation', type: 'string', format: 'password', example: 'password'),
                    ]
                )
            ),
            responses: [
                new OA\Response(
                    response: 201,
                    description: 'Usuario registrado exitosamente',
                    content: new OA\JsonContent(
                        properties: [
                            new OA\Property(property: 'data', type: 'object', properties: [
                                new OA\Property(property: 'token', type: 'string', example: '1|abc123def456...'),
                                new OA\Property(property: 'user', type: 'object', properties: [
                                    new OA\Property(property: 'id', type: 'integer', example: 1),
                                    new OA\Property(property: 'name', type: 'string', example: 'Usuario Ejemplo'),
                                    new OA\Property(property: 'email', type: 'string', format: 'email', example: 'user@example.com'),
                                    new OA\Property(property: 'email_verified_at', type: 'string', format: 'date-time', nullable: true),
                                    new OA\Property(property: 'roles', type: 'array', items: new OA\Items(type: 'object')),
                                    new OA\Property(property: 'permissions', type: 'array', items: new OA\Items(type: 'string')),
                                ]),
                            ]),
                            new OA\Property(property: 'message', type: 'string', example: 'Usuario registrado.'),
                        ]
                    )
                ),
                new OA\Response(response: 422, description: 'Errores de validación', content: new OA\JsonContent(ref: '#/components/schemas/ValidationErrorResponse')),
            ]
        )
    ]
    public function register(RegisterRequest $request): JsonResponse
    {
        $user = User::create([
            'name' => $request->name,
            'email' => $request->email,
            'password' => Hash::make($request->password),
        ]);

        $superAdminRole = Role::where('slug', 'super-admin')->first();
        $hasSuperAdmin = User::whereHas('roles', fn ($q) => $q->where('slug', 'super-admin'))->exists();

        if (!$hasSuperAdmin && $superAdminRole) {
            $user->assignRole('super-admin');
        } else {
            $secretaria = Role::where('slug', 'secretaria')->first();
            if ($secretaria) {
                $user->assignRole('secretaria');
            }
        }

        $user->load(['roles.permissions']);
        $token = $user->createToken('auth')->plainTextToken;

        return ApiResponse::success([
            'token' => $token,
            'user' => new UserResource($user),
        ], 'Usuario registrado.', null, 201);
    }

    /**
     * Envía enlace para restablecer contraseña. Responde 200 siempre (no se filtra si el email existe).
     */
    #[
        OA\Post(
            path: '/api/v1/auth/forgot-password',
            summary: 'Solicitar restablecimiento de contraseña',
            description: 'Envía un enlace por correo electrónico para restablecer la contraseña. Siempre responde 200 para no revelar si el email existe en el sistema.',
            tags: ['Auth'],
            security: [],
            requestBody: new OA\RequestBody(
                required: true,
                content: new OA\JsonContent(
                    required: ['email'],
                    properties: [
                        new OA\Property(property: 'email', type: 'string', format: 'email', example: 'user@example.com'),
                    ]
                )
            ),
            responses: [
                new OA\Response(
                    response: 200,
                    description: 'Solicitud procesada',
                    content: new OA\JsonContent(
                        properties: [
                            new OA\Property(property: 'data', nullable: true),
                            new OA\Property(property: 'message', type: 'string', example: 'Si el correo existe, recibirás un enlace para restablecer tu contraseña.'),
                        ]
                    )
                ),
                new OA\Response(response: 422, description: 'Errores de validación', content: new OA\JsonContent(ref: '#/components/schemas/ValidationErrorResponse')),
            ]
        )
    ]
    public function forgotPassword(ForgotPasswordRequest $request): JsonResponse
    {
        Password::sendResetLink($request->only('email'));

        return ApiResponse::success(null, 'Si el correo existe, recibirás un enlace para restablecer tu contraseña.', null, 200);
    }

    /**
     * Restablece la contraseña con token. 200 si ok, 422 con VALIDATION_ERROR si token/email inválidos.
     */
    #[
        OA\Post(
            path: '/api/v1/auth/reset-password',
            summary: 'Restablecer contraseña',
            description: 'Restablece la contraseña del usuario usando el token recibido por correo electrónico.',
            tags: ['Auth'],
            security: [],
            requestBody: new OA\RequestBody(
                required: true,
                content: new OA\JsonContent(
                    required: ['token', 'email', 'password', 'password_confirmation'],
                    properties: [
                        new OA\Property(property: 'token', type: 'string', example: 'reset-token-here'),
                        new OA\Property(property: 'email', type: 'string', format: 'email', example: 'user@example.com'),
                        new OA\Property(property: 'password', type: 'string', format: 'password', example: 'newpassword'),
                        new OA\Property(property: 'password_confirmation', type: 'string', format: 'password', example: 'newpassword'),
                    ]
                )
            ),
            responses: [
                new OA\Response(
                    response: 200,
                    description: 'Contraseña restablecida exitosamente',
                    content: new OA\JsonContent(
                        properties: [
                            new OA\Property(property: 'data', nullable: true),
                            new OA\Property(property: 'message', type: 'string', example: 'Contraseña restablecida correctamente.'),
                        ]
                    )
                ),
                new OA\Response(response: 422, description: 'Errores de validación (token inválido/expirado, etc.)', content: new OA\JsonContent(ref: '#/components/schemas/ValidationErrorResponse')),
            ]
        )
    ]
    public function resetPassword(ResetPasswordRequest $request): JsonResponse
    {
        $status = Password::reset(
            $request->only('email', 'password', 'password_confirmation', 'token'),
            fn ($user, $password) => $user->forceFill(['password' => Hash::make($password)])->save()
        );

        if ($status === Password::PASSWORD_RESET) {
            return ApiResponse::success(null, 'Contraseña restablecida correctamente.', null, 200);
        }

        return ApiResponse::error('VALIDATION_ERROR', __($status), ['email' => [__($status)]], 422);
    }

    /**
     * Verifica el email con enlace firmado (id, hash, expires, signature). Requiere User con MustVerifyEmail.
     */
    #[
        OA\Get(
            path: '/api/v1/auth/verify-email',
            summary: 'Verificar email',
            description: 'Verifica el email del usuario usando un enlace firmado. Requiere parámetros id, hash, expires y signature en la URL.',
            tags: ['Auth'],
            security: [],
            parameters: [
                new OA\Parameter(name: 'id', in: 'query', required: true, description: 'ID del usuario', schema: new OA\Schema(type: 'integer', example: 1)),
                new OA\Parameter(name: 'hash', in: 'query', required: true, description: 'Hash del email', schema: new OA\Schema(type: 'string', example: 'abc123...')),
                new OA\Parameter(name: 'expires', in: 'query', required: true, description: 'Timestamp de expiración', schema: new OA\Schema(type: 'integer', example: 1234567890)),
                new OA\Parameter(name: 'signature', in: 'query', required: true, description: 'Firma del enlace', schema: new OA\Schema(type: 'string', example: 'signature...')),
            ],
            responses: [
                new OA\Response(
                    response: 200,
                    description: 'Email verificado exitosamente',
                    content: new OA\JsonContent(
                        properties: [
                            new OA\Property(property: 'data', type: 'object', properties: [
                                new OA\Property(property: 'id', type: 'integer', example: 1),
                                new OA\Property(property: 'name', type: 'string', example: 'Usuario Ejemplo'),
                                new OA\Property(property: 'email', type: 'string', format: 'email', example: 'user@example.com'),
                                new OA\Property(property: 'email_verified_at', type: 'string', format: 'date-time'),
                                new OA\Property(property: 'roles', type: 'array', items: new OA\Items(type: 'object')),
                            ]),
                            new OA\Property(property: 'message', type: 'string', example: 'Correo verificado correctamente.'),
                        ]
                    )
                ),
                new OA\Response(response: 400, description: 'Verificación de email no habilitada', content: new OA\JsonContent(ref: '#/components/schemas/ErrorResponse')),
                new OA\Response(response: 403, description: 'Enlace de verificación inválido', content: new OA\JsonContent(ref: '#/components/schemas/ErrorResponse')),
                new OA\Response(response: 422, description: 'Errores de validación', content: new OA\JsonContent(ref: '#/components/schemas/ValidationErrorResponse')),
            ]
        )
    ]
    public function verifyEmail(VerifyEmailRequest $request): JsonResponse
    {
        $user = User::findOrFail($request->id);

        if (!method_exists($user, 'hasVerifiedEmail') || !method_exists($user, 'markEmailAsVerified')) {
            return ApiResponse::error('FEATURE_DISABLED', 'Verificación de email no está habilitada.', null, 400);
        }

        if ($user->hasVerifiedEmail()) {
            return ApiResponse::success(new UserResource($user->load('roles')), 'El correo ya estaba verificado.', null, 200);
        }

        if (hash('sha1', $user->email) !== $request->hash) {
            return ApiResponse::error('FORBIDDEN', 'Enlace de verificación inválido.', null, 403);
        }

        $user->markEmailAsVerified();
        event(new Verified($user));

        return ApiResponse::success(new UserResource($user->load('roles')), 'Correo verificado correctamente.', null, 200);
    }

    /**
     * Reenvía el correo de verificación. Requiere auth:sanctum.
     */
    #[
        OA\Post(
            path: '/api/v1/auth/resend-verification',
            summary: 'Reenviar correo de verificación',
            description: 'Reenvía el correo electrónico de verificación al usuario autenticado.',
            tags: ['Auth'],
            security: [['bearerAuth' => []]],
            responses: [
                new OA\Response(
                    response: 200,
                    description: 'Correo de verificación reenviado',
                    content: new OA\JsonContent(
                        properties: [
                            new OA\Property(property: 'data', nullable: true),
                            new OA\Property(property: 'message', type: 'string', example: 'Se ha enviado un nuevo enlace de verificación.'),
                        ]
                    )
                ),
                new OA\Response(response: 400, description: 'Verificación de email no habilitada', content: new OA\JsonContent(ref: '#/components/schemas/ErrorResponse')),
                new OA\Response(response: 401, description: 'No autenticado', content: new OA\JsonContent(ref: '#/components/schemas/ErrorResponse')),
            ]
        )
    ]
    public function resendVerification(ResendVerificationRequest $request): JsonResponse
    {
        $user = $request->user();

        if (!method_exists($user, 'hasVerifiedEmail') || !method_exists($user, 'sendEmailVerificationNotification')) {
            return ApiResponse::error('FEATURE_DISABLED', 'Verificación de email no está habilitada.', null, 400);
        }

        if ($user->hasVerifiedEmail()) {
            return ApiResponse::success(new UserResource($user->load('roles')), 'El correo ya está verificado.', null, 200);
        }

        $user->sendEmailVerificationNotification();

        return ApiResponse::success(null, 'Se ha enviado un nuevo enlace de verificación.', null, 200);
    }
}
