<?php

namespace App\Http\Controllers\Api\V1;

use App\Http\Controllers\Controller;
use App\Http\Requests\Api\V1\MatriculaStoreRequest;
use App\Http\Requests\Api\V1\MatriculaUpdateRequest;
use App\Http\Resources\V1\MatriculaResource;
use App\Http\Resources\V1\CostResource;
use App\Http\Resources\V1\EntryResource;
use App\Http\Resources\V1\OtherEntryResource;
use App\Http\Resources\V1\PurseResource;
use App\Services\MatriculaService;
use App\Support\ApiResponse;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use OpenApi\Attributes as OA;

class MatriculaController extends Controller
{
    public function __construct(
        private MatriculaService $matriculaService
    ) {}

    /**
     * GET /matriculas?search=...&programa=...&horario=...&tipo_documento=...&per_page=15
     */
    #[
        OA\Get(
            path: '/api/v1/matriculas',
            summary: 'Listar matrículas',
            description: 'Lista las matrículas de estudiantes con filtros opcionales y paginación.',
            tags: ['Matriculas'],
            security: [['bearerAuth' => []]],
            parameters: [
                new OA\Parameter(name: 'search', in: 'query', required: false, description: 'Búsqueda por nombre o número de documento', schema: new OA\Schema(type: 'string', example: 'Juan')),
                new OA\Parameter(name: 'programa', in: 'query', required: false, description: 'Filtrar por programa', schema: new OA\Schema(type: 'string', example: 'Técnico en Sistemas')),
                new OA\Parameter(name: 'horario', in: 'query', required: false, description: 'Filtrar por horario', schema: new OA\Schema(type: 'string', example: 'Diurno')),
                new OA\Parameter(name: 'tipo_documento', in: 'query', required: false, description: 'Filtrar por tipo de documento', schema: new OA\Schema(type: 'string', enum: ['CC', 'TI', 'PPT'])),
                new OA\Parameter(name: 'per_page', in: 'query', required: false, description: 'Elementos por página', schema: new OA\Schema(type: 'integer', example: 15)),
            ],
            responses: [
                new OA\Response(
                    response: 200,
                    description: 'Lista de matrículas',
                    content: new OA\JsonContent(
                        properties: [
                            new OA\Property(property: 'data', type: 'array', items: new OA\Items(type: 'object')),
                            new OA\Property(property: 'meta', type: 'object', properties: [
                                new OA\Property(property: 'current_page', type: 'integer', example: 1),
                                new OA\Property(property: 'per_page', type: 'integer', example: 15),
                                new OA\Property(property: 'total', type: 'integer', example: 100),
                                new OA\Property(property: 'last_page', type: 'integer', example: 7),
                            ]),
                        ]
                    )
                ),
                new OA\Response(response: 401, description: 'No autenticado', content: new OA\JsonContent(ref: '#/components/schemas/ErrorResponse')),
            ]
        )
    ]
    public function index(Request $request): JsonResponse
    {
        $paginator = $this->matriculaService->list($request);

        return ApiResponse::success(
            MatriculaResource::collection($paginator->items())->resolve(),
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
     * POST /matriculas
     */
    #[
        OA\Post(
            path: '/api/v1/matriculas',
            summary: 'Crear matrícula',
            description: 'Crea una nueva matrícula de estudiante.',
            tags: ['Matriculas'],
            security: [['bearerAuth' => []]],
            requestBody: new OA\RequestBody(
                required: true,
                content: new OA\JsonContent(
                    required: ['nombre_completo', 'numero_documento', 'tipo_documento', 'programa', 'sede', 'estado_estudiante', 'horario', 'semestre_actual', 'anio', 'numero_grupo'],
                    properties: [
                        new OA\Property(property: 'nombre_completo', type: 'string', example: 'Juan Pérez'),
                        new OA\Property(property: 'numero_documento', type: 'string', example: '1234567890'),
                        new OA\Property(property: 'tipo_documento', type: 'string', enum: ['CC', 'TI', 'PPT'], example: 'CC'),
                        new OA\Property(property: 'departamento', type: 'string', nullable: true),
                        new OA\Property(property: 'estado_civil', type: 'string', nullable: true),
                        new OA\Property(property: 'ocupacion', type: 'string', nullable: true),
                        new OA\Property(property: 'nivel_formacion', type: 'string', nullable: true),
                        new OA\Property(property: 'tiene_discapacidad', type: 'string', enum: ['No', 'Sí', 'Prefiero no decir'], nullable: true),
                        new OA\Property(property: 'programa', type: 'string', example: 'Técnico en Sistemas'),
                        new OA\Property(property: 'sede', type: 'string', enum: ['Barrancabermeja', 'Aguachica', 'Virtual'], example: 'Barrancabermeja'),
                        new OA\Property(property: 'estado_estudiante', type: 'string', enum: ['Activo', 'Inactivo', 'Por Certificar', 'Certificado', 'Retirado', 'Suspendido', 'Todos'], example: 'Activo'),
                        new OA\Property(property: 'horario', type: 'string', example: 'Diurno'),
                        new OA\Property(property: 'talla_uniforme', type: 'string', enum: ['XS', 'S', 'M', 'L', 'XL', 'XXL', 'XXXL'], nullable: true),
                        new OA\Property(property: 'semestre_actual', type: 'string', enum: ['I', 'II', 'Ninguno (curso)'], example: 'I'),
                        new OA\Property(property: 'anio', type: 'string', example: '2024'),
                        new OA\Property(property: 'numero_grupo', type: 'string', example: '101'),
                        new OA\Property(property: 'contraseña_plataforma', type: 'string', nullable: true),
                        new OA\Property(property: 'tipo_discapacidad', type: 'string', nullable: true),
                    ]
                )
            ),
            responses: [
                new OA\Response(
                    response: 201,
                    description: 'Matrícula creada exitosamente',
                    content: new OA\JsonContent(
                        properties: [
                            new OA\Property(property: 'data', type: 'object'),
                            new OA\Property(property: 'message', type: 'string', example: 'Matrícula creada.'),
                        ]
                    )
                ),
                new OA\Response(response: 401, description: 'No autenticado', content: new OA\JsonContent(ref: '#/components/schemas/ErrorResponse')),
                new OA\Response(response: 422, description: 'Errores de validación', content: new OA\JsonContent(ref: '#/components/schemas/ValidationErrorResponse')),
            ]
        )
    ]
    public function store(MatriculaStoreRequest $request): JsonResponse
    {
        $matricula = $this->matriculaService->create($request->validated());

        return ApiResponse::success(new MatriculaResource($matricula), 'Matrícula creada.', null, 201);
    }

    /**
     * GET /matriculas/{cod_alumno}
     */
    #[
        OA\Get(
            path: '/api/v1/matriculas/{cod_alumno}',
            summary: 'Obtener matrícula',
            description: 'Obtiene los datos de una matrícula específica por código de alumno.',
            tags: ['Matriculas'],
            security: [['bearerAuth' => []]],
            parameters: [
                new OA\Parameter(name: 'cod_alumno', in: 'path', required: true, description: 'Código del alumno', schema: new OA\Schema(type: 'string', example: '12345678')),
            ],
            responses: [
                new OA\Response(
                    response: 200,
                    description: 'Datos de la matrícula',
                    content: new OA\JsonContent(
                        properties: [
                            new OA\Property(property: 'data', type: 'object'),
                        ]
                    )
                ),
                new OA\Response(response: 401, description: 'No autenticado', content: new OA\JsonContent(ref: '#/components/schemas/ErrorResponse')),
                new OA\Response(response: 404, description: 'Matrícula no encontrada', content: new OA\JsonContent(ref: '#/components/schemas/ErrorResponse')),
            ]
        )
    ]
    public function show(string $cod_alumno): JsonResponse
    {
        $matricula = $this->matriculaService->getByCodAlumno($cod_alumno);

        return ApiResponse::success(new MatriculaResource($matricula));
    }

    /**
     * GET /api/v1/enrollments/{cod_alumno} — consolidado completo.
     */
    #[
        OA\Get(
            path: '/api/v1/enrollments/{cod_alumno}',
            summary: 'Obtener datos completos de matrícula',
            description: 'Obtiene el estudiante, sus costos, cartera, abonos y otros ingresos en una sola respuesta consolidada.',
            tags: ['Matriculas'],
            security: [['bearerAuth' => []]],
            parameters: [
                new OA\Parameter(name: 'cod_alumno', in: 'path', required: true, description: 'Código del alumno', schema: new OA\Schema(type: 'string')),
            ],
            responses: [
                new OA\Response(response: 200, description: 'Datos consolidados', content: new OA\JsonContent(ref: '#/components/schemas/SuccessResponse')),
                new OA\Response(response: 401, description: 'No autenticado', content: new OA\JsonContent(ref: '#/components/schemas/ErrorResponse')),
                new OA\Response(response: 404, description: 'No encontrado', content: new OA\JsonContent(ref: '#/components/schemas/ErrorResponse')),
            ]
        )
    ]
    public function showFull(string $cod_alumno): JsonResponse
    {
        $data = $this->matriculaService->getFullEnrollmentData($cod_alumno);

        $response = [
            'matricula' => new MatriculaResource($data['matricula']),
            'costs' => CostResource::collection($data['costs'])->resolve(),
            'entries' => EntryResource::collection($data['entries'])->resolve(),
            'other_entries' => OtherEntryResource::collection($data['other_entries'])->resolve(),
            'purses' => PurseResource::collection($data['purses'])->resolve(),
        ];

        return ApiResponse::success($response);
    }

    /**
     * PUT/PATCH /matriculas/{cod_alumno}
     */
    #[
        OA\Put(
            path: '/api/v1/matriculas/{cod_alumno}',
            summary: 'Actualizar matrícula',
            description: 'Actualiza los datos de una matrícula existente.',
            tags: ['Matriculas'],
            security: [['bearerAuth' => []]],
            parameters: [
                new OA\Parameter(name: 'cod_alumno', in: 'path', required: true, description: 'Código del alumno', schema: new OA\Schema(type: 'string', example: '12345678')),
            ],
            requestBody: new OA\RequestBody(
                required: false,
                content: new OA\JsonContent(
                    properties: [
                        new OA\Property(property: 'nombre_completo', type: 'string', nullable: true),
                        new OA\Property(property: 'numero_documento', type: 'string', nullable: true),
                        new OA\Property(property: 'tipo_documento', type: 'string', enum: ['CC', 'TI', 'PPT'], nullable: true),
                        new OA\Property(property: 'programa', type: 'string', nullable: true),
                        new OA\Property(property: 'sede', type: 'string', enum: ['Barrancabermeja', 'Aguachica', 'Virtual'], nullable: true),
                        new OA\Property(property: 'estado_estudiante', type: 'string', enum: ['Activo', 'Inactivo', 'Por Certificar', 'Certificado', 'Retirado', 'Suspendido', 'Todos'], nullable: true),
                        new OA\Property(property: 'horario', type: 'string', nullable: true),
                        new OA\Property(property: 'semestre_actual', type: 'string', enum: ['I', 'II', 'Ninguno (curso)'], nullable: true),
                        new OA\Property(property: 'anio', type: 'string', nullable: true),
                        new OA\Property(property: 'numero_grupo', type: 'string', nullable: true),
                    ]
                )
            ),
            responses: [
                new OA\Response(
                    response: 200,
                    description: 'Matrícula actualizada exitosamente',
                    content: new OA\JsonContent(
                        properties: [
                            new OA\Property(property: 'data', type: 'object'),
                            new OA\Property(property: 'message', type: 'string', example: 'Matrícula actualizada.'),
                        ]
                    )
                ),
                new OA\Response(response: 401, description: 'No autenticado', content: new OA\JsonContent(ref: '#/components/schemas/ErrorResponse')),
                new OA\Response(response: 404, description: 'Matrícula no encontrada', content: new OA\JsonContent(ref: '#/components/schemas/ErrorResponse')),
                new OA\Response(response: 422, description: 'Errores de validación', content: new OA\JsonContent(ref: '#/components/schemas/ValidationErrorResponse')),
            ]
        )
    ]
    public function update(MatriculaUpdateRequest $request, string $cod_alumno): JsonResponse
    {
        $matricula = $this->matriculaService->update($cod_alumno, $request->validated());

        return ApiResponse::success(new MatriculaResource($matricula), 'Matrícula actualizada.', null, 200);
    }

    /**
     * DELETE /matriculas/{cod_alumno}?confirmar_cascada=1
     */
    #[
        OA\Delete(
            path: '/api/v1/matriculas/{cod_alumno}',
            summary: 'Eliminar matrícula',
            description: 'Elimina una matrícula. Si hay relaciones en cascada, se debe enviar confirmar_cascada=1.',
            tags: ['Matriculas'],
            security: [['bearerAuth' => []]],
            parameters: [
                new OA\Parameter(name: 'cod_alumno', in: 'path', required: true, description: 'Código del alumno', schema: new OA\Schema(type: 'string', example: '12345678')),
                new OA\Parameter(name: 'confirmar_cascada', in: 'query', required: false, description: 'Confirmar eliminación en cascada', schema: new OA\Schema(type: 'boolean', example: false)),
            ],
            responses: [
                new OA\Response(
                    response: 200,
                    description: 'Matrícula eliminada exitosamente',
                    content: new OA\JsonContent(
                        properties: [
                            new OA\Property(property: 'data', nullable: true),
                            new OA\Property(property: 'message', type: 'string', example: 'Eliminado.'),
                        ]
                    )
                ),
                new OA\Response(response: 401, description: 'No autenticado', content: new OA\JsonContent(ref: '#/components/schemas/ErrorResponse')),
                new OA\Response(response: 404, description: 'Matrícula no encontrada', content: new OA\JsonContent(ref: '#/components/schemas/ErrorResponse')),
            ]
        )
    ]
    public function destroy(Request $request, string $cod_alumno): JsonResponse
    {
        $confirmar = filter_var($request->query('confirmar_cascada', false), FILTER_VALIDATE_BOOLEAN);
        $this->matriculaService->delete($cod_alumno, $confirmar);

        return ApiResponse::success(null, 'Eliminado.', null, 200);
    }

    /**
     * POST /matriculas/{cod_alumno}/foto — multipart form-data, campo "foto" (image)
     */
    #[
        OA\Post(
            path: '/api/v1/matriculas/{cod_alumno}/foto',
            summary: 'Subir foto de estudiante',
            description: 'Sube una foto para el estudiante especificado. El archivo debe ser una imagen (JPEG, JPG, PNG, WEBP) con un tamaño máximo de 2MB.',
            tags: ['Matriculas'],
            security: [['bearerAuth' => []]],
            parameters: [
                new OA\Parameter(name: 'cod_alumno', in: 'path', required: true, description: 'Código del alumno', schema: new OA\Schema(type: 'string', example: '12345678')),
            ],
            requestBody: new OA\RequestBody(
                required: true,
                content: new OA\MediaType(
                    mediaType: 'multipart/form-data',
                    schema: new OA\Schema(
                        required: ['foto'],
                        properties: [
                            new OA\Property(
                                property: 'foto',
                                type: 'string',
                                format: 'binary',
                                description: 'Archivo de imagen (JPEG, JPG, PNG, WEBP, máx. 2MB)'
                            ),
                        ]
                    )
                )
            ),
            responses: [
                new OA\Response(
                    response: 200,
                    description: 'Foto subida exitosamente',
                    content: new OA\JsonContent(
                        properties: [
                            new OA\Property(property: 'data', type: 'object'),
                            new OA\Property(property: 'message', type: 'string', example: 'Foto subida.'),
                        ]
                    )
                ),
                new OA\Response(response: 401, description: 'No autenticado', content: new OA\JsonContent(ref: '#/components/schemas/ErrorResponse')),
                new OA\Response(response: 404, description: 'Matrícula no encontrada', content: new OA\JsonContent(ref: '#/components/schemas/ErrorResponse')),
                new OA\Response(response: 422, description: 'Errores de validación', content: new OA\JsonContent(ref: '#/components/schemas/ValidationErrorResponse')),
            ]
        )
    ]
    public function uploadFoto(Request $request, string $cod_alumno): JsonResponse
    {
        $request->validate([
            'foto' => 'required|image|mimes:jpeg,jpg,png,webp|max:2048',
        ], [
            'foto.required' => 'Debe enviar un archivo de imagen.',
            'foto.image' => 'El archivo debe ser una imagen.',
            'foto.mimes' => 'Formatos permitidos: jpeg, jpg, png, webp.',
            'foto.max' => 'La imagen no debe superar 2 MB.',
        ]);

        $result = $this->matriculaService->uploadPhoto($cod_alumno, $request->file('foto'));

        return ApiResponse::success($result, 'Foto subida.');
    }

    /**
     * GET /matriculas/{cod_alumno}/foto — stream imagen
     */
    #[
        OA\Get(
            path: '/api/v1/matriculas/{cod_alumno}/foto',
            summary: 'Obtener foto de estudiante',
            description: 'Devuelve la foto del estudiante como imagen binaria. Si no tiene foto, devuelve 404. Este endpoint es público (sin autenticación) para permitir que funcione en etiquetas <img>.',
            tags: ['Matriculas'],
            security: [],
            parameters: [
                new OA\Parameter(
                    name: 'cod_alumno',
                    in: 'path',
                    required: true,
                    description: 'Código del alumno',
                    schema: new OA\Schema(type: 'string', example: '12345678')
                ),
            ],
            responses: [
                new OA\Response(
                    response: 200,
                    description: 'Foto obtenida exitosamente',
                    content: new OA\MediaType(
                        mediaType: 'image/jpeg',
                        schema: new OA\Schema(
                            type: 'string',
                            format: 'binary'
                        )
                    ),
                    headers: [
                        new OA\Header(
                            header: 'Content-Type',
                            schema: new OA\Schema(type: 'string'),
                            description: 'image/jpeg, image/png, image/webp según el archivo'
                        ),
                        new OA\Header(
                            header: 'Content-Disposition',
                            schema: new OA\Schema(type: 'string'),
                            description: 'inline; filename="foto-{cod_alumno}.{ext}"'
                        ),
                    ]
                ),
                new OA\Response(
                    response: 401,
                    description: 'No autenticado',
                    content: new OA\JsonContent(ref: '#/components/schemas/ErrorResponse')
                ),
                new OA\Response(
                    response: 404,
                    description: 'Matrícula no encontrada o sin foto',
                    content: new OA\JsonContent(ref: '#/components/schemas/ErrorResponse')
                ),
            ]
        )
    ]
    public function getFoto(string $cod_alumno)
    {
        \Log::info("getFoto llamado para cod_alumno: {$cod_alumno}");
        try {
            $matricula = $this->matriculaService->getByCodAlumno($cod_alumno);
            \Log::info("Matrícula encontrada, photo_path: " . ($matricula->photo_path ?? 'null'));

            if (!$matricula->photo_path) {
                \Log::warning("Matrícula {$cod_alumno} no tiene photo_path");
                abort(404, 'El estudiante no tiene foto registrada.');
            }

            $photoPath = $matricula->photo_path;
            $storage = \Illuminate\Support\Facades\Storage::disk('public');

            if (!$storage->exists($photoPath)) {
                \Log::warning("Foto no existe en storage: {$photoPath} para matrícula {$cod_alumno}");
                abort(404, 'La foto no se encuentra en el servidor.');
            }

            $file = $storage->get($photoPath);
            if (!$file) {
                \Log::error("No se pudo leer el archivo: {$photoPath}");
                abort(500, 'Error al leer la foto.');
            }

            $mimeType = \Illuminate\Support\Facades\Storage::disk('public')->mimeType($photoPath);
            if (!$mimeType) {
                // Intentar detectar el tipo MIME por extensión
                $extension = strtolower(pathinfo($photoPath, PATHINFO_EXTENSION));
                $mimeTypes = [
                    'jpg' => 'image/jpeg',
                    'jpeg' => 'image/jpeg',
                    'png' => 'image/png',
                    'webp' => 'image/webp',
                ];
                $mimeType = $mimeTypes[$extension] ?? 'image/jpeg';
            }

            $extension = pathinfo($photoPath, PATHINFO_EXTENSION);
            $filename = 'foto-' . $cod_alumno . '.' . $extension;

            return response($file, 200)
                ->header('Content-Type', $mimeType)
                ->header('Content-Disposition', 'inline; filename="' . $filename . '"')
                ->header('Cache-Control', 'public, max-age=3600');
        } catch (\Illuminate\Database\Eloquent\ModelNotFoundException $e) {
            \Log::error("Matrícula no encontrada: {$cod_alumno}");
            abort(404, 'Matrícula no encontrada.');
        } catch (\Exception $e) {
            \Log::error("Error en getFoto para {$cod_alumno}: " . $e->getMessage());
            abort(500, 'Error al obtener la foto.');
        }
    }

    /**
     * GET /matriculas/{cod_alumno}/pdf — stream PDF
     */
    #[
        OA\Get(
            path: '/api/v1/matriculas/{cod_alumno}/pdf',
            summary: 'Obtener PDF de matrícula',
            description: 'Genera y devuelve un PDF de la ficha de matrícula del estudiante especificado. El PDF se devuelve como binario para visualización inline.',
            tags: ['Matriculas'],
            security: [['bearerAuth' => []]],
            parameters: [
                new OA\Parameter(
                    name: 'cod_alumno',
                    in: 'path',
                    required: true,
                    description: 'Código del alumno',
                    schema: new OA\Schema(type: 'string', example: '12345678')
                ),
            ],
            responses: [
                new OA\Response(
                    response: 200,
                    description: 'PDF generado exitosamente',
                    content: new OA\MediaType(
                        mediaType: 'application/pdf',
                        schema: new OA\Schema(
                            type: 'string',
                            format: 'binary'
                        )
                    ),
                    headers: [
                        new OA\Header(
                            header: 'Content-Disposition',
                            schema: new OA\Schema(type: 'string'),
                            description: 'inline; filename="matricula-12345678.pdf"'
                        ),
                    ]
                ),
                new OA\Response(
                    response: 401,
                    description: 'No autenticado',
                    content: new OA\JsonContent(ref: '#/components/schemas/ErrorResponse')
                ),
                new OA\Response(
                    response: 404,
                    description: 'Matrícula no encontrada',
                    content: new OA\JsonContent(ref: '#/components/schemas/ErrorResponse')
                ),
            ]
        )
    ]
    public function streamPdf(string $cod_alumno)
    {
        return $this->matriculaService->streamPdf($cod_alumno);
    }

    /**
     * GET /matriculas/form-data
     */
    #[
        OA\Get(
            path: '/api/v1/matriculas/form-data',
            summary: 'Obtener datos para formularios',
            description: 'Devuelve listas de valores (enums) necesarios para poblar los selects en los formularios de matrícula.',
            tags: ['Matriculas'],
            security: [['bearerAuth' => []]],
            responses: [
                new OA\Response(
                    response: 200,
                    description: 'Datos de formulario obtenidos exitosamente',
                    content: new OA\JsonContent(
                        properties: [
                            new OA\Property(property: 'tipo_documento', type: 'array', items: new OA\Items(type: 'string')),
                            new OA\Property(property: 'sede', type: 'array', items: new OA\Items(type: 'string')),
                            new OA\Property(property: 'estado_estudiante', type: 'array', items: new OA\Items(type: 'string')),
                            new OA\Property(property: 'semestre_actual', type: 'array', items: new OA\Items(type: 'string')),
                            new OA\Property(property: 'talla_uniforme', type: 'array', items: new OA\Items(type: 'string')),
                            new OA\Property(property: 'tiene_discapacidad', type: 'array', items: new OA\Items(type: 'string')),
                        ]
                    )
                ),
                new OA\Response(response: 401, description: 'No autenticado', content: new OA\JsonContent(ref: '#/components/schemas/ErrorResponse')),
            ]
        )
    ]
    public function formData(): JsonResponse
    {
        $data = [
            'tipo_documento' => ['CC', 'TI', 'PPT'],
            'sede' => ['Barrancabermeja', 'Aguachica', 'Virtual'],
            'estado_estudiante' => ['Activo', 'Inactivo', 'Por Certificar', 'Certificado', 'Retirado', 'Suspendido', 'Todos'],
            'semestre_actual' => ['I', 'II', 'Ninguno (curso)'],
            'talla_uniforme' => ['XS', 'S', 'M', 'L', 'XL', 'XXL', 'XXXL'],
            'tiene_discapacidad' => ['No', 'Sí', 'Prefiero no decir'],
        ];

        return ApiResponse::success($data);
    }
}
