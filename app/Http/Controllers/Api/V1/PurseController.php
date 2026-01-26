<?php

namespace App\Http\Controllers\Api\V1;

use App\Http\Controllers\Controller;
use App\Http\Requests\Api\V1\PurseIndexRequest;
use App\Http\Resources\V1\HistoryPurseResource;
use App\Http\Resources\V1\PurseResource;
use App\Models\Cost;
use App\Models\historyPurse;
use App\Models\Purse;
use App\Services\CarteraService;
use App\Support\ApiResponse;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use OpenApi\Attributes as OA;
use App\Http\Controllers\TableChangeController;
use App\Http\Controllers\DateController;
use Illuminate\Support\Str;
use Dompdf\Dompdf;
use Illuminate\Support\Facades\DB;
use App\Models\InstitutionSetting;

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
        $ids_cost = Cost::where('cod_alumno', $request->cod_alumno)->pluck('id')->toArray();
        if (empty($ids_cost)) {
            return ApiResponse::success([], null, ['current_page' => 1, 'per_page' => (int) $request->get('per_page', 15), 'total' => 0, 'last_page' => 1], 200);
        }

        $perPage = min((int) $request->get('per_page', 15), 100);
        $paginator = Purse::whereIn('id_cost', $ids_cost)->with('cost')->orderBy('fecha_pago')->paginate($perPage);

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

    /**
     * GET /purses/totales?id_cost=...&cod_alumno=...
     * Obtiene los totales calculados de cartera usando CarteraService
     */
    #[
        OA\Get(
            path: '/api/v1/purses/totales',
            summary: 'Obtener totales de cartera',
            description: 'Obtiene los totales calculados de cartera (total_abono, cuotas_total, total_abonado, saldo_pendiente, saldo_a_favor, saldo_en_mora) para un id_cost o cod_alumno.',
            tags: ['Purses'],
            security: [['bearerAuth' => []]],
            parameters: [
                new OA\Parameter(name: 'id_cost', in: 'query', required: false, description: 'ID del costo', schema: new OA\Schema(type: 'integer')),
                new OA\Parameter(name: 'cod_alumno', in: 'query', required: false, description: 'Código del alumno (calcula para todos sus semestres)', schema: new OA\Schema(type: 'string', example: '12345678')),
            ],
            responses: [
                new OA\Response(response: 200, description: 'Totales calculados', content: new OA\JsonContent(ref: '#/components/schemas/SuccessResponse')),
                new OA\Response(response: 400, description: 'id_cost o cod_alumno es requerido', content: new OA\JsonContent(ref: '#/components/schemas/ErrorResponse')),
                new OA\Response(response: 401, description: 'No autenticado', content: new OA\JsonContent(ref: '#/components/schemas/ErrorResponse')),
            ]
        )
    ]
    public function totales(Request $request): JsonResponse
    {
        try {
            $id_cost = $request->input('id_cost');
            $cod_alumno = $request->input('cod_alumno');
            
            if (empty($id_cost) && empty($cod_alumno)) {
                return ApiResponse::error('id_cost o cod_alumno es requerido', 400);
            }
            
            $carteraData = CarteraService::calcularCartera($id_cost, $cod_alumno);
            
            return ApiResponse::success([
                'total_abono' => $carteraData['totales']['total_abono'],
                'cuotas_total' => $carteraData['totales']['cuotas_total'],
                'total_abonado' => $carteraData['totales']['total_abonado'],
                'saldo_pendiente' => $carteraData['totales']['saldo_pendiente'],
                'saldo_a_favor' => $carteraData['totales']['saldo_a_favor'],
                'saldo_en_mora' => $carteraData['totales']['saldo_en_mora'],
            ], 'Totales calculados correctamente', null, 200);
        } catch (\Exception $e) {
            \Log::error('Error en PurseController::totales', [
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString()
            ]);
            
            return ApiResponse::error('Error al calcular totales: ' . $e->getMessage(), 500);
        }
    }

    /**
     * GET /purses/cartera?id_cost=...&cod_alumno=...
     * Obtiene la información completa de cartera con cálculos detallados
     */
    #[
        OA\Get(
            path: '/api/v1/purses/cartera',
            summary: 'Obtener información completa de cartera',
            description: 'Obtiene la información completa de cartera con todas las cuotas calculadas, estados y totales para un id_cost o cod_alumno.',
            tags: ['Purses'],
            security: [['bearerAuth' => []]],
            parameters: [
                new OA\Parameter(name: 'id_cost', in: 'query', required: false, description: 'ID del costo', schema: new OA\Schema(type: 'integer')),
                new OA\Parameter(name: 'cod_alumno', in: 'query', required: false, description: 'Código del alumno (calcula para todos sus semestres)', schema: new OA\Schema(type: 'string', example: '12345678')),
            ],
            responses: [
                new OA\Response(response: 200, description: 'Información completa de cartera', content: new OA\JsonContent(ref: '#/components/schemas/SuccessResponse')),
                new OA\Response(response: 400, description: 'id_cost o cod_alumno es requerido', content: new OA\JsonContent(ref: '#/components/schemas/ErrorResponse')),
                new OA\Response(response: 401, description: 'No autenticado', content: new OA\JsonContent(ref: '#/components/schemas/ErrorResponse')),
            ]
        )
    ]
    public function cartera(Request $request): JsonResponse
    {
        try {
            $id_cost = $request->input('id_cost');
            $cod_alumno = $request->input('cod_alumno');
            
            if (empty($id_cost) && empty($cod_alumno)) {
                return ApiResponse::error('id_cost o cod_alumno es requerido', 400);
            }
            
            $carteraData = CarteraService::calcularCartera($id_cost, $cod_alumno);
            
            // Formatear las cuotas para incluir información adicional
            $cuotasFormateadas = [];
            foreach ($carteraData['cuotas'] as $cuota) {
                $cuotasFormateadas[] = [
                    'id' => $cuota['id'],
                    'id_cost' => $cuota['id_cost'],
                    'numero_semestre' => $cuota['numero_semestre'] ?? 1,
                    'fecha_pago' => $cuota['fecha_pago'],
                    'cuota' => $cuota['cuota'],
                    'abonado' => $cuota['abonado'],
                    'estado_pago' => $cuota['estado_pago'],
                    'estado' => $cuota['estado'],
                    'is_vencida' => $cuota['is_vencida'],
                    'comentario' => $cuota['comentario'] ?? '',
                ];
            }
            
            return ApiResponse::success([
                'cuotas' => $cuotasFormateadas,
                'totales' => $carteraData['totales'],
                'hoy' => $carteraData['hoy'],
            ], 'Información de cartera obtenida correctamente', null, 200);
        } catch (\Exception $e) {
            \Log::error('Error en PurseController::cartera', [
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString()
            ]);
            
            return ApiResponse::error('Error al obtener información de cartera: ' . $e->getMessage(), 500);
        }
    }
    /**
     * POST /purse/edit
     * Actualiza una cuota y opcionalmente las siguientes (cascada).
     */
    #[
        OA\Post(
            path: '/api/v1/purse/edit',
            summary: 'Actualizar cartera',
            description: 'Actualiza los datos de una cuota (fecha, valor, comentario). Puede actualizar en cascada si ModifyInputLabel="todos".',
            tags: ['Purses'],
            security: [['bearerAuth' => []]],
            requestBody: new OA\RequestBody(
                required: true,
                content: new OA\JsonContent(
                    required: ['id', 'fecha_pago', 'cuota'],
                    properties: [
                        new OA\Property(property: 'id', type: 'integer', description: 'ID de la cartera'),
                        new OA\Property(property: 'fecha_pago', type: 'string', format: 'date', example: '2023-01-15'),
                        new OA\Property(property: 'cuota', type: 'string', example: '500000'),
                        new OA\Property(property: 'comentario', type: 'string', nullable: true),
                        new OA\Property(property: 'ModifyInputLabel', type: 'string', enum: ['todos'], nullable: true, description: 'Enviar "todos" para actualización en cascada'),
                    ]
                )
            ),
            responses: [
                new OA\Response(response: 200, description: 'Actualizado correctamente', content: new OA\JsonContent(ref: '#/components/schemas/SuccessResponse')),
                new OA\Response(response: 404, description: 'Cartera no encontrada', content: new OA\JsonContent(ref: '#/components/schemas/ErrorResponse')),
                new OA\Response(response: 422, description: 'Datos inválidos', content: new OA\JsonContent(ref: '#/components/schemas/ValidationErrorResponse')),
            ]
        )
    ]
    public function update(Request $request): JsonResponse
    {
        $request->validate([
            'id' => 'required|integer|exists:purses,id',
            'fecha_pago' => 'required|date',
            'cuota' => 'required', // Puede venir como string con puntos
        ]);

        try {
            // Modifico el purse principal
            $purse = Purse::findOrFail($request->id);
            $purse->fecha_pago = $request->fecha_pago;
            $purse->cuota = Str::replace('.', '', $request->cuota);
            $purse->comentario = $request->comentario;
            $purse->save();

            TableChangeController::StoreEdit('purses', $purse->id);

            $new = historyPurse::create([
                'id_purse' => $purse->id,
                'fecha_pago' => $purse->fecha_pago,
                'estado' => $purse->estado,
                'cuota' => $purse->cuota,
                'abonado' => $purse->abonado,
                'comentario' => $purse->comentario
            ]);

            if ($new) {
                TableChangeController::StoreAdd('history_purses', $new->id);
            }

            // Necesito modificar los demas purses (Cascada)
            if ($request->ModifyInputLabel == "todos") {
                $arrayPurses = Purse::where([
                    ['id_cost', "=", $purse->id_cost],
                    ['id', ">", $purse->id]
                ])->orderBy('id', 'asc')->get();

                // Obtener el día original del purse principal
                $diaOriginal = (int)(new \DateTime($purse->fecha_pago))->format('d');
                $fechaActual = new \DateTime($purse->fecha_pago);

                foreach ($arrayPurses as $item) {
                    // Obtener año y mes actual
                    $year = (int)$fechaActual->format('Y');
                    $month = (int)$fechaActual->format('m');
                    
                    // Incrementar el mes
                    $month++;
                    if ($month > 12) {
                        $month = 1;
                        $year++;
                    }
                    
                    // Usar el día original, ajustándolo si el mes no tiene suficientes días
                    $fechaActuals = $this->validateAndAdjustDate($year, $month, $diaOriginal);
                    $fechaActual = new \DateTime($fechaActuals);
                    

                    $item->fecha_pago = $fechaActuals;
                    $item->cuota = Str::replace('.', '', $request->cuota);
                    $item->comentario = $request->comentario;
                    $item->save();

                    TableChangeController::StoreEdit('purses', $item->id);

                    $new1 = historyPurse::create([
                        'id_purse' => $item->id,
                        'fecha_pago' => $fechaActuals,
                        'estado' => $item->estado,
                        'cuota' => $item->cuota,
                        'abonado' => $item->abonado,
                        'comentario' => $item->comentario
                    ]);

                    if ($new1) {
                        TableChangeController::StoreAdd('history_purses', $new1->id);
                    }
                }
            }

            return ApiResponse::success(new PurseResource($purse), 'Pago actualizado correctamente');

        } catch (\Exception $e) {
            \Log::error('Error en PurseController::update', [
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString()
            ]);
            
            return ApiResponse::error('Error al actualizar: ' . $e->getMessage(), 500);
        }
    }

    /**
     * GET /purses/cartera/{cod_alumno}/pdf — stream PDF de cartera
     */
    #[
        OA\Get(
            path: '/api/v1/purses/cartera/{cod_alumno}/pdf',
            summary: 'Obtener PDF de cartera',
            description: 'Genera y devuelve un PDF de la cartera del estudiante especificado usando la plantilla Blade existente. El PDF se devuelve como binario para visualización inline.',
            tags: ['Purses'],
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
                        schema: new OA\Schema(type: 'string', format: 'binary')
                    ),
                    headers: [
                        new OA\Header(
                            header: 'Content-Disposition',
                            schema: new OA\Schema(type: 'string'),
                            description: 'inline; filename="informe-cartera-Estudiante.pdf"'
                        ),
                    ]
                ),
                new OA\Response(
                    response: 401,
                    description: 'No autenticado',
                    content: new OA\JsonContent(ref: '#/components/schemas/ErrorResponse')
                ),
                new OA\Response(
                    response: 500,
                    description: 'Error al generar PDF',
                    content: new OA\JsonContent(ref: '#/components/schemas/ErrorResponse')
                ),
            ]
        )
    ]
    public function streamCarteraPdf(string $cod_alumno)
    {
        try {
            // Obtener datos del estudiante
            // Usamos la misma lógica que en ViewPdf del controlador legacy pero adaptada a la API
            // Primero buscamos en la tabla local matriculas
            $data = [];
            
            try {
                $matricula = \App\Models\Matricula::where('cod_alumno', $cod_alumno)->first();
                if ($matricula) {
                    $data = [
                        (object)[
                            'cedula' => $matricula->numero_documento ?? '',
                            'nombre' => $matricula->nombre_completo ?? 'N/A',
                            'nombre_programa' => $matricula->programa ?? ''
                        ]
                    ];
                } else {
                    // FALLBACK: Intentar en mysql2 (solo si está configurado y disponible)
                    // Esta parte se mantiene por compatibilidad con el sistema legacy si es necesaria
                    try {
                        // Verificamos si existe la conexión mysql2 antes de intentar usarla
                        $hasMysql2 = config('database.connections.mysql2');
                        if ($hasMysql2) {
                            $Sql = 'SELECT alumno.cedula, alumno.nombre, programa.nombre_programa 
                                    FROM alumno 
                                    INNER JOIN relacion_programa_estudiante ON relacion_programa_estudiante.Alumno_cod = alumno.cod_alumno 
                                    INNER JOIN programa ON programa.cod_programa = relacion_programa_estudiante.programa_cod 
                                    WHERE alumno.cod_alumno = "'.$cod_alumno.'"';
                            $Student = DB::connection('mysql2')->select($Sql);
                            
                            if (!empty($Student) && isset($Student[0])) {
                                $data = $Student;
                            }
                        }
                    } catch (\Exception $e2) {
                        // Silenciosamente fallar si mysql2 no está disponible, ya que es un fallback
                        \Log::warning('Fallback a mysql2 falló en streamCarteraPdf: ' . $e2->getMessage());
                    }
                }
            } catch (\Exception $e) {
                \Log::error('Error buscando datos de estudiante en streamCarteraPdf: ' . $e->getMessage());
            }

            // Obtener todos los costs del estudiante para mostrar todas las cuotas
            // Nota: costs guarda cod_alumno como string
            $cost = DB::table('costs')->where('cod_alumno', $cod_alumno)->orderBy('numero_semestre', 'asc')->get();
            
            // Usar el servicio para calcular cartera con todos los semestres del estudiante
            $carteraData = CarteraService::calcularCartera(null, $cod_alumno);
            
            // Preparar datos para el PDF (mantener compatibilidad con la vista Blade existente)
            $entries = [ (object)['TotalAbono' => $carteraData['totales']['total_abono']] ];
            $purses = [];
            
            // Convertir array de arrays a array de objetos para la vista
            foreach($carteraData['cuotas'] as $cuota) {
                $purses[] = (object)[
                    'id' => $cuota['id'],
                    'id_cost' => $cuota['id_cost'],
                    'numero_semestre' => $cuota['numero_semestre'] ?? 1,
                    'fecha_pago' => $cuota['fecha_pago'],
                    'cuota' => $cuota['cuota'],
                    'abonado' => $cuota['abonado'],
                    'estado_pago' => $cuota['estado_pago'],
                    'estado' => $cuota['estado'],
                    'is_vencida' => $cuota['is_vencida'],
                    'comentario' => $cuota['comentario'] ?? ''
                ];
            }
            
            // Obtener configuración de la institución
            $institucion = InstitutionSetting::getSettings();
            
            // ID cost ficticio para la vista (no se usa realmente si pasamos $purses y $cost)
            $id_cost_ref = count($cost) > 0 ? $cost[0]->id : 0;

            // Generar el PDF
            $dompdf = new Dompdf();
            $html = view('PDFs.pdf_cartera', [
                'id_cost' => $id_cost_ref,
                'student' => $data,
                'cost' => $cost,
                'entries' => $entries,
                'purses' => $purses,
                'totales' => $carteraData['totales'], // Pasar totales calculados
                'hoy' => $carteraData['hoy'], // Pasar fecha de hoy
                'institucion' => $institucion // Pasar configuración de institución
            ])->render();
            
            $dompdf->loadHtml($html);
            $dompdf->setPaper('A4', 'portrait');
            $dompdf->render();
            
            // Nombre del archivo
            $nombreEstudiante = 'Estudiante';
            if (!empty($data) && isset($data[0]) && isset($data[0]->nombre)) {
                $nombreEstudiante = Str::slug($data[0]->nombre); // Limpiar nombre para filename
            }
            
            $filename = 'informe-cartera-' . $nombreEstudiante . '.pdf';
            
            // Obtener el PDF como string binario
            $output = $dompdf->output();
            
            return response($output, 200)
                ->header('Content-Type', 'application/pdf')
                ->header('Content-Disposition', 'inline; filename="' . $filename . '"');
            
        } catch (\Exception $e) {
            \Log::error('Error en PurseController::streamCarteraPdf', [
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString()
            ]);
            
            abort(500, 'Error al generar el documento PDF: ' . $e->getMessage());
        }
    }

    /**
     * Helper para ajustar fechas (lógica interna)
     */
    private function validateAndAdjustDate($year, $month, $day)

    {
        if (checkdate($month, $day, $year)) {
            return sprintf('%04d-%02d-%02d', $year, $month, $day);
        }
        $lastDay = date('t', mktime(0, 0, 0, $month, 1, $year));
        return sprintf('%04d-%02d-%02d', $year, $month, $lastDay);
    }
}
