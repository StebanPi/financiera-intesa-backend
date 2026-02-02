<?php

namespace App\Http\Controllers\Api\V1;

use App\Http\Controllers\Controller;
use App\Models\Purse;
use App\Services\CarteraService;
use App\Support\ApiResponse;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Carbon\Carbon;

class ReportDebtorsController extends Controller
{
    /**
     * GET /api/v1/reports/debtors
     *
     * Params:
     * - dia: (int) Día del mes (1-31)
     * - mes: (int) Mes (1-12)
     * - anio: (int) Año (YYYY)
     * - sede: (string) Sede (default: BARRANCABERMEJA)
     */
    public function getDebtors(Request $request): JsonResponse
    {
        $dia = $request->query('dia');
        $mes = $request->query('mes');
        $anio = $request->query('anio');
        $sede = $request->get('sede', 'BARRANCABERMEJA');

        $query = DB::table('purses')
            ->join('costs', 'purses.id_cost', '=', 'costs.id')
            ->join('matriculas', 'costs.cod_alumno', '=', 'matriculas.cod_alumno')
            ->whereRaw('UPPER(matriculas.sede) = ?', [strtoupper($sede)])
            ->select(
                'purses.id as purse_id',
                'purses.fecha_pago',
                'purses.cuota',
                'purses.comentario',
                'matriculas.cod_alumno',
                'matriculas.nombre_completo',
                'matriculas.telefono_personal',
                'matriculas.programa',
                'costs.numero_semestre',
                'costs.id as id_cost'
            );

        if ($dia) {
            $query->whereDay('purses.fecha_pago', $dia);
        }

        if ($mes) {
            $query->whereMonth('purses.fecha_pago', $mes);
        }

        if ($anio) {
            $query->whereYear('purses.fecha_pago', $anio);
        }

        $results = $query->orderBy('matriculas.nombre_completo')->get();

        $debtors = [];
        
        // Agrupar por alumno para evitar múltiples llamadas al CarteraService si tiene varias cuotas en el mismo día/mes
        // Aunque generalmente cada cuota es independiente, el CarteraService calcula por id_cost.
        
        foreach ($results as $row) {
            // Usar el servicio para verificar el estado real de la cartera
            // El servicio redistribuye los abonos cronológicamente
            $carteraData = CarteraService::calcularCartera($row->id_cost);
            
            // Buscar la cuota específica en los resultados calculados
            $cuotaCalculada = null;
            foreach ($carteraData['cuotas'] as $c) {
                if ($c['id'] == $row->purse_id) {
                    $cuotaCalculada = $c;
                    break;
                }
            }

            // Si la cuota no está completa, es un deudor
            if ($cuotaCalculada && ($cuotaCalculada['estado_pago'] !== 'Completa')) {
                $debtors[] = [
                    'cod_alumno' => $row->cod_alumno,
                    'nombre' => $row->nombre_completo,
                    'telefono' => $row->telefono_personal,
                    'programa' => $row->programa,
                    'semestre' => $row->numero_semestre,
                    'fecha_pago' => $row->fecha_pago,
                    'valor_cuota' => (float) $row->cuota,
                    'valor_abonado' => (float) $cuotaCalculada['abonado'],
                    'saldo_pendiente' => (float) ($row->cuota - $cuotaCalculada['abonado']),
                    'estado_pago' => $cuotaCalculada['estado_pago'],
                    'estado_general' => $cuotaCalculada['estado'],
                    'es_vencida' => $cuotaCalculada['is_vencida'],
                    'comentario' => $row->comentario
                ];
            }
        }
        
        return ApiResponse::success($debtors, 'Reporte de deudores obtenido correctamente');
    }
    
    /**
     * GET /api/v1/reports/active-debtors
     *
     * Params:
     * - sede: (string) Sede (default: BARRANCABERMEJA)
     */
    public function getActiveDebtors(Request $request): JsonResponse
    {
        $sede = $request->get('sede', 'BARRANCABERMEJA');

        // Obtener todos los estudiantes activos de la sede
        $students = DB::table('matriculas')
            ->where('estado_estudiante', 'Activo')
            ->whereRaw('UPPER(sede) = ?', [strtoupper($sede)])
            ->select('cod_alumno', 'nombre_completo', 'telefono_personal', 'programa')
            ->orderBy('id', 'desc')
            ->get();

        $debtors = [];

        foreach ($students as $student) {
            // Calcular la cartera del estudiante
            $carteraData = CarteraService::calcularCartera(null, $student->cod_alumno);
            
            $saldoEnMora = (float) ($carteraData['totales']['saldo_en_mora'] ?? 0);

            $debtors[] = [
                'cod_alumno' => $student->cod_alumno,
                'nombre' => $student->nombre_completo,
                'telefono' => $student->telefono_personal,
                'programa' => $student->programa,
                'saldo_en_mora' => $saldoEnMora,
            ];
        }

        return ApiResponse::success($debtors, 'Reporte de deudores activos obtenido correctamente');
    }
}
