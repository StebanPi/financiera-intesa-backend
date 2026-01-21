<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Services\AccountingExcelService;
use App\Services\AccountingReportService;
use App\Models\CashBase;
use App\Models\InitialBalance;
use Carbon\Carbon;
use Illuminate\Support\Facades\DB;

class AccountingController extends Controller
{
    protected $excelService;
    protected $reportService;

    public function __construct(AccountingExcelService $excelService, AccountingReportService $reportService)
    {
        $this->excelService = $excelService;
        $this->reportService = $reportService;
    }

    /**
     * Vista principal de Contabilidad
     */
    public function index()
    {
        // Obtener la base diaria del día actual
        $todayBase = CashBase::where('fecha', date('Y-m-d'))->first();
        
        return view('accounting.index', [
            'todayBase' => $todayBase
        ]);
    }

    /**
     * Vista: Informe de Abonos
     */
    public function abonosView(Request $request)
    {
        $fechaInicio = $request->get('fecha_inicio');
        $fechaFin = $request->get('fecha_fin');

        // Validar solo si se proporcionan fechas
        if ($fechaInicio || $fechaFin) {
            $request->validate([
                'fecha_inicio' => 'required_with:fecha_fin|date',
                'fecha_fin' => 'required_with:fecha_inicio|date|after_or_equal:fecha_inicio'
            ]);
        }

        try {
            $dataset = $this->reportService->buildAbonosDataset($fechaInicio, $fechaFin);
        } catch (\Exception $e) {
            return redirect()->route('accounting.abonos')
                ->with('error', 'Error al generar preview: ' . $e->getMessage())
                ->withInput();
        }

        return view('accounting.abonos', [
            'dataset' => $dataset,
            'fecha_inicio' => $fechaInicio,
            'fecha_fin' => $fechaFin,
        ]);
    }

    /**
     * Descarga: Informe de Abonos
     */
    public function abonosDownload(Request $request)
    {
        $request->validate([
            'fecha_inicio' => 'required|date',
            'fecha_fin' => 'required|date|after_or_equal:fecha_inicio'
        ]);

        try {
            return $this->excelService->generateAbonosReport(
                $request->fecha_inicio,
                $request->fecha_fin
            );
        } catch (\Exception $e) {
            return redirect()->route('accounting.abonos')
                ->with('error', 'Error al generar reporte: ' . $e->getMessage())
                ->withInput();
        }
    }

    /**
     * Vista: Informe de Otros Ingresos
     */
    public function otrosIngresosView(Request $request)
    {
        $fechaInicio = $request->get('fecha_inicio');
        $fechaFin = $request->get('fecha_fin');

        // Validar solo si se proporcionan fechas
        if ($fechaInicio || $fechaFin) {
            $request->validate([
                'fecha_inicio' => 'required_with:fecha_fin|date',
                'fecha_fin' => 'required_with:fecha_inicio|date|after_or_equal:fecha_inicio'
            ]);
        }

        try {
            $dataset = $this->reportService->buildOtrosIngresosDataset($fechaInicio, $fechaFin);
        } catch (\Exception $e) {
            return redirect()->route('accounting.otros-ingresos')
                ->with('error', 'Error al generar preview: ' . $e->getMessage())
                ->withInput();
        }

        return view('accounting.otros_ingresos', [
            'dataset' => $dataset,
            'fecha_inicio' => $fechaInicio,
            'fecha_fin' => $fechaFin,
        ]);
    }

    /**
     * Descarga: Informe de Otros Ingresos
     */
    public function otrosIngresosDownload(Request $request)
    {
        $request->validate([
            'fecha_inicio' => 'required|date',
            'fecha_fin' => 'required|date|after_or_equal:fecha_inicio'
        ]);

        try {
            return $this->excelService->generateOtrosIngresosReport(
                $request->fecha_inicio,
                $request->fecha_fin
            );
        } catch (\Exception $e) {
            return redirect()->route('accounting.otros-ingresos')
                ->with('error', 'Error al generar reporte: ' . $e->getMessage())
                ->withInput();
        }
    }

    /**
     * Vista: Informe Total Ingresos
     */
    public function totalIngresosView(Request $request)
    {
        $fechaInicio = $request->get('fecha_inicio');
        $fechaFin = $request->get('fecha_fin');

        // Validar solo si se proporcionan fechas
        if ($fechaInicio || $fechaFin) {
            $request->validate([
                'fecha_inicio' => 'required_with:fecha_fin|date',
                'fecha_fin' => 'required_with:fecha_inicio|date|after_or_equal:fecha_inicio'
            ]);
        }

        try {
            $dataset = $this->reportService->buildTotalIngresosDataset($fechaInicio, $fechaFin);
        } catch (\Exception $e) {
            return redirect()->route('accounting.total-ingresos')
                ->with('error', 'Error al generar preview: ' . $e->getMessage())
                ->withInput();
        }

        return view('accounting.total_ingresos', [
            'dataset' => $dataset,
            'fecha_inicio' => $fechaInicio,
            'fecha_fin' => $fechaFin,
        ]);
    }

    /**
     * Descarga: Informe Total Ingresos
     */
    public function totalIngresosDownload(Request $request)
    {
        $request->validate([
            'fecha_inicio' => 'required|date',
            'fecha_fin' => 'required|date|after_or_equal:fecha_inicio'
        ]);

        try {
            return $this->excelService->generateTotalIngresosReport(
                $request->fecha_inicio,
                $request->fecha_fin
            );
        } catch (\Exception $e) {
            return redirect()->route('accounting.total-ingresos')
                ->with('error', 'Error al generar reporte: ' . $e->getMessage())
                ->withInput();
        }
    }

    /**
     * Vista: Informe Total Egresos
     */
    public function totalEgresosView(Request $request)
    {
        $fechaInicio = $request->get('fecha_inicio');
        $fechaFin = $request->get('fecha_fin');

        // Validar solo si se proporcionan fechas
        if ($fechaInicio || $fechaFin) {
            $request->validate([
                'fecha_inicio' => 'required_with:fecha_fin|date',
                'fecha_fin' => 'required_with:fecha_inicio|date|after_or_equal:fecha_inicio'
            ]);
        }

        try {
            $dataset = $this->reportService->buildTotalEgresosDataset($fechaInicio, $fechaFin);
        } catch (\Exception $e) {
            return redirect()->route('accounting.total-egresos')
                ->with('error', 'Error al generar preview: ' . $e->getMessage())
                ->withInput();
        }

        return view('accounting.egresos', [
            'dataset' => $dataset,
            'fecha_inicio' => $fechaInicio,
            'fecha_fin' => $fechaFin,
        ]);
    }

    /**
     * Descarga: Informe Total Egresos
     */
    public function totalEgresosDownload(Request $request)
    {
        $request->validate([
            'fecha_inicio' => 'required|date',
            'fecha_fin' => 'required|date|after_or_equal:fecha_inicio'
        ]);

        try {
            return $this->excelService->generateTotalEgresosReport(
                $request->fecha_inicio,
                $request->fecha_fin
            );
        } catch (\Exception $e) {
            return redirect()->route('accounting.total-egresos')
                ->with('error', 'Error al generar reporte: ' . $e->getMessage())
                ->withInput();
        }
    }

    /**
     * Vista: Arqueo Diario
     */
    public function arqueoDiarioView(Request $request)
    {
        $dataset = null;
        $cashBase = null;
        $fecha = $request->get('fecha');
        
        if ($request->has('fecha')) {
            $request->validate([
                'fecha' => 'required|date'
            ]);

            // Obtener la base diaria del día seleccionado
            $cashBase = CashBase::where('fecha', $fecha)->first();

            try {
                $dataset = $this->reportService->buildArqueoDiarioDataset($fecha);
            } catch (\Exception $e) {
                return redirect()->route('accounting.arqueo-diario')
                    ->with('error', 'Error al generar preview: ' . $e->getMessage())
                    ->withInput();
            }
        }

        return view('accounting.arqueo_diario', [
            'dataset' => $dataset,
            'fecha' => $fecha,
            'cashBase' => $cashBase,
        ]);
    }

    /**
     * Descarga: Arqueo Diario
     */
    public function arqueoDiarioDownload(Request $request)
    {
        $request->validate([
            'fecha' => 'required|date'
        ]);

        try {
            return $this->excelService->generateArqueoDiario($request->fecha);
        } catch (\Exception $e) {
            if (str_contains($e->getMessage(), 'Faltan bases diarias')) {
                $message = $e->getMessage();
                $missingDatesStr = explode(':', $message)[1] ?? '';
                $missingDates = array_map('trim', explode(',', trim($missingDatesStr)));
                return redirect()->route('accounting.cash-bases')
                    ->with('missing_dates', $missingDates)
                    ->with('error', $e->getMessage());
            }
            return redirect()->route('accounting.arqueo-diario')
                ->with('error', 'Error al generar reporte: ' . $e->getMessage())
                ->withInput();
        }
    }

    /**
     * Vista: Informe Semanal
     */
    public function informeSemanalView(Request $request)
    {
        $dataset = null;
        if ($request->has('fecha')) {
            $request->validate([
                'fecha' => 'required|date'
            ]);

            try {
                $dataset = $this->reportService->buildInformeSemanalDataset($request->fecha);
            } catch (\Exception $e) {
                return redirect()->route('accounting.informe-semanal')
                    ->with('error', 'Error al generar preview: ' . $e->getMessage())
                    ->withInput();
            }
        }

        return view('accounting.semanal', [
            'dataset' => $dataset,
            'fecha' => $request->get('fecha'),
        ]);
    }

    /**
     * Descarga: Informe Semanal
     */
    public function informeSemanalDownload(Request $request)
    {
        $request->validate([
            'fecha' => 'required|date'
        ]);

        try {
            return $this->excelService->generateInformeSemanal($request->fecha);
        } catch (\Exception $e) {
            if (str_contains($e->getMessage(), 'base inicial')) {
                return redirect()->route('accounting.informe-semanal')
                    ->with('error', $e->getMessage())
                    ->withInput();
            }
            return redirect()->route('accounting.informe-semanal')
                ->with('error', 'Error al generar reporte: ' . $e->getMessage())
                ->withInput();
        }
    }

    /**
     * Vista: Informe Mensual
     */
    public function informeMensualView(Request $request)
    {
        $dataset = null;
        $mes = null;
        $anio = null;

        if ($request->has('month_year')) {
            $request->validate([
                'month_year' => 'required|date_format:Y-m'
            ]);

            $monthYear = explode('-', $request->month_year);
            $anio = (int)$monthYear[0];
            $mes = (int)$monthYear[1];

            try {
                $dataset = $this->reportService->buildInformeMensualDataset($mes, $anio);
            } catch (\Exception $e) {
                return redirect()->route('accounting.informe-mensual')
                    ->with('error', 'Error al generar preview: ' . $e->getMessage())
                    ->withInput();
            }
        } elseif ($request->has('mes') && $request->has('anio')) {
            $request->validate([
                'mes' => 'required|integer|min:1|max:12',
                'anio' => 'required|integer|min:2020|max:2100'
            ]);

            $mes = $request->mes;
            $anio = $request->anio;

            try {
                $dataset = $this->reportService->buildInformeMensualDataset($mes, $anio);
            } catch (\Exception $e) {
                return redirect()->route('accounting.informe-mensual')
                    ->with('error', 'Error al generar preview: ' . $e->getMessage())
                    ->withInput();
            }
        }

        return view('accounting.mensual', [
            'dataset' => $dataset,
            'mes' => $mes,
            'anio' => $anio,
        ]);
    }

    /**
     * Descarga: Informe Mensual
     */
    public function informeMensualDownload(Request $request)
    {
        $request->validate([
            'mes' => 'required|integer|min:1|max:12',
            'anio' => 'required|integer|min:2020|max:2100'
        ]);

        try {
            return $this->excelService->generateInformeMensual(
                $request->mes,
                $request->anio
            );
        } catch (\Exception $e) {
            if (str_contains($e->getMessage(), 'base inicial')) {
                return redirect()->route('accounting.informe-mensual')
                    ->with('error', $e->getMessage())
                    ->withInput();
            }
            return redirect()->route('accounting.informe-mensual')
                ->with('error', 'Error al generar reporte: ' . $e->getMessage())
                ->withInput();
        }
    }

    /**
     * Vista para configurar base inicial (solo Super Admin)
     */
    public function baseInicialView()
    {
        $initialBalance = InitialBalance::getActive();
        return view('accounting.base-inicial', ['initialBalance' => $initialBalance]);
    }

    /**
     * Guardar o actualizar base inicial (solo Super Admin)
     */
    public function baseInicialStore(Request $request)
    {
        $request->validate([
            'start_date' => 'required|date',
            'base_efectivo' => 'required|numeric|min:0',
            'base_banco' => 'required|numeric|min:0',
        ]);

        // Solo permitir un registro (singleton)
        $initialBalance = InitialBalance::getActive();
        
        if ($initialBalance) {
            // Actualizar existente
            $initialBalance->update([
                'start_date' => $request->start_date,
                'base_efectivo' => $request->base_efectivo,
                'base_banco' => $request->base_banco,
            ]);
        } else {
            // Crear nuevo
            InitialBalance::create([
                'start_date' => $request->start_date,
                'base_efectivo' => $request->base_efectivo,
                'base_banco' => $request->base_banco,
                'created_by' => auth()->id(),
            ]);
        }

        return redirect()->route('accounting.base-inicial')
            ->with('success', 'Base inicial configurada correctamente');
    }

    /**
     * Vista para registrar bases diarias faltantes
     */
    public function cashBases(Request $request)
    {
        // Obtener missing_dates de sesión, request query string, o request (puede venir de redirect con error)
        $missingDates = $request->session()->get('missing_dates', []);
        if (!is_array($missingDates)) {
            $missingDates = [];
        }
        
        // Si viene como query parameter (desde el enlace de arqueo diario)
        if ($request->has('missing_dates') && is_array($request->get('missing_dates'))) {
            $missingDates = array_unique(array_merge($missingDates, $request->get('missing_dates')));
        }

        if ($request->isMethod('post')) {
            try {
                // Validar datos
                $validated = $request->validate([
                    'bases' => 'required|array|min:1',
                    'bases.*.fecha' => 'required|date',
                    'bases.*.base_efectivo' => 'required|numeric|min:0',
                    'bases.*.base_banco' => 'required|numeric|min:0'
                ], [
                    'bases.required' => 'Debe proporcionar al menos una base diaria.',
                    'bases.array' => 'Los datos de bases deben ser un array.',
                    'bases.min' => 'Debe proporcionar al menos una base diaria.',
                    'bases.*.fecha.required' => 'La fecha es obligatoria.',
                    'bases.*.fecha.date' => 'La fecha debe ser válida.',
                    'bases.*.base_efectivo.required' => 'La base de efectivo es obligatoria.',
                    'bases.*.base_efectivo.numeric' => 'La base de efectivo debe ser un número.',
                    'bases.*.base_efectivo.min' => 'La base de efectivo debe ser mayor o igual a 0.',
                    'bases.*.base_banco.required' => 'La base de banco es obligatoria.',
                    'bases.*.base_banco.numeric' => 'La base de banco debe ser un número.',
                    'bases.*.base_banco.min' => 'La base de banco debe ser mayor o igual a 0.',
                ]);

                $saved = 0;
                $updated = 0;
                $created = 0;
                
                DB::beginTransaction();
                try {
                    foreach ($validated['bases'] as $base) {
                        // Convertir valores a float y asegurar formato correcto
                        $baseEfectivo = isset($base['base_efectivo']) && is_numeric($base['base_efectivo']) 
                            ? (float) $base['base_efectivo'] 
                            : 0.00;
                        $baseBanco = isset($base['base_banco']) && is_numeric($base['base_banco']) 
                            ? (float) $base['base_banco'] 
                            : 0.00;
                        
                        // Verificar que la fecha sea válida
                        $fecha = $base['fecha'];
                        if (empty($fecha) || !strtotime($fecha)) {
                            throw new \Exception("Fecha inválida: {$fecha}");
                        }
                        
                        $cashBase = CashBase::updateOrCreate(
                            ['fecha' => $fecha],
                            [
                                'base_efectivo' => $baseEfectivo,
                                'base_banco' => $baseBanco
                            ]
                        );
                        
                        // Verificar que realmente se guardó
                        if (!$cashBase->exists) {
                            throw new \Exception("No se pudo guardar la base diaria para la fecha: {$fecha}");
                        }
                        
                        if ($cashBase->wasRecentlyCreated) {
                            $created++;
                        } else {
                            $updated++;
                        }
                        
                        $saved++;
                    }
                    DB::commit();
                    
                    // Limpiar caché de modelos para asegurar que las consultas posteriores obtengan datos frescos
                    CashBase::getQuery()->getConnection()->resetQueryLog();
                    
                    // Verificar que realmente se guardaron verificando directamente en la BD
                    $fechasGuardadas = [];
                    foreach ($validated['bases'] as $base) {
                        $verificacion = CashBase::where('fecha', $base['fecha'])->first();
                        if ($verificacion) {
                            $fechasGuardadas[] = $base['fecha'];
                        }
                    }

                    $message = "Se guardaron {$saved} base(s) diaria(s) correctamente";
                    if ($created > 0 && $updated > 0) {
                        $message .= " ({$created} creada(s), {$updated} actualizada(s))";
                    } elseif ($created > 0) {
                        $message .= " ({$created} creada(s))";
                    } elseif ($updated > 0) {
                        $message .= " ({$updated} actualizada(s))";
                    }
                    $message .= ".";
                    
                    if (count($fechasGuardadas) !== count($validated['bases'])) {
                        \Log::warning('Algunas bases no se verificaron correctamente después de guardar', [
                            'fechas_guardadas' => $fechasGuardadas,
                            'fechas_intentadas' => array_column($validated['bases'], 'fecha')
                        ]);
                    }

                    return redirect()->route('accounting.cash-bases')
                        ->with('success', $message)
                        ->with('missing_dates', []); // Limpiar missing_dates de la sesión
                } catch (\Exception $e) {
                    DB::rollBack();
                    \Log::error('Error al guardar bases diarias: ' . $e->getMessage(), [
                        'trace' => $e->getTraceAsString(),
                        'bases' => $validated['bases'] ?? []
                    ]);
                    return redirect()->route('accounting.cash-bases')
                        ->with('error', 'Error al guardar las bases diarias: ' . $e->getMessage())
                        ->withInput()
                        ->with('missing_dates', $missingDates);
                }
            } catch (\Illuminate\Validation\ValidationException $e) {
                return redirect()->route('accounting.cash-bases')
                    ->withErrors($e->errors())
                    ->withInput()
                    ->with('missing_dates', $missingDates);
            } catch (\Exception $e) {
                \Log::error('Error en cashBases: ' . $e->getMessage());
                return redirect()->route('accounting.cash-bases')
                    ->with('error', 'Error al procesar la solicitud: ' . $e->getMessage())
                    ->withInput()
                    ->with('missing_dates', $missingDates);
            }
        }

        return view('accounting.cash-bases', ['missing_dates' => $missingDates]);
    }

    /**
     * Herramienta de investigación: Abonos problemáticos
     */
    public function investigarAbonos(Request $request)
    {
        $fechaInicio = $request->get('fecha_inicio', date('Y-m-01'));
        $fechaFin = $request->get('fecha_fin', date('Y-m-t'));

        // Obtener todos los abonos en el rango de fechas
        $entries = DB::table('entries')
            ->join('costs', 'costs.id', '=', 'entries.id_cost')
            ->join('conceptos', 'conceptos.id', '=', 'entries.concepto')
            ->whereBetween('entries.fecha_recibo', [$fechaInicio, $fechaFin])
            ->select(
                'entries.id',
                'entries.no_recibo',
                'entries.fecha_recibo',
                'entries.valor',
                'entries.descripcion',
                'costs.cod_alumno',
                'conceptos.nombre as concepto_nombre'
            )
            ->orderBy('entries.fecha_recibo')
            ->orderBy('entries.no_recibo')
            ->get();

        $problemas = [];
        $estadisticas = [
            'total' => count($entries),
            'sin_estudiante' => 0,
            'sin_programa' => 0,
            'validos' => 0
        ];

        foreach ($entries as $entry) {
            $problema = [
                'entry_id' => $entry->id,
                'no_recibo' => $entry->no_recibo,
                'fecha_recibo' => $entry->fecha_recibo,
                'valor' => $entry->valor,
                'descripcion' => $entry->descripcion,
                'concepto' => $entry->concepto_nombre,
                'cod_alumno' => $entry->cod_alumno,
                'tipo_problema' => null,
                'detalles' => []
            ];

            // Intentar obtener datos del estudiante
            $student = \App\Services\StudentResolverService::getStudentData($entry->cod_alumno);

            if (!$student) {
                // No se encontró el estudiante
                $problema['tipo_problema'] = 'SIN_ESTUDIANTE';
                $problema['detalles'][] = "No se encontró estudiante con cod_alumno: {$entry->cod_alumno}";
                
                // Verificar si existe en mysql2
                try {
                    $existeMysql2 = DB::connection('mysql2')->select(
                        'SELECT cod_alumno, nombre, cedula FROM alumno WHERE cod_alumno = ?',
                        [$entry->cod_alumno]
                    );
                    if (!empty($existeMysql2)) {
                        $problema['detalles'][] = "✓ Existe en mysql2 (alumno) pero no tiene programa asignado";
                        $problema['nombre_mysql2'] = $existeMysql2[0]->nombre ?? 'N/A';
                        $problema['cedula_mysql2'] = $existeMysql2[0]->cedula ?? 'N/A';
                    } else {
                        $problema['detalles'][] = "✗ No existe en mysql2 (alumno)";
                    }
                } catch (\Exception $e) {
                    $problema['detalles'][] = "Error al consultar mysql2: " . $e->getMessage();
                }

                // Verificar si existe en matriculas local
                $existeMatricula = \App\Models\Matricula::where('cod_alumno', $entry->cod_alumno)->first();
                if ($existeMatricula) {
                    $problema['detalles'][] = "✓ Existe en matriculas local pero no tiene programa";
                    $problema['nombre_matricula'] = $existeMatricula->nombre_completo ?? 'N/A';
                    $problema['cedula_matricula'] = $existeMatricula->numero_documento ?? 'N/A';
                    $problema['programa_matricula'] = $existeMatricula->programa ?? '(vacío)';
                } else {
                    $problema['detalles'][] = "✗ No existe en matriculas local";
                }

                $estadisticas['sin_estudiante']++;
                $problemas[] = $problema;
            } elseif (empty($student->nombre_programa) || trim($student->nombre_programa) === '') {
                // Estudiante existe pero no tiene programa
                $problema['tipo_problema'] = 'SIN_PROGRAMA';
                $problema['detalles'][] = "Estudiante encontrado pero sin programa asignado";
                $problema['nombre_estudiante'] = $student->nombre ?? 'N/A';
                $problema['cedula_estudiante'] = $student->cedula ?? 'N/A';
                
                // Verificar en mysql2 si tiene relación con programa
                try {
                    $tienePrograma = DB::connection('mysql2')->select(
                        'SELECT programa.nombre_programa 
                         FROM relacion_programa_estudiante 
                         INNER JOIN programa ON programa.cod_programa = relacion_programa_estudiante.programa_cod 
                         WHERE relacion_programa_estudiante.Alumno_cod = ?',
                        [$entry->cod_alumno]
                    );
                    if (empty($tienePrograma)) {
                        $problema['detalles'][] = "✗ No tiene relación con programa en mysql2";
                    } else {
                        $problema['detalles'][] = "⚠ Tiene programa en mysql2 pero no se recuperó correctamente";
                        $problema['programa_mysql2'] = $tienePrograma[0]->nombre_programa ?? 'N/A';
                    }
                } catch (\Exception $e) {
                    $problema['detalles'][] = "Error al consultar programa en mysql2: " . $e->getMessage();
                }

                // Verificar en matriculas local
                $matricula = \App\Models\Matricula::where('cod_alumno', $entry->cod_alumno)->first();
                if ($matricula && !empty($matricula->programa)) {
                    $problema['detalles'][] = "⚠ Tiene programa en matriculas local: {$matricula->programa}";
                    $problema['programa_matricula'] = $matricula->programa;
                } else {
                    $problema['detalles'][] = "✗ No tiene programa en matriculas local";
                }

                $estadisticas['sin_programa']++;
                $problemas[] = $problema;
            } else {
                $estadisticas['validos']++;
            }
        }

        return view('accounting.investigar-abonos', [
            'problemas' => $problemas,
            'estadisticas' => $estadisticas,
            'fecha_inicio' => $fechaInicio,
            'fecha_fin' => $fechaFin
        ]);
    }

    /**
     * Vista para eliminar abonos problemáticos (sin estudiante o sin programa)
     */
    public function eliminarAbonosProblematicos(Request $request)
    {
        $fechaInicio = $request->get('fecha_inicio', date('Y-m-01', strtotime('-1 year')));
        $fechaFin = $request->get('fecha_fin', date('Y-m-t'));

        // Obtener todos los abonos en el rango de fechas
        $entries = DB::table('entries')
            ->join('costs', 'costs.id', '=', 'entries.id_cost')
            ->join('conceptos', 'conceptos.id', '=', 'entries.concepto')
            ->whereBetween('entries.fecha_recibo', [$fechaInicio, $fechaFin])
            ->select(
                'entries.id',
                'entries.no_recibo',
                'entries.fecha_recibo',
                'entries.valor',
                'entries.descripcion',
                'costs.cod_alumno',
                'conceptos.nombre as concepto_nombre'
            )
            ->orderBy('entries.fecha_recibo')
            ->orderBy('entries.no_recibo')
            ->get();

        $abonosProblematicos = [];

        foreach ($entries as $entry) {
            $student = \App\Services\StudentResolverService::getStudentData($entry->cod_alumno);
            
            $esProblematico = false;
            $razon = '';

            if (!$student) {
                $esProblematico = true;
                $razon = 'Sin estudiante válido';
            } elseif (empty($student->nombre_programa) || trim($student->nombre_programa) === '') {
                $esProblematico = true;
                $razon = 'Estudiante sin programa';
            }

            if ($esProblematico) {
                $abonosProblematicos[] = [
                    'id' => $entry->id,
                    'no_recibo' => $entry->no_recibo,
                    'fecha_recibo' => $entry->fecha_recibo,
                    'valor' => $entry->valor,
                    'descripcion' => $entry->descripcion,
                    'concepto' => $entry->concepto_nombre,
                    'cod_alumno' => $entry->cod_alumno,
                    'razon' => $razon,
                    'student' => $student
                ];
            }
        }

        // Si se solicita eliminar
        if ($request->isMethod('post') && $request->has('eliminar_ids')) {
            $idsAEliminar = $request->eliminar_ids;
            
            if (!is_array($idsAEliminar)) {
                $idsAEliminar = [$idsAEliminar];
            }

            $eliminados = 0;
            $totalValor = 0;

            foreach ($idsAEliminar as $id) {
                $entry = \App\Models\Entry::find($id);
                if ($entry) {
                    $totalValor += $entry->valor;
                    \App\Http\Controllers\TableChangeController::StoreDelete('entries', $entry->id);
                    $entry->delete();
                    $eliminados++;
                }
            }

            return redirect()->route('accounting.eliminar-abonos-problematicos')
                ->with('success', "Se eliminaron {$eliminados} abono(s) problemático(s) por un valor total de $" . number_format($totalValor, 0, ',', '.'));
        }

        return view('accounting.eliminar-abonos-problematicos', [
            'abonosProblematicos' => $abonosProblematicos,
            'fecha_inicio' => $fechaInicio,
            'fecha_fin' => $fechaFin,
            'total' => count($abonosProblematicos),
            'totalValor' => array_sum(array_column($abonosProblematicos, 'valor'))
        ]);
    }
}
