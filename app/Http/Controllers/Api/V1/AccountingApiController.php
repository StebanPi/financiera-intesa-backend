<?php

namespace App\Http\Controllers\Api\V1;

use App\Http\Controllers\Controller;
use App\Http\Requests\Api\V1\DateRangeRequiredRequest;
use App\Http\Requests\Api\V1\DateRangeRequest;
use App\Http\Requests\Api\V1\MonthYearRequest;
use App\Http\Requests\Api\V1\SingleDateRequest;
use App\Models\CashBase;
use App\Models\InitialBalance;
use Illuminate\Support\Facades\DB;
use App\Services\AccountingExcelService;
use App\Services\AccountingReportService;
use App\Support\ApiResponse;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Str;
use OpenApi\Attributes as OA;

class AccountingApiController extends Controller
{
    public function __construct(
        private AccountingReportService $reportService,
        private AccountingExcelService $excelService
    ) {}

    /**
     * GET /api/v1/accounting — Resumen del dashboard de contabilidad (base del día).
     */
    #[
        OA\Get(
            path: '/api/v1/accounting',
            summary: 'Resumen de contabilidad',
            description: 'Obtiene el resumen del dashboard de contabilidad con la base del día actual.',
            tags: ['Accounting'],
            security: [['bearerAuth' => []]],
            responses: [
                new OA\Response(
                    response: 200,
                    description: 'Resumen de contabilidad',
                    content: new OA\JsonContent(
                        properties: [
                            new OA\Property(property: 'data', type: 'object', properties: [
                                new OA\Property(property: 'today_base', nullable: true, type: 'object', properties: [
                                    new OA\Property(property: 'fecha', type: 'string', format: 'date', example: '2024-01-15'),
                                    new OA\Property(property: 'base_efectivo', type: 'number', format: 'float', example: 1000000.00),
                                    new OA\Property(property: 'base_banco', type: 'number', format: 'float', example: 2000000.00),
                                ]),
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
        $sede = $request->get('sede_activa', 'BARRANCABERMEJA');
        $todayBase = CashBase::where('fecha', date('Y-m-d'))
            ->where('sede', $sede)
            ->first();
        $data = [
            'today_base' => $todayBase ? [
                'fecha' => $todayBase->fecha->format('Y-m-d'),
                'base_efectivo' => (float) $todayBase->base_efectivo,
                'base_banco' => (float) $todayBase->base_banco,
            ] : null,
        ];
        return ApiResponse::success($data, 'OK');
    }

    /**
     * GET /api/v1/accounting/abonos?fecha_inicio=&fecha_fin=
     */
    #[
        OA\Get(
            path: '/api/v1/accounting/abonos',
            summary: 'Reporte de abonos',
            description: 'Obtiene el reporte de abonos agrupado por programa en el rango de fechas especificado.',
            tags: ['Accounting'],
            security: [['bearerAuth' => []]],
            parameters: [
                new OA\Parameter(name: 'fecha_inicio', in: 'query', required: false, description: 'Fecha de inicio (YYYY-MM-DD)', schema: new OA\Schema(type: 'string', format: 'date')),
                new OA\Parameter(name: 'fecha_fin', in: 'query', required: false, description: 'Fecha final (YYYY-MM-DD)', schema: new OA\Schema(type: 'string', format: 'date')),
            ],
            responses: [
                new OA\Response(
                    response: 200,
                    description: 'Reporte de abonos',
                    content: new OA\JsonContent(
                        properties: [
                            new OA\Property(property: 'data', type: 'object', properties: [
                                new OA\Property(property: 'rows', type: 'array', items: new OA\Items(type: 'object')),
                                new OA\Property(property: 'totals', type: 'object'),
                                new OA\Property(property: 'params', type: 'object'),
                            ]),
                            new OA\Property(property: 'meta', type: 'object'),
                        ]
                    )
                ),
                new OA\Response(response: 401, description: 'No autenticado', content: new OA\JsonContent(ref: '#/components/schemas/ErrorResponse')),
            ]
        )
    ]
    public function abonos(DateRangeRequest $request): JsonResponse
    {
        if ($request->filled('mes')) {
            $year = $request->get('anio', date('Y'));
            $month = $request->get('mes');
            $fechaInicio = \Carbon\Carbon::create($year, $month, 1)->startOfMonth()->toDateString();
            $fechaFin = \Carbon\Carbon::create($year, $month, 1)->endOfMonth()->toDateString();
        } else {
            $fechaInicio = $request->filled('fecha_inicio') ? $request->fecha_inicio : null;
            $fechaFin = $request->filled('fecha_fin') ? $request->fecha_fin : null;
        }
        $sede = $request->get('sede_activa', 'BARRANCABERMEJA');

        $ds = $this->reportService->buildAbonosDataset($fechaInicio, $fechaFin, $sede);
        $rows = [];
        foreach ($ds['grouped'] as $programa => $items) {
            foreach ($items as $i) {
                $rows[] = array_merge($i, ['programa' => $programa]);
            }
        }
        $totals = ['total' => $ds['total'], 'total_rows' => $ds['total_rows'], 'is_partial' => $ds['is_partial']];
        $params = ['fecha_inicio' => $fechaInicio, 'fecha_fin' => $fechaFin];
        return ApiResponse::success(compact('rows', 'totals', 'params'), 'OK', ['total_rows' => $ds['total_rows'], 'is_partial' => $ds['is_partial']]);
    }

    /**
     * GET /api/v1/accounting/otros-ingresos?fecha_inicio=&fecha_fin=
     */
    #[
        OA\Get(
            path: '/api/v1/accounting/otros-ingresos',
            summary: 'Reporte de otros ingresos',
            description: 'Obtiene el reporte de otros ingresos agrupado por concepto y programa.',
            tags: ['Accounting'],
            security: [['bearerAuth' => []]],
            parameters: [
                new OA\Parameter(name: 'fecha_inicio', in: 'query', required: false, description: 'Fecha de inicio (YYYY-MM-DD)', schema: new OA\Schema(type: 'string', format: 'date')),
                new OA\Parameter(name: 'fecha_fin', in: 'query', required: false, description: 'Fecha final (YYYY-MM-DD)', schema: new OA\Schema(type: 'string', format: 'date')),
            ],
            responses: [
                new OA\Response(
                    response: 200,
                    description: 'Reporte de otros ingresos',
                    content: new OA\JsonContent(
                        properties: [
                            new OA\Property(property: 'data', type: 'object', properties: [
                                new OA\Property(property: 'rows', type: 'array', items: new OA\Items(type: 'object')),
                                new OA\Property(property: 'totals', type: 'object'),
                                new OA\Property(property: 'params', type: 'object'),
                            ]),
                        ]
                    )
                ),
                new OA\Response(response: 401, description: 'No autenticado', content: new OA\JsonContent(ref: '#/components/schemas/ErrorResponse')),
            ]
        )
    ]
    public function otrosIngresos(DateRangeRequest $request): JsonResponse
    {
        if ($request->filled('mes')) {
            $year = $request->get('anio', date('Y'));
            $month = $request->get('mes');
            $fechaInicio = \Carbon\Carbon::create($year, $month, 1)->startOfMonth()->toDateString();
            $fechaFin = \Carbon\Carbon::create($year, $month, 1)->endOfMonth()->toDateString();
        } else {
            $fechaInicio = $request->filled('fecha_inicio') ? $request->fecha_inicio : null;
            $fechaFin = $request->filled('fecha_fin') ? $request->fecha_fin : null;
        }
        $sede = $request->get('sede_activa', 'BARRANCABERMEJA');

        $ds = $this->reportService->buildOtrosIngresosDataset($fechaInicio, $fechaFin, $sede);
        $rows = [];
        foreach ($ds['grouped'] as $concepto => $programas) {
            foreach ($programas as $programa => $items) {
                foreach ($items as $i) {
                    $rows[] = array_merge($i, ['programa' => $programa, 'concepto_grupo' => $concepto]);
                }
            }
        }
        $totals = ['total' => $ds['total'], 'total_rows' => $ds['total_rows'], 'is_partial' => $ds['is_partial']];
        $params = ['fecha_inicio' => $fechaInicio, 'fecha_fin' => $fechaFin];
        return ApiResponse::success(compact('rows', 'totals', 'params'), 'OK', ['total_rows' => $ds['total_rows'], 'is_partial' => $ds['is_partial']]);
    }

    /**
     * GET /api/v1/accounting/total-ingresos?fecha_inicio=&fecha_fin=
     */
    #[
        OA\Get(
            path: '/api/v1/accounting/total-ingresos',
            summary: 'Reporte de total de ingresos',
            description: 'Obtiene el reporte consolidado de todos los ingresos en el rango de fechas.',
            tags: ['Accounting'],
            security: [['bearerAuth' => []]],
            parameters: [
                new OA\Parameter(name: 'fecha_inicio', in: 'query', required: false, description: 'Fecha de inicio (YYYY-MM-DD)', schema: new OA\Schema(type: 'string', format: 'date')),
                new OA\Parameter(name: 'fecha_fin', in: 'query', required: false, description: 'Fecha final (YYYY-MM-DD)', schema: new OA\Schema(type: 'string', format: 'date')),
            ],
            responses: [
                new OA\Response(
                    response: 200,
                    description: 'Reporte de total de ingresos',
                    content: new OA\JsonContent(
                        properties: [
                            new OA\Property(property: 'data', type: 'object', properties: [
                                new OA\Property(property: 'rows', type: 'array', items: new OA\Items(type: 'object')),
                                new OA\Property(property: 'totals', type: 'object'),
                                new OA\Property(property: 'params', type: 'object'),
                            ]),
                        ]
                    )
                ),
                new OA\Response(response: 401, description: 'No autenticado', content: new OA\JsonContent(ref: '#/components/schemas/ErrorResponse')),
            ]
        )
    ]
    public function totalIngresos(DateRangeRequest $request): JsonResponse
    {
        if ($request->filled('mes')) {
            $year = $request->get('anio', date('Y'));
            $month = $request->get('mes');
            $fechaInicio = \Carbon\Carbon::create($year, $month, 1)->startOfMonth()->toDateString();
            $fechaFin = \Carbon\Carbon::create($year, $month, 1)->endOfMonth()->toDateString();
        } else {
            $fechaInicio = $request->filled('fecha_inicio') ? $request->fecha_inicio : null;
            $fechaFin = $request->filled('fecha_fin') ? $request->fecha_fin : null;
        }
        $sede = $request->get('sede_activa', 'BARRANCABERMEJA');

        $ds = $this->reportService->buildTotalIngresosDataset($fechaInicio, $fechaFin, $sede);
        $totals = ['total' => $ds['total'], 'total_rows' => $ds['total_rows'], 'is_partial' => $ds['is_partial']];
        $params = ['fecha_inicio' => $fechaInicio, 'fecha_fin' => $fechaFin];
        return ApiResponse::success(['rows' => $ds['entries'], 'totals' => $totals, 'params' => $params], 'OK', ['total_rows' => $ds['total_rows'], 'is_partial' => $ds['is_partial']]);
    }

    /**
     * GET /api/v1/accounting/egresos?fecha_inicio=&fecha_fin=
     */
    #[
        OA\Get(
            path: '/api/v1/accounting/egresos',
            summary: 'Reporte de egresos',
            description: 'Obtiene el reporte de egresos en el rango de fechas especificado.',
            tags: ['Accounting'],
            security: [['bearerAuth' => []]],
            parameters: [
                new OA\Parameter(name: 'fecha_inicio', in: 'query', required: false, description: 'Fecha de inicio (YYYY-MM-DD)', schema: new OA\Schema(type: 'string', format: 'date')),
                new OA\Parameter(name: 'fecha_fin', in: 'query', required: false, description: 'Fecha final (YYYY-MM-DD)', schema: new OA\Schema(type: 'string', format: 'date')),
            ],
            responses: [
                new OA\Response(
                    response: 200,
                    description: 'Reporte de egresos',
                    content: new OA\JsonContent(
                        properties: [
                            new OA\Property(property: 'data', type: 'object', properties: [
                                new OA\Property(property: 'rows', type: 'array', items: new OA\Items(type: 'object')),
                                new OA\Property(property: 'totals', type: 'object'),
                                new OA\Property(property: 'params', type: 'object'),
                            ]),
                        ]
                    )
                ),
                new OA\Response(response: 401, description: 'No autenticado', content: new OA\JsonContent(ref: '#/components/schemas/ErrorResponse')),
            ]
        )
    ]
    public function egresos(DateRangeRequest $request): JsonResponse
    {
        if ($request->filled('mes')) {
            $year = $request->get('anio', date('Y'));
            $month = $request->get('mes');
            $fechaInicio = \Carbon\Carbon::create($year, $month, 1)->startOfMonth()->toDateString();
            $fechaFin = \Carbon\Carbon::create($year, $month, 1)->endOfMonth()->toDateString();
        } else {
            $fechaInicio = $request->filled('fecha_inicio') ? $request->fecha_inicio : null;
            $fechaFin = $request->filled('fecha_fin') ? $request->fecha_fin : null;
        }
        $sede = $request->get('sede_activa', 'BARRANCABERMEJA');

        $ds = $this->reportService->buildTotalEgresosDataset($fechaInicio, $fechaFin, $sede);
        $totals = ['total' => $ds['total'], 'total_rows' => $ds['total_rows'], 'is_partial' => $ds['is_partial']];
        $params = ['fecha_inicio' => $fechaInicio, 'fecha_fin' => $fechaFin];
        return ApiResponse::success(['rows' => $ds['items'], 'totals' => $totals, 'params' => $params], 'OK', ['total_rows' => $ds['total_rows'], 'is_partial' => $ds['is_partial']]);
    }

    /**
     * GET /api/v1/accounting/arqueo-diario?fecha=YYYY-MM-DD
     */
    #[
        OA\Get(
            path: '/api/v1/accounting/arqueo-diario',
            summary: 'Arqueo diario',
            description: 'Obtiene el arqueo diario para una fecha específica. Requiere que existan las bases diarias.',
            tags: ['Accounting'],
            security: [['bearerAuth' => []]],
            parameters: [
                new OA\Parameter(name: 'fecha', in: 'query', required: true, description: 'Fecha (YYYY-MM-DD)', schema: new OA\Schema(type: 'string', format: 'date', example: '2024-01-15')),
            ],
            responses: [
                new OA\Response(
                    response: 200,
                    description: 'Arqueo diario',
                    content: new OA\JsonContent(
                        properties: [
                            new OA\Property(property: 'data', type: 'object', properties: [
                                new OA\Property(property: 'rows', type: 'array', items: new OA\Items(type: 'object')),
                                new OA\Property(property: 'totals', type: 'object'),
                                new OA\Property(property: 'params', type: 'object'),
                            ]),
                        ]
                    )
                ),
                new OA\Response(response: 401, description: 'No autenticado', content: new OA\JsonContent(ref: '#/components/schemas/ErrorResponse')),
                new OA\Response(response: 422, description: 'Faltan bases diarias', content: new OA\JsonContent(ref: '#/components/schemas/ValidationErrorResponse')),
            ]
        )
    ]
    public function arqueoDiario(SingleDateRequest $request): JsonResponse
    {
        $fecha = $request->fecha;
        $sede = $request->get('sede_activa', 'BARRANCABERMEJA');
        $ds = $this->reportService->buildArqueoDiarioDataset($fecha, $sede);

        if (!empty($ds['missing_dates'])) {
            return ApiResponse::error(
                'VALIDATION_ERROR',
                'Faltan bases diarias para las fechas del rango. Registre las bases en Contabilidad > Bases diarias.',
                ['missing_dates' => array_values($ds['missing_dates'])],
                422
            );
        }

        $totals = ['total_rows' => $ds['total_rows'], 'is_partial' => $ds['is_partial']];
        $params = ['fecha' => $fecha];
        return ApiResponse::success(['rows' => $ds['dates'], 'totals' => $totals, 'params' => $params], 'OK', ['total_rows' => $ds['total_rows'], 'is_partial' => $ds['is_partial']]);
    }

    /**
     * GET /api/v1/accounting/informe-semanal?fecha=YYYY-MM-DD
     */
    #[
        OA\Get(
            path: '/api/v1/accounting/informe-semanal',
            summary: 'Informe semanal',
            description: 'Obtiene el informe semanal de contabilidad. Requiere que esté configurada la base inicial.',
            tags: ['Accounting'],
            security: [['bearerAuth' => []]],
            parameters: [
                new OA\Parameter(name: 'fecha', in: 'query', required: true, description: 'Fecha dentro de la semana (YYYY-MM-DD)', schema: new OA\Schema(type: 'string', format: 'date', example: '2024-01-15')),
            ],
            responses: [
                new OA\Response(
                    response: 200,
                    description: 'Informe semanal',
                    content: new OA\JsonContent(
                        properties: [
                            new OA\Property(property: 'data', type: 'object', properties: [
                                new OA\Property(property: 'rows', type: 'array', items: new OA\Items(type: 'object')),
                                new OA\Property(property: 'totals', type: 'object'),
                                new OA\Property(property: 'params', type: 'object'),
                            ]),
                        ]
                    )
                ),
                new OA\Response(response: 401, description: 'No autenticado', content: new OA\JsonContent(ref: '#/components/schemas/ErrorResponse')),
                new OA\Response(response: 422, description: 'Falta base inicial', content: new OA\JsonContent(ref: '#/components/schemas/ValidationErrorResponse')),
            ]
        )
    ]
    public function informeSemanal(SingleDateRequest $request): JsonResponse
    {
        $fecha = $request->fecha;
        $sede = $request->get('sede_activa', 'BARRANCABERMEJA');
        $ds = $this->reportService->buildInformeSemanalDataset($fecha, $sede);

        if (!empty($ds['missing_initial_base'])) {
            return ApiResponse::error(
                'VALIDATION_ERROR',
                'Falta configurar la base inicial de contabilidad. Configure la base inicial (solo Super Admin).',
                null,
                422
            );
        }

        $totals = $ds['summary'];
        $params = ['fecha' => $fecha, 'startDate' => $ds['startDate'], 'endDate' => $ds['endDate']];
        return ApiResponse::success(['rows' => $ds['rows'], 'totals' => $totals, 'params' => $params], 'OK', ['total_rows' => $ds['total_rows'], 'is_partial' => $ds['is_partial']]);
    }

    /**
     * GET /api/v1/accounting/informe-mensual?month_year=YYYY-MM  o  ?mes=1&anio=2024
     */
    #[
        OA\Get(
            path: '/api/v1/accounting/informe-mensual',
            summary: 'Informe mensual',
            description: 'Obtiene el informe mensual de contabilidad. Requiere que esté configurada la base inicial.',
            tags: ['Accounting'],
            security: [['bearerAuth' => []]],
            parameters: [
                new OA\Parameter(name: 'month_year', in: 'query', required: false, description: 'Año y mes (YYYY-MM)', schema: new OA\Schema(type: 'string', example: '2024-01')),
                new OA\Parameter(name: 'mes', in: 'query', required: false, description: 'Mes (1-12)', schema: new OA\Schema(type: 'integer', example: 1)),
                new OA\Parameter(name: 'anio', in: 'query', required: false, description: 'Año', schema: new OA\Schema(type: 'integer', example: 2024)),
            ],
            responses: [
                new OA\Response(
                    response: 200,
                    description: 'Informe mensual',
                    content: new OA\JsonContent(
                        properties: [
                            new OA\Property(property: 'data', type: 'object', properties: [
                                new OA\Property(property: 'rows', type: 'array', items: new OA\Items(type: 'object')),
                                new OA\Property(property: 'totals', type: 'object'),
                                new OA\Property(property: 'params', type: 'object'),
                            ]),
                        ]
                    )
                ),
                new OA\Response(response: 401, description: 'No autenticado', content: new OA\JsonContent(ref: '#/components/schemas/ErrorResponse')),
                new OA\Response(response: 422, description: 'Falta base inicial', content: new OA\JsonContent(ref: '#/components/schemas/ValidationErrorResponse')),
            ]
        )
    ]
    public function informeMensual(MonthYearRequest $request): JsonResponse
    {
        [$mes, $anio] = $request->getMesAnio();
        $sede = $request->get('sede_activa', 'BARRANCABERMEJA');
        $ds = $this->reportService->buildInformeMensualDataset($mes, $anio, $sede);

        if (!empty($ds['missing_initial_base'])) {
            return ApiResponse::error(
                'VALIDATION_ERROR',
                'Falta configurar la base inicial de contabilidad. Configure la base inicial (solo Super Admin).',
                null,
                422
            );
        }

        $totals = $ds['summary'];
        $params = ['mes' => $mes, 'anio' => $anio, 'startDate' => $ds['startDate'], 'endDate' => $ds['endDate']];
        return ApiResponse::success(['rows' => $ds['rows'], 'totals' => $totals, 'params' => $params], 'OK', ['total_rows' => $ds['total_rows'], 'is_partial' => $ds['is_partial']]);
    }

    // --- Downloads Excel (stream xlsx). Reutilizan AccountingExcelService. ---

    /**
     * GET /api/v1/accounting/abonos/download?fecha_inicio=&fecha_fin=
     */
    #[
        OA\Get(
            path: '/api/v1/accounting/abonos/download',
            summary: 'Descargar reporte de abonos (XLSX)',
            description: 'Genera y descarga un archivo Excel (XLSX) con el reporte de abonos en el rango de fechas especificado. El archivo se descarga como binario.',
            tags: ['Accounting'],
            security: [['bearerAuth' => []]],
            parameters: [
                new OA\Parameter(
                    name: 'fecha_inicio',
                    in: 'query',
                    required: true,
                    description: 'Fecha de inicio del reporte (YYYY-MM-DD)',
                    schema: new OA\Schema(type: 'string', format: 'date', example: '2024-01-01')
                ),
                new OA\Parameter(
                    name: 'fecha_fin',
                    in: 'query',
                    required: true,
                    description: 'Fecha final del reporte (YYYY-MM-DD). Debe ser igual o posterior a fecha_inicio.',
                    schema: new OA\Schema(type: 'string', format: 'date', example: '2024-01-31')
                ),
            ],
            responses: [
                new OA\Response(
                    response: 200,
                    description: 'Archivo Excel descargado exitosamente',
                    content: new OA\MediaType(
                        mediaType: 'application/vnd.openxmlformats-officedocument.spreadsheetml.sheet',
                        schema: new OA\Schema(
                            type: 'string',
                            format: 'binary'
                        )
                    ),
                    headers: [
                        new OA\Header(
                            header: 'Content-Disposition',
                            schema: new OA\Schema(type: 'string'),
                            description: 'attachment; filename="Informe de Abonos 2024-01-01 a 2024-01-31.xlsx"'
                        ),
                    ]
                ),
                new OA\Response(
                    response: 401,
                    description: 'No autenticado',
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
    public function abonosDownload(DateRangeRequiredRequest $request)
    {
        try {
            $sede = $request->get('sede_activa', 'BARRANCABERMEJA');
            $r = $this->excelService->generateAbonosReport($request->fecha_inicio, $request->fecha_fin, $sede);
            $r->headers->set('Content-Type', 'application/vnd.openxmlformats-officedocument.spreadsheetml.sheet');
            return $r;
        } catch (\Throwable $e) {
            return $this->handleExcelError($e, $request);
        }
    }

    /**
     * GET /api/v1/accounting/otros-ingresos/download?fecha_inicio=&fecha_fin=
     */
    #[
        OA\Get(
            path: '/api/v1/accounting/otros-ingresos/download',
            summary: 'Descargar reporte de otros ingresos (XLSX)',
            description: 'Genera y descarga un archivo Excel con el reporte de otros ingresos.',
            tags: ['Accounting'],
            security: [['bearerAuth' => []]],
            parameters: [
                new OA\Parameter(name: 'fecha_inicio', in: 'query', required: true, description: 'Fecha de inicio (YYYY-MM-DD)', schema: new OA\Schema(type: 'string', format: 'date')),
                new OA\Parameter(name: 'fecha_fin', in: 'query', required: true, description: 'Fecha final (YYYY-MM-DD)', schema: new OA\Schema(type: 'string', format: 'date')),
            ],
            responses: [
                new OA\Response(
                    response: 200,
                    description: 'Archivo Excel descargado',
                    content: new OA\MediaType(
                        mediaType: 'application/vnd.openxmlformats-officedocument.spreadsheetml.sheet',
                        schema: new OA\Schema(type: 'string', format: 'binary')
                    ),
                    headers: [
                        new OA\Header(header: 'Content-Disposition', schema: new OA\Schema(type: 'string'), description: 'attachment; filename="..."'),
                    ]
                ),
                new OA\Response(response: 401, description: 'No autenticado', content: new OA\JsonContent(ref: '#/components/schemas/ErrorResponse')),
                new OA\Response(response: 422, description: 'Errores de validación', content: new OA\JsonContent(ref: '#/components/schemas/ValidationErrorResponse')),
                new OA\Response(response: 500, description: 'Error interno', content: new OA\JsonContent(ref: '#/components/schemas/ErrorResponse')),
            ]
        )
    ]
    public function otrosIngresosDownload(DateRangeRequiredRequest $request)
    {
        try {
            $sede = $request->get('sede_activa', 'BARRANCABERMEJA');
            $r = $this->excelService->generateOtrosIngresosReport($request->fecha_inicio, $request->fecha_fin, $sede);
            $r->headers->set('Content-Type', 'application/vnd.openxmlformats-officedocument.spreadsheetml.sheet');
            return $r;
        } catch (\Throwable $e) {
            return $this->handleExcelError($e, $request);
        }
    }

    /**
     * GET /api/v1/accounting/total-ingresos/download?fecha_inicio=&fecha_fin=
     */
    #[
        OA\Get(
            path: '/api/v1/accounting/total-ingresos/download',
            summary: 'Descargar reporte de total de ingresos (XLSX)',
            description: 'Genera y descarga un archivo Excel con el reporte consolidado de ingresos.',
            tags: ['Accounting'],
            security: [['bearerAuth' => []]],
            parameters: [
                new OA\Parameter(name: 'fecha_inicio', in: 'query', required: true, description: 'Fecha de inicio (YYYY-MM-DD)', schema: new OA\Schema(type: 'string', format: 'date')),
                new OA\Parameter(name: 'fecha_fin', in: 'query', required: true, description: 'Fecha final (YYYY-MM-DD)', schema: new OA\Schema(type: 'string', format: 'date')),
            ],
            responses: [
                new OA\Response(
                    response: 200,
                    description: 'Archivo Excel descargado',
                    content: new OA\MediaType(
                        mediaType: 'application/vnd.openxmlformats-officedocument.spreadsheetml.sheet',
                        schema: new OA\Schema(type: 'string', format: 'binary')
                    ),
                    headers: [
                        new OA\Header(header: 'Content-Disposition', schema: new OA\Schema(type: 'string'), description: 'attachment; filename="..."'),
                    ]
                ),
                new OA\Response(response: 401, description: 'No autenticado', content: new OA\JsonContent(ref: '#/components/schemas/ErrorResponse')),
                new OA\Response(response: 422, description: 'Errores de validación', content: new OA\JsonContent(ref: '#/components/schemas/ValidationErrorResponse')),
                new OA\Response(response: 500, description: 'Error interno', content: new OA\JsonContent(ref: '#/components/schemas/ErrorResponse')),
            ]
        )
    ]
    public function totalIngresosDownload(DateRangeRequiredRequest $request)
    {
        try {
            $sede = $request->get('sede_activa', 'BARRANCABERMEJA');
            $r = $this->excelService->generateTotalIngresosReport($request->fecha_inicio, $request->fecha_fin, $sede);
            $r->headers->set('Content-Type', 'application/vnd.openxmlformats-officedocument.spreadsheetml.sheet');
            return $r;
        } catch (\Throwable $e) {
            return $this->handleExcelError($e, $request);
        }
    }

    /**
     * GET /api/v1/accounting/egresos/download?fecha_inicio=&fecha_fin=
     */
    #[
        OA\Get(
            path: '/api/v1/accounting/egresos/download',
            summary: 'Descargar reporte de egresos (XLSX)',
            description: 'Genera y descarga un archivo Excel con el reporte de egresos.',
            tags: ['Accounting'],
            security: [['bearerAuth' => []]],
            parameters: [
                new OA\Parameter(name: 'fecha_inicio', in: 'query', required: true, description: 'Fecha de inicio (YYYY-MM-DD)', schema: new OA\Schema(type: 'string', format: 'date')),
                new OA\Parameter(name: 'fecha_fin', in: 'query', required: true, description: 'Fecha final (YYYY-MM-DD)', schema: new OA\Schema(type: 'string', format: 'date')),
            ],
            responses: [
                new OA\Response(
                    response: 200,
                    description: 'Archivo Excel descargado',
                    content: new OA\MediaType(
                        mediaType: 'application/vnd.openxmlformats-officedocument.spreadsheetml.sheet',
                        schema: new OA\Schema(type: 'string', format: 'binary')
                    ),
                    headers: [
                        new OA\Header(header: 'Content-Disposition', schema: new OA\Schema(type: 'string'), description: 'attachment; filename="..."'),
                    ]
                ),
                new OA\Response(response: 401, description: 'No autenticado', content: new OA\JsonContent(ref: '#/components/schemas/ErrorResponse')),
                new OA\Response(response: 422, description: 'Errores de validación', content: new OA\JsonContent(ref: '#/components/schemas/ValidationErrorResponse')),
                new OA\Response(response: 500, description: 'Error interno', content: new OA\JsonContent(ref: '#/components/schemas/ErrorResponse')),
            ]
        )
    ]
    public function egresosDownload(DateRangeRequiredRequest $request)
    {
        try {
            $sede = $request->get('sede_activa', 'BARRANCABERMEJA');
            $r = $this->excelService->generateTotalEgresosReport($request->fecha_inicio, $request->fecha_fin, $sede);
            $r->headers->set('Content-Type', 'application/vnd.openxmlformats-officedocument.spreadsheetml.sheet');
            return $r;
        } catch (\Throwable $e) {
            return $this->handleExcelError($e, $request);
        }
    }

    /**
     * GET /api/v1/accounting/arqueo-diario/download?fecha=YYYY-MM-DD
     */
    #[
        OA\Get(
            path: '/api/v1/accounting/arqueo-diario/download',
            summary: 'Descargar arqueo diario (XLSX)',
            description: 'Genera y descarga un archivo Excel con el arqueo diario.',
            tags: ['Accounting'],
            security: [['bearerAuth' => []]],
            parameters: [
                new OA\Parameter(name: 'fecha', in: 'query', required: true, description: 'Fecha (YYYY-MM-DD)', schema: new OA\Schema(type: 'string', format: 'date')),
            ],
            responses: [
                new OA\Response(
                    response: 200,
                    description: 'Archivo Excel descargado',
                    content: new OA\MediaType(
                        mediaType: 'application/vnd.openxmlformats-officedocument.spreadsheetml.sheet',
                        schema: new OA\Schema(type: 'string', format: 'binary')
                    ),
                    headers: [
                        new OA\Header(header: 'Content-Disposition', schema: new OA\Schema(type: 'string'), description: 'attachment; filename="..."'),
                    ]
                ),
                new OA\Response(response: 401, description: 'No autenticado', content: new OA\JsonContent(ref: '#/components/schemas/ErrorResponse')),
                new OA\Response(response: 422, description: 'Faltan bases diarias', content: new OA\JsonContent(ref: '#/components/schemas/ValidationErrorResponse')),
                new OA\Response(response: 500, description: 'Error interno', content: new OA\JsonContent(ref: '#/components/schemas/ErrorResponse')),
            ]
        )
    ]
    public function arqueoDiarioDownload(SingleDateRequest $request)
    {
        try {
            $sede = $request->get('sede_activa', 'BARRANCABERMEJA');
            $r = $this->excelService->generateArqueoDiario($request->fecha, $sede);
            $r->headers->set('Content-Type', 'application/vnd.openxmlformats-officedocument.spreadsheetml.sheet');
            return $r;
        } catch (\Throwable $e) {
            return $this->handleExcelError($e, $request);
        }
    }

    /**
     * GET /api/v1/accounting/informe-semanal/download?fecha=YYYY-MM-DD
     */
    #[
        OA\Get(
            path: '/api/v1/accounting/informe-semanal/download',
            summary: 'Descargar informe semanal (XLSX)',
            description: 'Genera y descarga un archivo Excel con el informe semanal.',
            tags: ['Accounting'],
            security: [['bearerAuth' => []]],
            parameters: [
                new OA\Parameter(name: 'fecha', in: 'query', required: true, description: 'Fecha (YYYY-MM-DD)', schema: new OA\Schema(type: 'string', format: 'date')),
            ],
            responses: [
                new OA\Response(
                    response: 200,
                    description: 'Archivo Excel descargado',
                    content: new OA\MediaType(
                        mediaType: 'application/vnd.openxmlformats-officedocument.spreadsheetml.sheet',
                        schema: new OA\Schema(type: 'string', format: 'binary')
                    ),
                    headers: [
                        new OA\Header(header: 'Content-Disposition', schema: new OA\Schema(type: 'string'), description: 'attachment; filename="..."'),
                    ]
                ),
                new OA\Response(response: 401, description: 'No autenticado', content: new OA\JsonContent(ref: '#/components/schemas/ErrorResponse')),
                new OA\Response(response: 422, description: 'Falta base inicial', content: new OA\JsonContent(ref: '#/components/schemas/ValidationErrorResponse')),
                new OA\Response(response: 500, description: 'Error interno', content: new OA\JsonContent(ref: '#/components/schemas/ErrorResponse')),
            ]
        )
    ]
    public function informeSemanalDownload(SingleDateRequest $request)
    {
        try {
            $sede = $request->get('sede_activa', 'BARRANCABERMEJA');
            $r = $this->excelService->generateInformeSemanal($request->fecha, $sede);
            $r->headers->set('Content-Type', 'application/vnd.openxmlformats-officedocument.spreadsheetml.sheet');
            return $r;
        } catch (\Throwable $e) {
            return $this->handleExcelError($e, $request);
        }
    }

    /**
     * GET /api/v1/accounting/informe-mensual/download?month_year=YYYY-MM  o  ?mes=1&anio=2024
     */
    #[
        OA\Get(
            path: '/api/v1/accounting/informe-mensual/download',
            summary: 'Descargar informe mensual (XLSX)',
            description: 'Genera y descarga un archivo Excel con el informe mensual.',
            tags: ['Accounting'],
            security: [['bearerAuth' => []]],
            parameters: [
                new OA\Parameter(name: 'month_year', in: 'query', required: false, description: 'Año y mes (YYYY-MM)', schema: new OA\Schema(type: 'string')),
                new OA\Parameter(name: 'mes', in: 'query', required: false, description: 'Mes (1-12)', schema: new OA\Schema(type: 'integer')),
                new OA\Parameter(name: 'anio', in: 'query', required: false, description: 'Año', schema: new OA\Schema(type: 'integer')),
            ],
            responses: [
                new OA\Response(
                    response: 200,
                    description: 'Archivo Excel descargado',
                    content: new OA\MediaType(
                        mediaType: 'application/vnd.openxmlformats-officedocument.spreadsheetml.sheet',
                        schema: new OA\Schema(type: 'string', format: 'binary')
                    ),
                    headers: [
                        new OA\Header(header: 'Content-Disposition', schema: new OA\Schema(type: 'string'), description: 'attachment; filename="..."'),
                    ]
                ),
                new OA\Response(response: 401, description: 'No autenticado', content: new OA\JsonContent(ref: '#/components/schemas/ErrorResponse')),
                new OA\Response(response: 422, description: 'Falta base inicial', content: new OA\JsonContent(ref: '#/components/schemas/ValidationErrorResponse')),
                new OA\Response(response: 500, description: 'Error interno', content: new OA\JsonContent(ref: '#/components/schemas/ErrorResponse')),
            ]
        )
    ]
    public function informeMensualDownload(MonthYearRequest $request)
    {
        try {
            [$mes, $anio] = $request->getMesAnio();
            $sede = $request->get('sede_activa', 'BARRANCABERMEJA');
            $r = $this->excelService->generateInformeMensual($mes, $anio, $sede);
            $r->headers->set('Content-Type', 'application/vnd.openxmlformats-officedocument.spreadsheetml.sheet');
            return $r;
        } catch (\Throwable $e) {
            return $this->handleExcelError($e, $request);
        }
    }

    /**
     * GET /api/v1/accounting/initial-balance
     * Obtiene la configuración de la base inicial.
     */
    #[
        OA\Get(
            path: '/api/v1/accounting/initial-balance',
            summary: 'Obtener base inicial',
            description: 'Obtiene la configuración actual de la base inicial de contabilidad.',
            tags: ['Accounting'],
            security: [['bearerAuth' => []]],
            responses: [
                new OA\Response(
                    response: 200,
                    description: 'Configuración de base inicial',
                    content: new OA\JsonContent(
                        properties: [
                            new OA\Property(property: 'data', type: 'object', properties: [
                                new OA\Property(property: 'exists', type: 'boolean'),
                                new OA\Property(property: 'data', type: 'object', nullable: true),
                            ]),
                        ]
                    )
                ),
            ]
        )
    ]
    public function initialBalance(Request $request): JsonResponse
    {
        $sede = $request->get('sede_activa', 'BARRANCABERMEJA');
        $initial = InitialBalance::getActive($sede);
        return ApiResponse::success([
            'exists' => (bool) $initial,
            'data' => $initial
        ], 'OK');
    }

    /**
     * POST /api/v1/accounting/initial-balance
     * Configura o actualiza la base inicial.
     */
    #[
        OA\Post(
            path: '/api/v1/accounting/initial-balance',
            summary: 'Configurar base inicial',
            description: 'Crea o actualiza la base inicial de contabilidad. Solo debe existir una activa.',
            tags: ['Accounting'],
            security: [['bearerAuth' => []]],
            requestBody: new OA\RequestBody(
                required: true,
                content: new OA\JsonContent(
                    required: ['start_date', 'base_efectivo', 'base_banco'],
                    properties: [
                        new OA\Property(property: 'start_date', type: 'string', format: 'date'),
                        new OA\Property(property: 'base_efectivo', type: 'number'),
                        new OA\Property(property: 'base_banco', type: 'number'),
                    ]
                )
            ),
            responses: [
                new OA\Response(response: 200, description: 'Base inicial configurada'),
            ]
        )
    ]
    public function storeInitialBalance(Request $request): JsonResponse
    {
        $request->validate([
            'start_date' => 'required|date',
            'base_efectivo' => 'required|numeric|min:0',
            'base_banco' => 'required|numeric|min:0',
            'sede' => 'required|string'
        ]);

        $sede = $request->sede;
        $initialBalance = InitialBalance::getActive($sede);

        if ($initialBalance) {
            $initialBalance->update([
                'start_date' => $request->start_date,
                'base_efectivo' => $request->base_efectivo,
                'base_banco' => $request->base_banco,
            ]);
        } else {
            InitialBalance::create([
                'start_date' => $request->start_date,
                'base_efectivo' => $request->base_efectivo,
                'base_banco' => $request->base_banco,
                'created_by' => auth()->id(),
                'sede' => $sede,
            ]);
        }

        return ApiResponse::success(null, 'Base inicial configurada correctamente');
    }

    /**
     * GET /api/v1/accounting/cash-bases?start_date=&end_date=
     * Obtiene las bases diarias existentes en un rango.
     */
    #[
        OA\Get(
            path: '/api/v1/accounting/cash-bases',
            summary: 'Obtener bases diarias',
            description: 'Obtiene las bases diarias registradas en un rango de fechas.',
            tags: ['Accounting'],
            security: [['bearerAuth' => []]],
            parameters: [
                new OA\Parameter(name: 'start_date', in: 'query', required: false, schema: new OA\Schema(type: 'string', format: 'date')),
                new OA\Parameter(name: 'end_date', in: 'query', required: false, schema: new OA\Schema(type: 'string', format: 'date')),
            ],
            responses: [
                new OA\Response(response: 200, description: 'Lista de bases diarias')
            ]
        )
    ]
    public function cashBases(Request $request): JsonResponse
    {
        $startDate = $request->get('start_date', date('Y-m-01'));
        $endDate = $request->get('end_date', date('Y-m-t'));
        $sede = $request->get('sede_activa', 'BARRANCABERMEJA');

        $bases = CashBase::whereBetween('fecha', [$startDate, $endDate])
            ->where('sede', $sede)
            ->orderBy('fecha')
            ->get();

        return ApiResponse::success(['bases' => $bases], 'OK');
    }

    /**
     * POST /api/v1/accounting/cash-bases
     * Guarda o actualiza múltiples bases diarias.
     */
    #[
        OA\Post(
            path: '/api/v1/accounting/cash-bases',
            summary: 'Guardar bases diarias',
            description: 'Guarda o actualiza bases diarias en lote.',
            tags: ['Accounting'],
            security: [['bearerAuth' => []]],
            requestBody: new OA\RequestBody(
                required: true,
                content: new OA\JsonContent(
                    required: ['bases'],
                    properties: [
                        new OA\Property(
                            property: 'bases',
                            type: 'array',
                            items: new OA\Items(properties: [
                                new OA\Property(property: 'fecha', type: 'string', format: 'date'),
                                new OA\Property(property: 'base_efectivo', type: 'number'),
                                new OA\Property(property: 'base_banco', type: 'number'),
                            ])
                        ),
                    ]
                )
            ),
            responses: [
                new OA\Response(response: 200, description: 'Bases guardadas'),
            ]
        )
    ]
    public function storeCashBases(Request $request): JsonResponse
    {
        $request->validate([
            'bases' => 'required|array|min:1',
            'bases.*.fecha' => 'required|date',
            'bases.*.base_efectivo' => 'required|numeric|min:0',
            'bases.*.base_banco' => 'required|numeric|min:0',
            'sede' => 'required|string'
        ]);

        $sede = $request->sede;
        $saved = 0;
        
        DB::beginTransaction();
        try {
            foreach ($request->bases as $base) {
                CashBase::updateOrCreate(
                    [
                        'fecha' => $base['fecha'],
                        'sede' => $sede
                    ],
                    [
                        'base_efectivo' => $base['base_efectivo'],
                        'base_banco' => $base['base_banco']
                    ]
                );
                $saved++;
            }
            DB::commit();
        } catch (\Exception $e) {
            DB::rollBack();
            return ApiResponse::error('SERVER_ERROR', 'Error al guardar las bases: ' . $e->getMessage(), null, 500);
        }

        return ApiResponse::success(null, "Se guardaron {$saved} base(s) diaria(s) correctamente.");
    }

    /**
     * Convierte excepciones del Excel en 422 (missing_dates, base inicial) o 500 (trace_id).
     * @param \Throwable $e
     * @param Request $request
     * @return JsonResponse
     */
    private function handleExcelError(\Throwable $e, Request $request): JsonResponse
    {
        $msg = $e->getMessage();

        if (str_contains($msg, 'Faltan bases diarias')) {
            $part = explode(':', $msg, 2)[1] ?? '';
            $missingDates = array_values(array_map('trim', explode(',', trim($part))));
            return ApiResponse::error(
                'VALIDATION_ERROR',
                'Faltan bases diarias para las fechas del rango. Registre las bases en Contabilidad > Bases diarias.',
                ['missing_dates' => $missingDates],
                422
            );
        }

        if (str_contains($msg, 'base inicial')) {
            return ApiResponse::error(
                'VALIDATION_ERROR',
                'Falta configurar la base inicial de contabilidad. Configure la base inicial (solo Super Admin).',
                null,
                422
            );
        }

        \Log::error('Error al generar Excel (API Contabilidad): ' . $e->getMessage());
        \Log::error('Stack trace: ' . $e->getTraceAsString());
        // Usar request_id del middleware RequestIdMiddleware
        $traceId = $request->attributes->get('request_id') ?? $request->header('X-Request-Id') ?? Str::uuid()->toString();
        $message = config('app.debug') ? 'Error al generar el Excel: ' . $e->getMessage() : 'Error al generar el Excel.';
        return ApiResponse::error('SERVER_ERROR', $message, null, 500, ['trace_id' => $traceId]);
    }
}
