<?php

namespace App\Http\Controllers\Api\V1;

use App\Http\Controllers\Controller;
use App\Http\Requests\Api\V1\InstitutionUpdateRequest;
use App\Http\Resources\V1\ConceptoCatalogResource;
use App\Http\Resources\V1\DebeCatalogResource;
use App\Http\Resources\V1\ElaboradoCatalogResource;
use App\Http\Resources\V1\GroupCatalogResource;
use App\Http\Resources\V1\HaberCatalogResource;
use App\Http\Resources\V1\InstitutionCatalogResource;
use App\Http\Resources\V1\ModuleCatalogResource;
use App\Http\Resources\V1\OtrosConceptoCatalogResource;
use App\Http\Resources\V1\ProgramCatalogResource;
use App\Http\Resources\V1\ScheduleCatalogResource;
use App\Http\Resources\V1\TeacherCatalogResource;
use App\Models\concepto;
use App\Models\debe;
use App\Models\elaborado;
use App\Models\Group;
use App\Models\haber;
use App\Models\InstitutionSetting;
use App\Models\Matricula;
use App\Models\Module;
use App\Models\otrosConcepto;
use App\Models\Program;
use App\Models\Schedule;
use App\Models\Teacher;
use App\Support\ApiResponse;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;
use OpenApi\Attributes as OA;

class CatalogController extends Controller
{
    /** @var array<string, array{model: class-string, resource: class-string, request: class-string}> */
    private const WHITELIST = [
        'programs' => [ 'model' => Program::class, 'resource' => ProgramCatalogResource::class, 'request' => \App\Http\Requests\Api\V1\ProgramCatalogRequest::class ],
        'schedules' => [ 'model' => Schedule::class, 'resource' => ScheduleCatalogResource::class, 'request' => \App\Http\Requests\Api\V1\ScheduleCatalogRequest::class ],
        'groups' => [ 'model' => Group::class, 'resource' => GroupCatalogResource::class, 'request' => \App\Http\Requests\Api\V1\GroupCatalogRequest::class ],
        'teachers' => [ 'model' => Teacher::class, 'resource' => TeacherCatalogResource::class, 'request' => \App\Http\Requests\Api\V1\TeacherCatalogRequest::class ],
        'modules' => [ 'model' => Module::class, 'resource' => ModuleCatalogResource::class, 'request' => \App\Http\Requests\Api\V1\ModuleCatalogRequest::class ],
        'conceptos' => [ 'model' => concepto::class, 'resource' => ConceptoCatalogResource::class, 'request' => \App\Http\Requests\Api\V1\ConceptoCatalogRequest::class ],
        'elaborados' => [ 'model' => elaborado::class, 'resource' => ElaboradoCatalogResource::class, 'request' => \App\Http\Requests\Api\V1\ElaboradoCatalogRequest::class ],
        'habers' => [ 'model' => haber::class, 'resource' => HaberCatalogResource::class, 'request' => \App\Http\Requests\Api\V1\HaberCatalogRequest::class ],
        'debes' => [ 'model' => debe::class, 'resource' => DebeCatalogResource::class, 'request' => \App\Http\Requests\Api\V1\DebeCatalogRequest::class ],
        'otros-conceptos' => [ 'model' => otrosConcepto::class, 'resource' => OtrosConceptoCatalogResource::class, 'request' => \App\Http\Requests\Api\V1\OtrosConceptoCatalogRequest::class ],
    ];

    private function config(string $resource): ?array
    {
        return self::WHITELIST[$resource] ?? null;
    }

    /**
     * GET /settings/{resource} — lista paginada.
     */
    #[
        OA\Get(
            path: '/api/v1/settings/{resource}',
            summary: 'Listar catálogo',
            description: 'Lista los elementos de un catálogo específico con paginación. Recursos disponibles: programs, schedules, groups, teachers, modules, conceptos, elaborados, habers, debes, otros-conceptos.',
            tags: ['Settings'],
            security: [['bearerAuth' => []]],
            parameters: [
                new OA\Parameter(name: 'resource', in: 'path', required: true, description: 'Recurso del catálogo', schema: new OA\Schema(type: 'string', enum: ['programs', 'schedules', 'groups', 'teachers', 'modules', 'conceptos', 'elaborados', 'habers', 'debes', 'otros-conceptos'], example: 'programs')),
                new OA\Parameter(name: 'per_page', in: 'query', required: false, description: 'Elementos por página', schema: new OA\Schema(type: 'integer', example: 15)),
            ],
            responses: [
                new OA\Response(response: 200, description: 'Lista del catálogo', content: new OA\JsonContent(ref: '#/components/schemas/SuccessResponse')),
                new OA\Response(response: 401, description: 'No autenticado', content: new OA\JsonContent(ref: '#/components/schemas/ErrorResponse')),
                new OA\Response(response: 403, description: 'Sin permisos', content: new OA\JsonContent(ref: '#/components/schemas/ErrorResponse')),
                new OA\Response(response: 404, description: 'Recurso no encontrado', content: new OA\JsonContent(ref: '#/components/schemas/ErrorResponse')),
            ]
        )
    ]
    public function index(Request $request, string $resource): JsonResponse
    {
        $cfg = $this->config($resource);
        if (!$cfg) {
            return ApiResponse::error('NOT_FOUND', 'Recurso no encontrado.', null, 404);
        }

        $perPage = min((int) $request->get('per_page', 15), 100);
        $model = $cfg['model'];
        $query = $model::query()->orderBy('id');
        $paginator = $query->paginate($perPage);
        $res = $cfg['resource'];

        return ApiResponse::success(
            $res::collection($paginator->items())->resolve(),
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
     * POST /settings/{resource} — crear.
     */
    #[
        OA\Post(
            path: '/api/v1/settings/{resource}',
            summary: 'Crear elemento de catálogo',
            description: 'Crea un nuevo elemento en el catálogo especificado. Requiere permisos de administración de configuración.',
            tags: ['Settings'],
            security: [['bearerAuth' => []]],
            parameters: [
                new OA\Parameter(name: 'resource', in: 'path', required: true, description: 'Recurso del catálogo', schema: new OA\Schema(type: 'string', enum: ['programs', 'schedules', 'groups', 'teachers', 'modules', 'conceptos', 'elaborados', 'habers', 'debes', 'otros-conceptos'], example: 'programs')),
            ],
            requestBody: new OA\RequestBody(
                required: true,
                content: new OA\JsonContent(
                    description: 'Los campos requeridos varían según el recurso. Consultar la validación específica de cada catálogo.',
                    example: ['name' => 'Ejemplo']
                )
            ),
            responses: [
                new OA\Response(response: 201, description: 'Elemento creado exitosamente', content: new OA\JsonContent(ref: '#/components/schemas/SuccessResponse')),
                new OA\Response(response: 401, description: 'No autenticado', content: new OA\JsonContent(ref: '#/components/schemas/ErrorResponse')),
                new OA\Response(response: 403, description: 'Sin permisos', content: new OA\JsonContent(ref: '#/components/schemas/ErrorResponse')),
                new OA\Response(response: 404, description: 'Recurso no encontrado', content: new OA\JsonContent(ref: '#/components/schemas/ErrorResponse')),
                new OA\Response(response: 422, description: 'Errores de validación', content: new OA\JsonContent(ref: '#/components/schemas/ValidationErrorResponse')),
            ]
        )
    ]
    public function store(Request $request, string $resource): JsonResponse
    {
        $cfg = $this->config($resource);
        if (!$cfg) {
            return ApiResponse::error('NOT_FOUND', 'Recurso no encontrado.', null, 404);
        }

        $rules = $cfg['request']::getRules(true);
        $v = Validator::make($request->all(), $rules);
        if ($v->fails()) {
            return ApiResponse::error('VALIDATION_ERROR', 'Los datos enviados no son válidos.', $v->errors()->toArray(), 422);
        }

        $model = $cfg['model'];
        $item = $model::create($v->validated());
        $res = $cfg['resource'];

        return ApiResponse::success(new $res($item), 'Creado.', null, 201);
    }

    /**
     * GET /settings/{resource}/{id} — ver uno.
     */
    #[
        OA\Get(
            path: '/api/v1/settings/{resource}/{id}',
            summary: 'Obtener elemento de catálogo',
            description: 'Obtiene los datos de un elemento específico del catálogo.',
            tags: ['Settings'],
            security: [['bearerAuth' => []]],
            parameters: [
                new OA\Parameter(name: 'resource', in: 'path', required: true, description: 'Recurso del catálogo', schema: new OA\Schema(type: 'string', enum: ['programs', 'schedules', 'groups', 'teachers', 'modules', 'conceptos', 'elaborados', 'habers', 'debes', 'otros-conceptos'], example: 'programs')),
                new OA\Parameter(name: 'id', in: 'path', required: true, description: 'ID del elemento', schema: new OA\Schema(type: 'integer')),
            ],
            responses: [
                new OA\Response(response: 200, description: 'Datos del elemento', content: new OA\JsonContent(ref: '#/components/schemas/SuccessResponse')),
                new OA\Response(response: 401, description: 'No autenticado', content: new OA\JsonContent(ref: '#/components/schemas/ErrorResponse')),
                new OA\Response(response: 403, description: 'Sin permisos', content: new OA\JsonContent(ref: '#/components/schemas/ErrorResponse')),
                new OA\Response(response: 404, description: 'Recurso o elemento no encontrado', content: new OA\JsonContent(ref: '#/components/schemas/ErrorResponse')),
            ]
        )
    ]
    public function show(string $resource, int $id): JsonResponse
    {
        $cfg = $this->config($resource);
        if (!$cfg) {
            return ApiResponse::error('NOT_FOUND', 'Recurso no encontrado.', null, 404);
        }

        $model = $cfg['model'];
        $item = $model::find($id);
        if (!$item) {
            return ApiResponse::error('NOT_FOUND', 'No encontrado.', null, 404);
        }

        $res = $cfg['resource'];
        return ApiResponse::success(new $res($item));
    }

    /**
     * PUT/PATCH /settings/{resource}/{id} — actualizar.
     */
    #[
        OA\Put(
            path: '/api/v1/settings/{resource}/{id}',
            summary: 'Actualizar elemento de catálogo',
            description: 'Actualiza los datos de un elemento existente del catálogo. Requiere permisos de administración de configuración.',
            tags: ['Settings'],
            security: [['bearerAuth' => []]],
            parameters: [
                new OA\Parameter(name: 'resource', in: 'path', required: true, description: 'Recurso del catálogo', schema: new OA\Schema(type: 'string', enum: ['programs', 'schedules', 'groups', 'teachers', 'modules', 'conceptos', 'elaborados', 'habers', 'debes', 'otros-conceptos'], example: 'programs')),
                new OA\Parameter(name: 'id', in: 'path', required: true, description: 'ID del elemento', schema: new OA\Schema(type: 'integer')),
            ],
            requestBody: new OA\RequestBody(
                required: false,
                content: new OA\JsonContent(
                    description: 'Los campos permitidos varían según el recurso. Consultar la validación específica de cada catálogo.',
                    example: ['name' => 'Ejemplo Actualizado']
                )
            ),
            responses: [
                new OA\Response(response: 200, description: 'Elemento actualizado exitosamente', content: new OA\JsonContent(ref: '#/components/schemas/SuccessResponse')),
                new OA\Response(response: 401, description: 'No autenticado', content: new OA\JsonContent(ref: '#/components/schemas/ErrorResponse')),
                new OA\Response(response: 403, description: 'Sin permisos', content: new OA\JsonContent(ref: '#/components/schemas/ErrorResponse')),
                new OA\Response(response: 404, description: 'Recurso o elemento no encontrado', content: new OA\JsonContent(ref: '#/components/schemas/ErrorResponse')),
                new OA\Response(response: 422, description: 'Errores de validación', content: new OA\JsonContent(ref: '#/components/schemas/ValidationErrorResponse')),
            ]
        )
    ]
    public function update(Request $request, string $resource, int $id): JsonResponse
    {
        $cfg = $this->config($resource);
        if (!$cfg) {
            return ApiResponse::error('NOT_FOUND', 'Recurso no encontrado.', null, 404);
        }

        $rules = $cfg['request']::getRules(false);
        $v = Validator::make($request->all(), $rules);
        if ($v->fails()) {
            return ApiResponse::error('VALIDATION_ERROR', 'Los datos enviados no son válidos.', $v->errors()->toArray(), 422);
        }

        $model = $cfg['model'];
        $item = $model::find($id);
        if (!$item) {
            return ApiResponse::error('NOT_FOUND', 'No encontrado.', null, 404);
        }

        $item->update($v->validated());
        $res = $cfg['resource'];
        return ApiResponse::success(new $res($item->fresh()), 'Actualizado.', null, 200);
    }

    /**
     * DELETE /settings/{resource}/{id} — eliminar.
     */
    #[
        OA\Delete(
            path: '/api/v1/settings/{resource}/{id}',
            summary: 'Eliminar elemento de catálogo',
            description: 'Elimina un elemento existente del catálogo. No se puede eliminar si está siendo usado en otras entidades. Requiere permisos de administración de configuración.',
            tags: ['Settings'],
            security: [['bearerAuth' => []]],
            parameters: [
                new OA\Parameter(name: 'resource', in: 'path', required: true, description: 'Recurso del catálogo', schema: new OA\Schema(type: 'string', enum: ['programs', 'schedules', 'groups', 'teachers', 'modules', 'conceptos', 'elaborados', 'habers', 'debes', 'otros-conceptos'], example: 'programs')),
                new OA\Parameter(name: 'id', in: 'path', required: true, description: 'ID del elemento', schema: new OA\Schema(type: 'integer')),
            ],
            responses: [
                new OA\Response(response: 200, description: 'Elemento eliminado exitosamente', content: new OA\JsonContent(ref: '#/components/schemas/SuccessResponse')),
                new OA\Response(response: 401, description: 'No autenticado', content: new OA\JsonContent(ref: '#/components/schemas/ErrorResponse')),
                new OA\Response(response: 403, description: 'Sin permisos', content: new OA\JsonContent(ref: '#/components/schemas/ErrorResponse')),
                new OA\Response(response: 404, description: 'Recurso o elemento no encontrado', content: new OA\JsonContent(ref: '#/components/schemas/ErrorResponse')),
                new OA\Response(response: 422, description: 'No se puede eliminar porque está siendo usado', content: new OA\JsonContent(ref: '#/components/schemas/ValidationErrorResponse')),
            ]
        )
    ]
    public function destroy(string $resource, int $id): JsonResponse
    {
        $cfg = $this->config($resource);
        if (!$cfg) {
            return ApiResponse::error('NOT_FOUND', 'Recurso no encontrado.', null, 404);
        }

        $model = $cfg['model'];
        $item = $model::find($id);
        if (!$item) {
            return ApiResponse::error('NOT_FOUND', 'No encontrado.', null, 404);
        }

        $err = $this->checkInUse($resource, $item);
        if ($err) {
            return ApiResponse::error('VALIDATION_ERROR', $err, null, 422);
        }

        $item->delete();
        return ApiResponse::success(null, 'Eliminado.', null, 200);
    }

    /**
     * GET /settings/institution — configuración de institución (singleton).
     */
    #[
        OA\Get(
            path: '/api/v1/settings/institution',
            summary: 'Obtener configuración de institución',
            description: 'Obtiene la configuración de la institución (singleton). Requiere permisos de administración de configuración.',
            tags: ['Settings'],
            security: [['bearerAuth' => []]],
            responses: [
                new OA\Response(response: 200, description: 'Configuración de institución', content: new OA\JsonContent(ref: '#/components/schemas/SuccessResponse')),
                new OA\Response(response: 401, description: 'No autenticado', content: new OA\JsonContent(ref: '#/components/schemas/ErrorResponse')),
                new OA\Response(response: 403, description: 'Sin permisos', content: new OA\JsonContent(ref: '#/components/schemas/ErrorResponse')),
            ]
        )
    ]
    public function showInstitution(): JsonResponse
    {
        $item = InstitutionSetting::getSettings();
        return ApiResponse::success(new InstitutionCatalogResource($item));
    }

    /**
     * PUT /settings/institution — actualizar institución.
     */
    #[
        OA\Put(
            path: '/api/v1/settings/institution',
            summary: 'Actualizar configuración de institución',
            description: 'Actualiza la configuración de la institución. Requiere permisos de administración de configuración.',
            tags: ['Settings'],
            security: [['bearerAuth' => []]],
            requestBody: new OA\RequestBody(
                required: false,
                content: new OA\JsonContent(
                    description: 'Campos de configuración de institución. Varían según InstitutionUpdateRequest.',
                    example: ['nombre' => 'Institución Ejemplo', 'nit' => '123456789']
                )
            ),
            responses: [
                new OA\Response(response: 200, description: 'Configuración actualizada exitosamente', content: new OA\JsonContent(ref: '#/components/schemas/SuccessResponse')),
                new OA\Response(response: 401, description: 'No autenticado', content: new OA\JsonContent(ref: '#/components/schemas/ErrorResponse')),
                new OA\Response(response: 403, description: 'Sin permisos', content: new OA\JsonContent(ref: '#/components/schemas/ErrorResponse')),
                new OA\Response(response: 422, description: 'Errores de validación', content: new OA\JsonContent(ref: '#/components/schemas/ValidationErrorResponse')),
            ]
        )
    ]
    public function updateInstitution(InstitutionUpdateRequest $request): JsonResponse
    {
        $item = InstitutionSetting::getSettings();
        $item->update($request->validated());
        \Illuminate\Support\Facades\Cache::forget('institution_settings');
        return ApiResponse::success(new InstitutionCatalogResource($item->fresh()), 'Configuración actualizada.', null, 200);
    }

    private function checkInUse(string $resource, object $item): ?string
    {
        switch ($resource) {
            case 'conceptos':
                if (\App\Models\Entry::where('concepto', $item->id)->exists()) {
                    return 'No se puede eliminar el concepto porque está siendo usado en abonos.';
                }
                break;
            case 'elaborados':
                if (\App\Models\Entry::where('elaborado_por', $item->id)->exists() || \App\Models\OtherEntry::where('elaborado_por', $item->id)->exists()
                    || \App\Models\EgresoReceipt::where('elaborado_por', $item->id)->exists() || \App\Models\ThirdReceipts::where('elaborado_por', $item->id)->exists()) {
                    return 'No se puede eliminar el elaborador porque está siendo usado en recibos.';
                }
                break;
            case 'habers':
                if (\App\Models\Entry::where('haber', $item->id)->exists() || \App\Models\OtherEntry::where('haber', $item->id)->exists()
                    || \App\Models\EgresoReceipt::where('haber', $item->id)->exists() || \App\Models\ThirdReceipts::where('haber', $item->id)->exists()
                    || concepto::where('haber', $item->id)->exists() || otrosConcepto::where('haber', $item->id)->exists()
                    || \App\Models\ConceptEntryReceipt::where('haber', $item->id)->exists() || \App\Models\ConceptDischargeReceipt::where('haber', $item->id)->exists()) {
                    return 'No se puede eliminar la cuenta haber porque está siendo usada.';
                }
                break;
            case 'debes':
                if (\App\Models\Entry::where('debe', $item->id)->exists() || \App\Models\OtherEntry::where('debe', $item->id)->exists()
                    || \App\Models\EgresoReceipt::where('debe', $item->id)->exists() || \App\Models\ThirdReceipts::where('debe', $item->id)->exists()
                    || concepto::where('debe', $item->id)->exists() || otrosConcepto::where('debe', $item->id)->exists()
                    || \App\Models\ConceptEntryReceipt::where('debe', $item->id)->exists() || \App\Models\ConceptDischargeReceipt::where('debe', $item->id)->exists()) {
                    return 'No se puede eliminar la cuenta debe porque está siendo usada.';
                }
                break;
            case 'otros-conceptos':
                if (\App\Models\OtherEntry::where('concepto', $item->id)->exists()) {
                    return 'No se puede eliminar el concepto porque está siendo usado en otros ingresos.';
                }
                break;
            case 'programs':
                if (Matricula::where('programa', $item->name)->exists()) {
                    return 'No se puede eliminar el programa porque está siendo usado en matrículas.';
                }
                break;
            case 'schedules':
                if (Matricula::where('horario', $item->name)->exists()) {
                    return 'No se puede eliminar el horario porque está siendo usado en matrículas.';
                }
                break;
            case 'groups':
                if (Matricula::where('numero_grupo', $item->name)->exists()) {
                    return 'No se puede eliminar el grupo porque está siendo usado en matrículas.';
                }
                break;
            // teachers, modules: sin comprobación en web
        }
        return null;
    }
}
