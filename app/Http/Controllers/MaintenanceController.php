<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use App\Models\Entry;
use App\Models\OtherEntry;
use App\Models\Cost;
use App\Models\Matricula;
use App\Models\Purse;
use App\Models\historyPurse;
use App\Models\ThirdReceipts;
use App\Models\EgresoReceipt;
use App\Models\EgresoProvider;
use App\Models\thirdEntry;
use App\Models\thirdActivity;
use App\Models\CashBase;
use App\Models\InitialBalance;
use App\Models\debe;
use App\Models\haber;
use App\Models\elaborado;
use App\Models\Program;
use App\Models\Schedule;
use App\Models\Group;
use App\Models\Teacher;
use App\Models\Module;
use App\Models\InstitutionSetting;
use Dompdf\Dompdf;

class MaintenanceController extends Controller
{
    /**
     * Mostrar la vista de herramientas de investigación y limpieza
     */
    public function index()
    {
        // Obtener estadísticas de datos para la sección de limpieza
        $stats = [
            'entries' => Entry::count(),
            'other_entries' => OtherEntry::count(),
            'costs' => Cost::count(),
            'matriculas' => Matricula::count(),
            'purses' => Purse::count(),
            'history_purses' => historyPurse::count(),
            'third_receipts' => ThirdReceipts::count(),
            'egreso_receipts' => EgresoReceipt::count(),
            'egreso_providers' => EgresoProvider::count(),
            'third_entries' => thirdEntry::count(),
            'third_activities' => thirdActivity::count(),
            'cash_bases' => CashBase::count(),
            'initial_balances' => InitialBalance::count(),
        ];

        return view('maintenance.index', compact('stats'));
    }

    /**
     * Investigar abonos problemáticos (sin estudiante o sin programa)
     */
    public function investigarAbonosProblematicos()
    {
        $problemas = [
            'entries_sin_estudiante' => [],
            'entries_sin_programa' => [],
            'other_entries_sin_estudiante' => [],
            'other_entries_sin_programa' => []
        ];

        // Investigar entries sin estudiante o sin programa
        $entries = Entry::all();
        foreach ($entries as $entry) {
            $cost = Cost::where('id', $entry->id_cost)->first();
            if ($cost) {
                $codAlumno = $cost->cod_alumno;
                
                // Buscar estudiante en mysql2
                $student = DB::connection('mysql2')->select(
                    "SELECT alumno.cedula, alumno.nombre, programa.nombre_programa 
                     FROM alumno 
                     LEFT JOIN relacion_programa_estudiante ON relacion_programa_estudiante.Alumno_cod = alumno.cod_alumno 
                     LEFT JOIN programa ON programa.cod_programa = relacion_programa_estudiante.programa_cod 
                     WHERE alumno.cod_alumno = ?",
                    [$codAlumno]
                );

                // Si no se encuentra en mysql2, buscar en matriculas
                if (empty($student)) {
                    $matricula = Matricula::where('cod_alumno', $codAlumno)->first();
                    if ($matricula) {
                        $student = [(object)[
                            'cedula' => $matricula->numero_documento,
                            'nombre' => $matricula->nombre_completo,
                            'nombre_programa' => $matricula->programa ?? null
                        ]];
                    }
                }

                if (empty($student)) {
                    // Sin estudiante
                    $problemas['entries_sin_estudiante'][] = [
                        'entry' => $entry,
                        'cost' => $cost,
                        'cod_alumno' => $codAlumno
                    ];
                } elseif (empty($student[0]->nombre_programa) || $student[0]->nombre_programa === '') {
                    // Sin programa
                    $problemas['entries_sin_programa'][] = [
                        'entry' => $entry,
                        'cost' => $cost,
                        'student' => $student[0],
                        'cod_alumno' => $codAlumno
                    ];
                }
            } else {
                // Cost no existe
                $problemas['entries_sin_estudiante'][] = [
                    'entry' => $entry,
                    'cost' => null,
                    'cod_alumno' => null
                ];
            }
        }

        // Investigar other_entries sin estudiante o sin programa
        $otherEntries = OtherEntry::all();
        foreach ($otherEntries as $otherEntry) {
            $cost = Cost::where('id', $otherEntry->id_cost)->first();
            if ($cost) {
                $codAlumno = $cost->cod_alumno;
                
                // Buscar estudiante en mysql2
                $student = DB::connection('mysql2')->select(
                    "SELECT alumno.cedula, alumno.nombre, programa.nombre_programa 
                     FROM alumno 
                     LEFT JOIN relacion_programa_estudiante ON relacion_programa_estudiante.Alumno_cod = alumno.cod_alumno 
                     LEFT JOIN programa ON programa.cod_programa = relacion_programa_estudiante.programa_cod 
                     WHERE alumno.cod_alumno = ?",
                    [$codAlumno]
                );

                // Si no se encuentra en mysql2, buscar en matriculas
                if (empty($student)) {
                    $matricula = Matricula::where('cod_alumno', $codAlumno)->first();
                    if ($matricula) {
                        $student = [(object)[
                            'cedula' => $matricula->numero_documento,
                            'nombre' => $matricula->nombre_completo,
                            'nombre_programa' => $matricula->programa ?? null
                        ]];
                    }
                }

                if (empty($student)) {
                    // Sin estudiante
                    $problemas['other_entries_sin_estudiante'][] = [
                        'other_entry' => $otherEntry,
                        'cost' => $cost,
                        'cod_alumno' => $codAlumno
                    ];
                } elseif (empty($student[0]->nombre_programa) || $student[0]->nombre_programa === '') {
                    // Sin programa
                    $problemas['other_entries_sin_programa'][] = [
                        'other_entry' => $otherEntry,
                        'cost' => $cost,
                        'student' => $student[0],
                        'cod_alumno' => $codAlumno
                    ];
                }
            } else {
                // Cost no existe
                $problemas['other_entries_sin_estudiante'][] = [
                    'other_entry' => $otherEntry,
                    'cost' => null,
                    'cod_alumno' => null
                ];
            }
        }

        return response()->json([
            'success' => true,
            'problemas' => $problemas,
            'total' => count($problemas['entries_sin_estudiante']) + 
                      count($problemas['entries_sin_programa']) + 
                      count($problemas['other_entries_sin_estudiante']) + 
                      count($problemas['other_entries_sin_programa'])
        ]);
    }

    /**
     * Eliminar abonos problemáticos
     */
    public function eliminarAbonosProblematicos(Request $request)
    {
        $eliminados = [
            'entries' => 0,
            'other_entries' => 0,
            'costs' => 0
        ];

        try {
            DB::beginTransaction();

            // Obtener todos los entries
            $entries = Entry::all();
            foreach ($entries as $entry) {
                $cost = Cost::where('id', $entry->id_cost)->first();
                $debeEliminar = false;

                if (!$cost) {
                    $debeEliminar = true;
                } else {
                    $codAlumno = $cost->cod_alumno;
                    
                    // Buscar estudiante
                    $student = DB::connection('mysql2')->select(
                        "SELECT alumno.cod_alumno FROM alumno WHERE alumno.cod_alumno = ?",
                        [$codAlumno]
                    );

                    if (empty($student)) {
                        $matricula = Matricula::where('cod_alumno', $codAlumno)->first();
                        if (!$matricula) {
                            $debeEliminar = true;
                        }
                    }
                }

                if ($debeEliminar) {
                    $entry->delete();
                    $eliminados['entries']++;
                }
            }

            // Obtener todos los other_entries
            $otherEntries = OtherEntry::all();
            foreach ($otherEntries as $otherEntry) {
                $cost = Cost::where('id', $otherEntry->id_cost)->first();
                $debeEliminar = false;

                if (!$cost) {
                    $debeEliminar = true;
                } else {
                    $codAlumno = $cost->cod_alumno;
                    
                    // Buscar estudiante
                    $student = DB::connection('mysql2')->select(
                        "SELECT alumno.cod_alumno FROM alumno WHERE alumno.cod_alumno = ?",
                        [$codAlumno]
                    );

                    if (empty($student)) {
                        $matricula = Matricula::where('cod_alumno', $codAlumno)->first();
                        if (!$matricula) {
                            $debeEliminar = true;
                        }
                    }
                }

                if ($debeEliminar) {
                    $otherEntry->delete();
                    $eliminados['other_entries']++;
                }
            }

            // Eliminar costs huérfanos (sin entries ni other_entries)
            $costs = Cost::all();
            foreach ($costs as $cost) {
                $tieneEntries = Entry::where('id_cost', $cost->id)->exists();
                $tieneOtherEntries = OtherEntry::where('id_cost', $cost->id)->exists();
                $tienePurses = Purse::where('id_cost', $cost->id)->exists();

                if (!$tieneEntries && !$tieneOtherEntries && !$tienePurses) {
                    $codAlumno = $cost->cod_alumno;
                    
                    // Verificar si existe el estudiante
                    $student = DB::connection('mysql2')->select(
                        "SELECT alumno.cod_alumno FROM alumno WHERE alumno.cod_alumno = ?",
                        [$codAlumno]
                    );

                    if (empty($student)) {
                        $matricula = Matricula::where('cod_alumno', $codAlumno)->first();
                        if (!$matricula) {
                            $cost->delete();
                            $eliminados['costs']++;
                        }
                    }
                }
            }

            DB::commit();

            return response()->json([
                'success' => true,
                'message' => 'Abonos problemáticos eliminados exitosamente.',
                'eliminados' => $eliminados
            ]);
        } catch (\Exception $e) {
            DB::rollBack();
            return response()->json([
                'success' => false,
                'message' => 'Error al eliminar abonos: ' . $e->getMessage()
            ], 500);
        }
    }

    /**
     * Investigar matrículas problemáticas
     */
    public function investigarMatriculasProblematicas()
    {
        $problemas = [];

        $matriculas = Matricula::all();
        foreach ($matriculas as $matricula) {
            $problemasItem = [];
            
            // Verificar si tiene cost asociado
            $cost = Cost::where('cod_alumno', $matricula->cod_alumno)->first();
            if (!$cost) {
                $problemasItem[] = 'Sin costo asociado';
            }

            // Verificar si tiene programa
            if (empty($matricula->programa)) {
                $problemasItem[] = 'Sin programa';
            }

            if (!empty($problemasItem)) {
                $problemas[] = [
                    'matricula' => $matricula,
                    'problemas' => $problemasItem
                ];
            }
        }

        return response()->json([
            'success' => true,
            'problemas' => $problemas,
            'total' => count($problemas)
        ]);
    }

    /**
     * Reparar matrículas problemáticas
     */
    public function repararMatriculasProblematicas(Request $request)
    {
        $reparadas = 0;

        try {
            DB::beginTransaction();

            $matriculas = Matricula::all();
            foreach ($matriculas as $matricula) {
                $necesitaReparacion = false;

                // Verificar si tiene cost asociado, si no, intentar crear uno básico
                $cost = Cost::where('cod_alumno', $matricula->cod_alumno)->first();
                if (!$cost) {
                    // No crear automáticamente, solo marcar como problemático
                    // El superadmin debe decidir qué hacer
                    continue;
                }

                // Intentar reparar programa si está vacío (buscar en mysql2)
                if (empty($matricula->programa)) {
                    $student = DB::connection('mysql2')->select(
                        "SELECT programa.nombre_programa 
                         FROM alumno 
                         INNER JOIN relacion_programa_estudiante ON relacion_programa_estudiante.Alumno_cod = alumno.cod_alumno 
                         INNER JOIN programa ON programa.cod_programa = relacion_programa_estudiante.programa_cod 
                         WHERE alumno.cod_alumno = ?",
                        [$matricula->cod_alumno]
                    );

                    if (!empty($student) && !empty($student[0]->nombre_programa)) {
                        $matricula->programa = $student[0]->nombre_programa;
                        $matricula->save();
                        $reparadas++;
                    }
                }
            }

            DB::commit();

            return response()->json([
                'success' => true,
                'message' => 'Matrículas reparadas exitosamente.',
                'reparadas' => $reparadas
            ]);
        } catch (\Exception $e) {
            DB::rollBack();
            return response()->json([
                'success' => false,
                'message' => 'Error al reparar matrículas: ' . $e->getMessage()
            ], 500);
        }
    }

    /**
     * Limpiar todos los datos de prueba
     */
    public function limpiarDatosPrueba(Request $request)
    {
        $eliminados = [
            'entries' => 0,
            'other_entries' => 0,
            'costs' => 0,
            'matriculas' => 0,
            'purses' => 0,
            'history_purses' => 0,
            'third_receipts' => 0,
            'egreso_receipts' => 0,
            'egreso_providers' => 0,
            'third_entries' => 0,
            'third_activities' => 0,
            'cash_bases' => 0,
            'initial_balances' => 0,
        ];

        try {
            // Deshabilitar verificaciones de claves foráneas temporalmente
            DB::statement('SET FOREIGN_KEY_CHECKS=0;');

            // Eliminar en orden para respetar las relaciones de claves foráneas
            
            // 1. Eliminar history_purses (depende de purses)
            $eliminados['history_purses'] = historyPurse::count();
            historyPurse::truncate();

            // 2. Eliminar purses (depende de costs)
            $eliminados['purses'] = Purse::count();
            Purse::truncate();

            // 3. Eliminar entries (depende de costs)
            $eliminados['entries'] = Entry::count();
            Entry::truncate();

            // 4. Eliminar other_entries (depende de costs)
            $eliminados['other_entries'] = OtherEntry::count();
            OtherEntry::truncate();

            // 5. Eliminar third_receipts (depende de third_entries)
            $eliminados['third_receipts'] = ThirdReceipts::count();
            ThirdReceipts::truncate();

            // 6. Eliminar egreso_receipts (depende de egreso_providers)
            $eliminados['egreso_receipts'] = EgresoReceipt::count();
            EgresoReceipt::truncate();

            // 7. Eliminar egreso_providers
            $eliminados['egreso_providers'] = EgresoProvider::count();
            EgresoProvider::truncate();

            // 8. Eliminar third_entries
            $eliminados['third_entries'] = thirdEntry::count();
            thirdEntry::truncate();

            // 9. Eliminar third_activities
            $eliminados['third_activities'] = thirdActivity::count();
            thirdActivity::truncate();

            // 10. Eliminar costs (depende de matriculas)
            $eliminados['costs'] = Cost::count();
            Cost::truncate();

            // 11. Eliminar matriculas
            $eliminados['matriculas'] = Matricula::count();
            Matricula::truncate();

            // 12. Eliminar cash_bases
            $eliminados['cash_bases'] = CashBase::count();
            CashBase::truncate();

            // 13. Eliminar initial_balances
            $eliminados['initial_balances'] = InitialBalance::count();
            InitialBalance::truncate();

            // Rehabilitar verificaciones de claves foráneas
            DB::statement('SET FOREIGN_KEY_CHECKS=1;');

            // Ejecutar seeders para restaurar la configuración del sistema
            $seederMessage = '';
            try {
                \Artisan::call('db:seed', ['--class' => 'DatabaseSeeder']);
                $seederMessage = ' y los seeders se han ejecutado correctamente para restaurar la configuración del sistema.';
            } catch (\Exception $seederException) {
                $seederMessage = ' pero hubo un error al ejecutar los seeders: ' . $seederException->getMessage();
            }

            return response()->json([
                'success' => true,
                'message' => 'Todos los datos de prueba han sido eliminados exitosamente' . $seederMessage,
                'eliminados' => $eliminados,
                'total' => array_sum($eliminados)
            ]);
        } catch (\Exception $e) {
            // Asegurarse de rehabilitar las claves foráneas en caso de error
            try {
                DB::statement('SET FOREIGN_KEY_CHECKS=1;');
            } catch (\Exception $e2) {
                // Ignorar errores al rehabilitar
            }
            
            return response()->json([
                'success' => false,
                'message' => 'Error al limpiar datos de prueba: ' . $e->getMessage()
            ], 500);
        }
    }

    /**
     * Obtener estadísticas actuales de datos
     */
    public function getStatsDatosPrueba()
    {
        $stats = [
            'entries' => Entry::count(),
            'other_entries' => OtherEntry::count(),
            'costs' => Cost::count(),
            'matriculas' => Matricula::count(),
            'purses' => Purse::count(),
            'history_purses' => historyPurse::count(),
            'third_receipts' => ThirdReceipts::count(),
            'egreso_receipts' => EgresoReceipt::count(),
            'egreso_providers' => EgresoProvider::count(),
            'third_entries' => thirdEntry::count(),
            'third_activities' => thirdActivity::count(),
            'cash_bases' => CashBase::count(),
            'initial_balances' => InitialBalance::count(),
        ];

        $stats['total'] = array_sum($stats);

        return response()->json([
            'success' => true,
            'stats' => $stats
        ]);
    }

    /**
     * Obtener vista previa de datos que se van a borrar
     */
    public function obtenerVistaPreviaDatos()
    {
        $datos = [
            [
                'tabla' => 'entries',
                'nombre' => 'Abonos',
                'cantidad' => Entry::count(),
                'descripcion' => 'Recibos de pago de estudiantes'
            ],
            [
                'tabla' => 'other_entries',
                'nombre' => 'Otros Abonos',
                'cantidad' => OtherEntry::count(),
                'descripcion' => 'Otros recibos de pago'
            ],
            [
                'tabla' => 'costs',
                'nombre' => 'Costos',
                'cantidad' => Cost::count(),
                'descripcion' => 'Costos asociados a estudiantes'
            ],
            [
                'tabla' => 'matriculas',
                'nombre' => 'Matrículas',
                'cantidad' => Matricula::count(),
                'descripcion' => 'Registros de matrícula de estudiantes'
            ],
            [
                'tabla' => 'purses',
                'nombre' => 'Cartera',
                'cantidad' => Purse::count(),
                'descripcion' => 'Registros de cartera'
            ],
            [
                'tabla' => 'history_purses',
                'nombre' => 'Historial de Cartera',
                'cantidad' => historyPurse::count(),
                'descripcion' => 'Historial de movimientos de cartera'
            ],
            [
                'tabla' => 'third_receipts',
                'nombre' => 'Recibos de Terceros',
                'cantidad' => ThirdReceipts::count(),
                'descripcion' => 'Recibos de ingreso de terceros'
            ],
            [
                'tabla' => 'egreso_receipts',
                'nombre' => 'Recibos de Egresos',
                'cantidad' => EgresoReceipt::count(),
                'descripcion' => 'Recibos de egreso'
            ],
            [
                'tabla' => 'egreso_providers',
                'nombre' => 'Proveedores de Egresos',
                'cantidad' => EgresoProvider::count(),
                'descripcion' => 'Proveedores registrados'
            ],
            [
                'tabla' => 'third_entries',
                'nombre' => 'Terceros',
                'cantidad' => thirdEntry::count(),
                'descripcion' => 'Registros de terceros'
            ],
            [
                'tabla' => 'third_activities',
                'nombre' => 'Actividades de Terceros',
                'cantidad' => thirdActivity::count(),
                'descripcion' => 'Actividades asociadas a terceros'
            ],
            [
                'tabla' => 'cash_bases',
                'nombre' => 'Bases de Caja',
                'cantidad' => CashBase::count(),
                'descripcion' => 'Bases de caja diarias'
            ],
            [
                'tabla' => 'initial_balances',
                'nombre' => 'Balances Iniciales',
                'cantidad' => InitialBalance::count(),
                'descripcion' => 'Balances iniciales configurados'
            ],
        ];

        return response()->json([
            'success' => true,
            'datos' => $datos,
            'total' => array_sum(array_column($datos, 'cantidad'))
        ]);
    }

    /**
     * Limpiar una tabla específica de datos de prueba
     */
    public function limpiarTablaEspecifica(Request $request)
    {
        $request->validate([
            'tabla' => 'required|string|in:entries,other_entries,costs,matriculas,purses,history_purses,third_receipts,egreso_receipts,egreso_providers,third_entries,third_activities,cash_bases,initial_balances'
        ]);

        try {
            DB::beginTransaction();

            $tabla = $request->tabla;
            $cantidad = 0;
            $nombreTabla = '';

            switch ($tabla) {
                case 'entries':
                    $cantidad = Entry::count();
                    Entry::truncate();
                    $nombreTabla = 'Abonos';
                    break;
                case 'other_entries':
                    $cantidad = OtherEntry::count();
                    OtherEntry::truncate();
                    $nombreTabla = 'Otros Abonos';
                    break;
                case 'costs':
                    // Verificar que no haya entries ni other_entries asociados
                    if (Entry::count() > 0 || OtherEntry::count() > 0 || Purse::count() > 0) {
                        return response()->json([
                            'success' => false,
                            'message' => 'No se puede eliminar Costos porque tiene registros asociados (Abonos, Otros Abonos o Cartera). Elimine primero esos registros.'
                        ], 400);
                    }
                    $cantidad = Cost::count();
                    Cost::truncate();
                    $nombreTabla = 'Costos';
                    break;
                case 'matriculas':
                    // Verificar que no haya costs asociados
                    if (Cost::count() > 0) {
                        return response()->json([
                            'success' => false,
                            'message' => 'No se puede eliminar Matrículas porque tiene Costos asociados. Elimine primero los Costos.'
                        ], 400);
                    }
                    $cantidad = Matricula::count();
                    Matricula::truncate();
                    $nombreTabla = 'Matrículas';
                    break;
                case 'purses':
                    // Verificar que no haya history_purses asociados
                    if (historyPurse::count() > 0) {
                        return response()->json([
                            'success' => false,
                            'message' => 'No se puede eliminar Cartera porque tiene Historial de Cartera asociado. Elimine primero el Historial.'
                        ], 400);
                    }
                    $cantidad = Purse::count();
                    Purse::truncate();
                    $nombreTabla = 'Cartera';
                    break;
                case 'history_purses':
                    $cantidad = historyPurse::count();
                    historyPurse::truncate();
                    $nombreTabla = 'Historial de Cartera';
                    break;
                case 'third_receipts':
                    $cantidad = ThirdReceipts::count();
                    ThirdReceipts::truncate();
                    $nombreTabla = 'Recibos de Terceros';
                    break;
                case 'egreso_receipts':
                    $cantidad = EgresoReceipt::count();
                    EgresoReceipt::truncate();
                    $nombreTabla = 'Recibos de Egresos';
                    break;
                case 'egreso_providers':
                    // Verificar que no haya egreso_receipts asociados
                    if (EgresoReceipt::count() > 0) {
                        return response()->json([
                            'success' => false,
                            'message' => 'No se puede eliminar Proveedores porque tiene Recibos de Egresos asociados. Elimine primero los Recibos.'
                        ], 400);
                    }
                    $cantidad = EgresoProvider::count();
                    EgresoProvider::truncate();
                    $nombreTabla = 'Proveedores de Egresos';
                    break;
                case 'third_entries':
                    // Verificar que no haya third_receipts asociados
                    if (ThirdReceipts::count() > 0) {
                        return response()->json([
                            'success' => false,
                            'message' => 'No se puede eliminar Terceros porque tiene Recibos de Terceros asociados. Elimine primero los Recibos.'
                        ], 400);
                    }
                    $cantidad = thirdEntry::count();
                    thirdEntry::truncate();
                    $nombreTabla = 'Terceros';
                    break;
                case 'third_activities':
                    $cantidad = thirdActivity::count();
                    thirdActivity::truncate();
                    $nombreTabla = 'Actividades de Terceros';
                    break;
                case 'cash_bases':
                    $cantidad = CashBase::count();
                    CashBase::truncate();
                    $nombreTabla = 'Bases de Caja';
                    break;
                case 'initial_balances':
                    $cantidad = InitialBalance::count();
                    InitialBalance::truncate();
                    $nombreTabla = 'Balances Iniciales';
                    break;
            }

            DB::commit();

            return response()->json([
                'success' => true,
                'message' => "Tabla '{$nombreTabla}' limpiada exitosamente.",
                'tabla' => $tabla,
                'nombre' => $nombreTabla,
                'cantidad' => $cantidad
            ]);
        } catch (\Exception $e) {
            DB::rollBack();
            return response()->json([
                'success' => false,
                'message' => 'Error al limpiar la tabla: ' . $e->getMessage()
            ], 500);
        }
    }

    /**
     * Revisar duplicados en debe, haber y elaborado
     */
    public function revisarDuplicados()
    {
        $duplicados = [
            'debe' => [],
            'haber' => [],
            'elaborado' => []
        ];

        // Revisar duplicados en debe (por cuenta y nombre)
        $debes = debe::all();
        $debeAgrupados = [];
        foreach ($debes as $item) {
            $key = $item->cuenta . '|' . $item->nombre;
            if (!isset($debeAgrupados[$key])) {
                $debeAgrupados[$key] = [];
            }
            $debeAgrupados[$key][] = $item;
        }
        foreach ($debeAgrupados as $key => $items) {
            if (count($items) > 1) {
                $duplicados['debe'][] = [
                    'cuenta' => $items[0]->cuenta,
                    'nombre' => $items[0]->nombre,
                    'ids' => array_map(function($item) { return $item->id; }, $items),
                    'cantidad' => count($items)
                ];
            }
        }

        // Revisar duplicados en haber (por cuenta y nombre)
        $habers = haber::all();
        $haberAgrupados = [];
        foreach ($habers as $item) {
            $key = $item->cuenta . '|' . $item->nombre;
            if (!isset($haberAgrupados[$key])) {
                $haberAgrupados[$key] = [];
            }
            $haberAgrupados[$key][] = $item;
        }
        foreach ($haberAgrupados as $key => $items) {
            if (count($items) > 1) {
                $duplicados['haber'][] = [
                    'cuenta' => $items[0]->cuenta,
                    'nombre' => $items[0]->nombre,
                    'ids' => array_map(function($item) { return $item->id; }, $items),
                    'cantidad' => count($items)
                ];
            }
        }

        // Revisar duplicados en elaborado (por nombre)
        $elaborados = elaborado::all();
        $elaboradoAgrupados = [];
        foreach ($elaborados as $item) {
            $key = strtolower(trim($item->nombre));
            if (!isset($elaboradoAgrupados[$key])) {
                $elaboradoAgrupados[$key] = [];
            }
            $elaboradoAgrupados[$key][] = $item;
        }
        foreach ($elaboradoAgrupados as $key => $items) {
            if (count($items) > 1) {
                $duplicados['elaborado'][] = [
                    'nombre' => $items[0]->nombre,
                    'ids' => array_map(function($item) { return $item->id; }, $items),
                    'cantidad' => count($items)
                ];
            }
        }

        $total = count($duplicados['debe']) + count($duplicados['haber']) + count($duplicados['elaborado']);

        return response()->json([
            'success' => true,
            'duplicados' => $duplicados,
            'total' => $total,
            'resumen' => [
                'debe' => count($duplicados['debe']),
                'haber' => count($duplicados['haber']),
                'elaborado' => count($duplicados['elaborado'])
            ]
        ]);
    }

    /**
     * Limpiar duplicados en debe, haber y elaborado
     */
    public function limpiarDuplicados(Request $request)
    {
        try {
            DB::beginTransaction();

            $eliminados = [
                'debe' => 0,
                'haber' => 0,
                'elaborado' => 0
            ];

            // Limpiar duplicados en debe
            $debes = debe::all();
            $debeAgrupados = [];
            foreach ($debes as $item) {
                $key = $item->cuenta . '|' . $item->nombre;
                if (!isset($debeAgrupados[$key])) {
                    $debeAgrupados[$key] = [];
                }
                $debeAgrupados[$key][] = $item;
            }
            foreach ($debeAgrupados as $key => $items) {
                if (count($items) > 1) {
                    // Mantener el primero (el de menor ID), eliminar los demás
                    $primero = array_shift($items);
                    foreach ($items as $item) {
                        $item->delete();
                        $eliminados['debe']++;
                    }
                }
            }

            // Limpiar duplicados en haber
            $habers = haber::all();
            $haberAgrupados = [];
            foreach ($habers as $item) {
                $key = $item->cuenta . '|' . $item->nombre;
                if (!isset($haberAgrupados[$key])) {
                    $haberAgrupados[$key] = [];
                }
                $haberAgrupados[$key][] = $item;
            }
            foreach ($haberAgrupados as $key => $items) {
                if (count($items) > 1) {
                    // Mantener el primero (el de menor ID), eliminar los demás
                    $primero = array_shift($items);
                    foreach ($items as $item) {
                        $item->delete();
                        $eliminados['haber']++;
                    }
                }
            }

            // Limpiar duplicados en elaborado
            $elaborados = elaborado::all();
            $elaboradoAgrupados = [];
            foreach ($elaborados as $item) {
                $key = strtolower(trim($item->nombre));
                if (!isset($elaboradoAgrupados[$key])) {
                    $elaboradoAgrupados[$key] = [];
                }
                $elaboradoAgrupados[$key][] = $item;
            }
            foreach ($elaboradoAgrupados as $key => $items) {
                if (count($items) > 1) {
                    // Mantener el primero (el de menor ID), eliminar los demás
                    $primero = array_shift($items);
                    foreach ($items as $item) {
                        $item->delete();
                        $eliminados['elaborado']++;
                    }
                }
            }

            DB::commit();

            $total = $eliminados['debe'] + $eliminados['haber'] + $eliminados['elaborado'];

            return response()->json([
                'success' => true,
                'message' => 'Duplicados eliminados exitosamente.',
                'eliminados' => $eliminados,
                'total' => $total
            ]);
        } catch (\Exception $e) {
            DB::rollBack();
            return response()->json([
                'success' => false,
                'message' => 'Error al limpiar duplicados: ' . $e->getMessage()
            ], 500);
        }
    }

    /**
     * Verificar eliminaciones completas en las tablas de configuración
     */
    public function verificarEliminaciones()
    {
        $resultados = [];
        
        // Tablas a verificar
        $tablas = [
            'conceptos' => [
                'modelo' => \App\Models\concepto::class,
                'nombre' => 'Conceptos',
                'campo_id' => 'id'
            ],
            'elaborados' => [
                'modelo' => \App\Models\elaborado::class,
                'nombre' => 'Elaborados',
                'campo_id' => 'id'
            ],
            'debes' => [
                'modelo' => \App\Models\debe::class,
                'nombre' => 'Cuentas DEBE',
                'campo_id' => 'id'
            ],
            'habers' => [
                'modelo' => \App\Models\haber::class,
                'nombre' => 'Cuentas HABER',
                'campo_id' => 'id'
            ],
            'otros_conceptos' => [
                'modelo' => \App\Models\otrosConcepto::class,
                'nombre' => 'Otros Conceptos',
                'campo_id' => 'id'
            ],
            'programs' => [
                'modelo' => \App\Models\Program::class,
                'nombre' => 'Programas',
                'campo_id' => 'id'
            ],
            'schedules' => [
                'modelo' => \App\Models\Schedule::class,
                'nombre' => 'Horarios',
                'campo_id' => 'id'
            ],
            'groups' => [
                'modelo' => \App\Models\Group::class,
                'nombre' => 'Grupos',
                'campo_id' => 'id'
            ],
            'teachers' => [
                'modelo' => \App\Models\Teacher::class,
                'nombre' => 'Docentes',
                'campo_id' => 'id'
            ],
            'modules' => [
                'modelo' => \App\Models\Module::class,
                'nombre' => 'Módulos',
                'campo_id' => 'id'
            ],
        ];

        foreach ($tablas as $tabla => $config) {
            try {
                $modelo = $config['modelo'];
                $instanciaModelo = new $modelo();
                $nombreTabla = $instanciaModelo->getTable();
                
                $totalRegistros = DB::table($nombreTabla)->count();
                $registrosActivos = $modelo::count();
                
                // Verificar si hay registros que no se pueden encontrar con Eloquent (posiblemente eliminados pero aún en BD)
                $diferencia = $totalRegistros - $registrosActivos;
                
                // Verificar si hay registros huérfanos (IDs que existen en la tabla pero no se pueden acceder)
                $idsEnTabla = DB::table($nombreTabla)->pluck($config['campo_id'])->toArray();
                $idsEnModelo = $modelo::pluck($config['campo_id'])->toArray();
                $idsHuérfanos = array_diff($idsEnTabla, $idsEnModelo);
                
                $resultados[$tabla] = [
                    'nombre' => $config['nombre'],
                    'total_en_tabla' => $totalRegistros,
                    'total_en_modelo' => $registrosActivos,
                    'diferencia' => $diferencia,
                    'ids_huerfanos' => array_values($idsHuérfanos),
                    'tiene_problemas' => $diferencia > 0 || count($idsHuérfanos) > 0,
                    'estado' => ($diferencia == 0 && count($idsHuérfanos) == 0) ? 'ok' : 'problema'
                ];
            } catch (\Exception $e) {
                $resultados[$tabla] = [
                    'nombre' => $config['nombre'],
                    'error' => $e->getMessage(),
                    'estado' => 'error'
                ];
            }
        }

        $totalProblemas = 0;
        foreach ($resultados as $resultado) {
            if (isset($resultado['tiene_problemas']) && $resultado['tiene_problemas']) {
                $totalProblemas++;
            }
        }

        return response()->json([
            'success' => true,
            'resultados' => $resultados,
            'total_problemas' => $totalProblemas,
            'resumen' => [
                'total_tablas' => count($tablas),
                'tablas_ok' => count($tablas) - $totalProblemas,
                'tablas_con_problemas' => $totalProblemas
            ]
        ]);
    }

    /**
     * Forzar eliminación física de registros huérfanos
     */
    public function forzarEliminacionFisica(Request $request)
    {
        $request->validate([
            'tabla' => 'required|string',
            'ids' => 'required|array'
        ]);

        try {
            DB::beginTransaction();

            $tabla = $request->tabla;
            $ids = $request->ids;
            $eliminados = 0;

            // Obtener el nombre real de la tabla del modelo
            $modelos = [
                'conceptos' => \App\Models\concepto::class,
                'elaborados' => \App\Models\elaborado::class,
                'debes' => \App\Models\debe::class,
                'habers' => \App\Models\haber::class,
                'otros_conceptos' => \App\Models\otrosConcepto::class,
                'programs' => \App\Models\Program::class,
                'schedules' => \App\Models\Schedule::class,
                'groups' => \App\Models\Group::class,
                'teachers' => \App\Models\Teacher::class,
                'modules' => \App\Models\Module::class,
            ];
            
            if (!isset($modelos[$tabla])) {
                return response()->json([
                    'success' => false,
                    'message' => 'Tabla no válida: ' . $tabla
                ], 400);
            }
            
            $modelo = $modelos[$tabla];
            $instanciaModelo = new $modelo();
            $nombreTabla = $instanciaModelo->getTable();
            
            // Eliminar físicamente de la base de datos
            $eliminados = DB::table($nombreTabla)->whereIn('id', $ids)->delete();

            DB::commit();

            return response()->json([
                'success' => true,
                'message' => "Se eliminaron {$eliminados} registros huérfanos de la tabla {$tabla}.",
                'eliminados' => $eliminados
            ]);
        } catch (\Exception $e) {
            DB::rollBack();
            return response()->json([
                'success' => false,
                'message' => 'Error al eliminar registros: ' . $e->getMessage()
            ], 500);
        }
    }

    /**
     * Eliminar todos los costos y sus relaciones asociadas
     */
    public function eliminarCostos(Request $request)
    {
        try {
            DB::beginTransaction();

            $eliminados = [
                'history_purses' => 0,
                'purses' => 0,
                'entries' => 0,
                'other_entries' => 0,
                'costs' => 0
            ];

            // 1. Eliminar history_purses (depende de purses)
            $eliminados['history_purses'] = historyPurse::count();
            historyPurse::truncate();

            // 2. Eliminar purses (depende de costs)
            $eliminados['purses'] = Purse::count();
            Purse::truncate();

            // 3. Eliminar entries (depende de costs)
            $eliminados['entries'] = Entry::count();
            Entry::truncate();

            // 4. Eliminar other_entries (depende de costs)
            $eliminados['other_entries'] = OtherEntry::count();
            OtherEntry::truncate();

            // 5. Eliminar costs
            $eliminados['costs'] = Cost::count();
            Cost::truncate();

            DB::commit();

            return response()->json([
                'success' => true,
                'message' => 'Todos los costos y sus relaciones han sido eliminados exitosamente.',
                'eliminados' => $eliminados,
                'total' => array_sum($eliminados)
            ]);
        } catch (\Exception $e) {
            DB::rollBack();
            return response()->json([
                'success' => false,
                'message' => 'Error al eliminar costos: ' . $e->getMessage()
            ], 500);
        }
    }

    /**
     * Obtener estadísticas de costos antes de eliminar
     */
    public function getStatsCostos()
    {
        $stats = [
            'costs' => Cost::count(),
            'purses' => Purse::count(),
            'history_purses' => historyPurse::count(),
            'entries' => Entry::count(),
            'other_entries' => OtherEntry::count(),
        ];

        $stats['total'] = array_sum($stats);

        return response()->json([
            'success' => true,
            'stats' => $stats
        ]);
    }

    /**
     * Generar planilla de asistencia de prueba con N estudiantes
     */
    public function generarPlanillaAsistenciaPrueba(Request $request)
    {
        try {
            $request->validate([
                'numero_estudiantes' => 'required|integer|min:1|max:500'
            ]);

            $numeroEstudiantes = $request->numero_estudiantes;

            // Obtener o crear datos de prueba necesarios
            $programa = Program::where('active', true)->first();
            if (!$programa) {
                // Crear programa de prueba si no existe
                $programa = Program::create([
                    'name' => 'Programa de Prueba',
                    'active' => true
                ]);
            }

            $horario = Schedule::where('active', true)->first();
            if (!$horario) {
                $horario = Schedule::create([
                    'name' => 'Horario de Prueba',
                    'active' => true
                ]);
            }

            $grupo = Group::where('active', true)->first();
            if (!$grupo) {
                $grupo = Group::create([
                    'name' => 'Grupo de Prueba',
                    'active' => true
                ]);
            }

            $docente = Teacher::where('active', true)->first();
            if (!$docente) {
                $docente = Teacher::create([
                    'name' => 'Docente de Prueba',
                    'active' => true
                ]);
            }

            $modulo = Module::where('active', true)->first();
            if (!$modulo) {
                $modulo = Module::create([
                    'name' => 'Módulo de Prueba',
                    'active' => true
                ]);
            }

            // Obtener o crear estudiantes de prueba
            $estudiantesExistentes = Matricula::where('programa', $programa->name)
                ->where('horario', $horario->name)
                ->where('numero_grupo', $grupo->name)
                ->count();

            $estudiantesACrear = $numeroEstudiantes - $estudiantesExistentes;

            if ($estudiantesACrear > 0) {
                $nombres = [
                    'Juan', 'María', 'Carlos', 'Ana', 'Luis', 'Laura', 'Pedro', 'Sofía', 'Diego', 'Valentina',
                    'Andrés', 'Camila', 'Sebastián', 'Isabella', 'Nicolás', 'Mariana', 'Fernando', 'Daniela', 'Alejandro', 'Gabriela',
                    'Javier', 'Paula', 'Ricardo', 'Andrea', 'Felipe', 'Natalia', 'David', 'Carolina', 'Santiago', 'Juliana',
                    'Miguel', 'Alejandra', 'Roberto', 'Catalina', 'Jorge', 'Diana', 'Manuel', 'Sara', 'Cristian', 'Tatiana',
                    'Óscar', 'Lorena', 'Eduardo', 'Monica', 'Rafael', 'Patricia', 'Gustavo', 'Claudia', 'Héctor', 'Adriana'
                ];
                $apellidos = [
                    'García', 'Rodríguez', 'López', 'Martínez', 'González', 'Pérez', 'Sánchez', 'Ramírez', 'Torres', 'Flores',
                    'Gómez', 'Díaz', 'Hernández', 'Morales', 'Castro', 'Ortiz', 'Jiménez', 'Moreno', 'Álvarez', 'Ruiz',
                    'Romero', 'Vargas', 'Mendoza', 'Guerrero', 'Ramos', 'Medina', 'Silva', 'Cruz', 'Reyes', 'Ortega',
                    'Delgado', 'Molina', 'Herrera', 'Vega', 'Campos', 'Peña', 'Rojas', 'Navarro', 'Aguilar', 'Méndez',
                    'Suárez', 'Cortés', 'Vásquez', 'Fernández', 'Castillo', 'Rivera', 'Espinoza', 'León', 'Paredes', 'Villalobos'
                ];
                
                $ultimoCodAlumno = Matricula::max('cod_alumno') ?? 1000;
                
                // Mezclar arrays para mayor aleatoriedad
                shuffle($nombres);
                shuffle($apellidos);
                
                // Usar un índice para asegurar nombres únicos
                $indiceNombre = 0;
                $indiceApellido1 = 0;
                $indiceApellido2 = 0;
                
                for ($i = 0; $i < $estudiantesACrear; $i++) {
                    // Rotar índices para evitar repeticiones
                    $nombre = $nombres[$indiceNombre % count($nombres)];
                    $apellido1 = $apellidos[$indiceApellido1 % count($apellidos)];
                    $apellido2 = $apellidos[$indiceApellido2 % count($apellidos)];
                    
                    // Asegurar que los apellidos sean diferentes
                    while ($apellido1 === $apellido2) {
                        $indiceApellido2++;
                        $apellido2 = $apellidos[$indiceApellido2 % count($apellidos)];
                    }
                    
                    $nombreCompleto = $nombre . ' ' . $apellido1 . ' ' . $apellido2;
                    $codAlumno = $ultimoCodAlumno + $i + 1;
                    
                    // Generar cédula única
                    do {
                        $cedula = str_pad(rand(10000000, 99999999), 8, '0', STR_PAD_LEFT);
                        $existe = Matricula::where('numero_documento', $cedula)->exists();
                    } while ($existe);
                    
                    Matricula::create([
                        'cod_alumno' => $codAlumno,
                        'nombre_completo' => $nombreCompleto,
                        'numero_documento' => $cedula,
                        'tipo_documento' => 'CC',
                        'programa' => $programa->name,
                        'horario' => $horario->name,
                        'numero_grupo' => $grupo->name,
                        'estado_estudiante' => 'Activo',
                        'anio' => date('Y')
                    ]);
                    
                    // Incrementar índices
                    $indiceNombre++;
                    $indiceApellido1++;
                    $indiceApellido2++;
                }
            }

            // Obtener estudiantes (los existentes + los nuevos)
            $estudiantes = Matricula::where('programa', $programa->name)
                ->where('horario', $horario->name)
                ->where('numero_grupo', $grupo->name)
                ->orderBy('nombre_completo', 'asc')
                ->limit($numeroEstudiantes)
                ->get();

            // Obtener configuración de la institución
            $institucion = InstitutionSetting::getSettings();

            // Preparar datos para la vista
            $fechaInicio = $request->fecha_inicio ?? date('Y-m-d');
            $fechaFinal = $request->fecha_final ?? date('Y-m-d');
            $fechaClase = $request->fecha_clase ?? date('Y-m-d');

            $data = [
                'institucion' => $institucion,
                'programa' => $programa->name,
                'horario' => $horario->name,
                'grupo' => $grupo->name,
                'docente' => $docente->name,
                'modulo' => $modulo->name,
                'fecha_inicio' => $fechaInicio,
                'fecha_final' => $fechaFinal,
                'fecha_clase' => $fechaClase,
                'estudiantes' => $estudiantes,
                'hideDefaultFooter' => true,
            ];

            // Generar el PDF
            $dompdf = new Dompdf();
            $html = view('academic-management.planillas.asistencia.pdf', $data)->render();
            
            $dompdf->loadHtml($html);
            $dompdf->setPaper('A4', 'portrait');
            $dompdf->render();

            // Agregar paginación
            $canvas = $dompdf->getCanvas();
            $pageWidth = $canvas->get_width();
            $fontSize = 7;
            $font = 'Helvetica';
            $x = $pageWidth - 110;
            $y = 18;
            
            $canvas->page_text(
                $x,
                $y,
                'Página {PAGE_NUM} de {PAGE_COUNT}',
                $font,
                $fontSize,
                [0, 0, 0]
            );

            // Nombre del archivo
            $nombreArchivo = 'planilla_asistencia_prueba_' . $numeroEstudiantes . '_estudiantes_' . date('Y-m-d') . '.pdf';

            // Descargar el PDF
            return $dompdf->stream($nombreArchivo, [
                'Attachment' => true
            ]);

        } catch (\Exception $e) {
            \Log::error('Error al generar planilla de asistencia de prueba: ' . $e->getMessage());
            \Log::error('Stack trace: ' . $e->getTraceAsString());
            return response()->json([
                'success' => false,
                'message' => 'Error al generar planilla de prueba: ' . $e->getMessage()
            ], 500);
        }
    }
}
