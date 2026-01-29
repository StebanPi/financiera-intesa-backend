<?php

namespace App\Services;

use Illuminate\Support\Facades\DB;
use App\Models\OtherEntry;
use App\Models\ThirdReceipts;
use App\Models\EgresoReceipt;
use App\Models\CashBase;
use App\Models\InitialBalance;
use Carbon\Carbon;

class AccountingReportService
{
    const MAX_PREVIEW_ROWS = 500;

    /**
     * Construye dataset para preview de Abonos
     * Agrupa por PROGRAMA
     */
    public function buildAbonosDataset($startDate = null, $endDate = null, $sede = 'BARRANCABERMEJA')
    {
        $query = DB::table('entries')
            ->join('costs', 'costs.id', '=', 'entries.id_cost')
            ->join('matriculas', 'matriculas.cod_alumno', '=', 'costs.cod_alumno')
            ->join('conceptos', 'conceptos.id', '=', 'entries.concepto')
            ->where('matriculas.sede', $sede)
            ->select(
                'entries.*',
                'costs.cod_alumno',
                'conceptos.nombre as concepto_nombre'
            );

        if ($startDate && $endDate) {
            $query->whereBetween('entries.fecha_recibo', [$startDate, $endDate]);
        }

        $entries = $query->orderBy('entries.fecha_recibo', 'desc')
            ->orderBy('entries.no_recibo', 'desc')
            ->get();

        $grouped = [];
        foreach ($entries as $entry) {
            $student = StudentResolverService::getStudentData($entry->cod_alumno);
            
            if (!$student || empty($student->nombre_programa) || trim($student->nombre_programa) === '') {
                continue;
            }
            
            $programa = $student->nombre_programa;
            
            if (!isset($grouped[$programa])) {
                $grouped[$programa] = [];
            }
            
            $forma = StudentResolverService::normalizePaymentForm($entry->forma ?? 'Efectivo');
            
            $grouped[$programa][] = [
                'no_recibo' => $entry->no_recibo,
                'fecha_recibo' => $entry->fecha_recibo,
                'cedula' => $student->cedula ?? '',
                'nombre' => $student->nombre ?? 'N/A',
                'tipo' => $forma,
                'descripcion' => $entry->concepto_nombre ?? '',
                'valor' => $entry->valor,
            ];
        }

        // Ordenar items dentro de cada grupo por fecha reciente
        foreach ($grouped as $programa => &$items) {
            usort($items, function($a, $b) {
                $dateCompare = strtotime($b['fecha_recibo']) - strtotime($a['fecha_recibo']);
                if ($dateCompare !== 0) {
                    return $dateCompare;
                }
                return strcmp($b['no_recibo'], $a['no_recibo']);
            });
        }

        // Calcular total y contar filas
        $totalRows = 0;
        $total = 0;
        foreach ($grouped as $programa => $items) {
            $totalRows += count($items);
            foreach ($items as $item) {
                $total += $item['valor'];
            }
        }

        $isPartial = $totalRows > self::MAX_PREVIEW_ROWS;

        // Si es parcial, limitar datos
        if ($isPartial) {
            $remaining = self::MAX_PREVIEW_ROWS;
            $limitedGrouped = [];
            foreach ($grouped as $programa => $items) {
                if ($remaining <= 0) break;
                $limitedGrouped[$programa] = array_slice($items, 0, $remaining);
                $remaining -= count($limitedGrouped[$programa]);
            }
            $grouped = $limitedGrouped;
        }

        $result = [
            'grouped' => $grouped,
            'total' => $total,
            'is_partial' => $isPartial,
            'total_rows' => $totalRows,
        ];

        return $result;
    }

    /**
     * Construye dataset para preview de Otros Ingresos
     * Agrupa por CONCEPTO -> PROGRAMA
     */
    public function buildOtrosIngresosDataset($startDate = null, $endDate = null, $sede = 'BARRANCABERMEJA')
    {
        $query = DB::table('other_entries')
            ->join('costs', 'costs.id', '=', 'other_entries.id_cost')
            ->join('matriculas', 'matriculas.cod_alumno', '=', 'costs.cod_alumno')
            ->join('otros_conceptos', 'otros_conceptos.id', '=', 'other_entries.concepto')
            ->where('matriculas.sede', $sede)
            ->select(
                'other_entries.*',
                'costs.cod_alumno',
                'otros_conceptos.nombre as concepto_nombre'
            );

        if ($startDate && $endDate) {
            $query->whereBetween('other_entries.fecha_recibo', [$startDate, $endDate]);
        }

        $otherEntries = $query->orderBy('other_entries.fecha_recibo', 'desc')
            ->orderBy('other_entries.no_recibo', 'desc')
            ->get();

        $grouped = [];
        foreach ($otherEntries as $entry) {
            $student = StudentResolverService::getStudentData($entry->cod_alumno);
            $programa = $student ? $student->nombre_programa : 'SIN PROGRAMA';
            $concepto = $entry->concepto_nombre;
            
            if (!isset($grouped[$concepto])) {
                $grouped[$concepto] = [];
            }
            if (!isset($grouped[$concepto][$programa])) {
                $grouped[$concepto][$programa] = [];
            }
            
            $forma = StudentResolverService::normalizePaymentForm($entry->forma ?? 'Efectivo');
            
            $grouped[$concepto][$programa][] = [
                'no_recibo' => $entry->no_recibo,
                'fecha_recibo' => $entry->fecha_recibo,
                'cedula' => $student ? $student->cedula : '',
                'nombre' => $student ? $student->nombre : 'N/A',
                'concepto' => $concepto,
                'tipo' => $forma,
                'descripcion' => $entry->descripcion ?? '',
                'valor' => $entry->valor,
            ];
        }

        // Calcular total y contar filas
        $totalRows = 0;
        $total = 0;
        foreach ($grouped as $concepto => $programas) {
            foreach ($programas as $programa => $items) {
                $totalRows += count($items);
                foreach ($items as $item) {
                    $total += $item['valor'];
                }
            }
        }

        $isPartial = $totalRows > self::MAX_PREVIEW_ROWS;

        // Limitar si es parcial
        if ($isPartial) {
            $remaining = self::MAX_PREVIEW_ROWS;
            $limitedGrouped = [];
            foreach ($grouped as $concepto => $programas) {
                if ($remaining <= 0) break;
                $limitedGrouped[$concepto] = [];
                foreach ($programas as $programa => $items) {
                    if ($remaining <= 0) break;
                    $limitedGrouped[$concepto][$programa] = array_slice($items, 0, $remaining);
                    $remaining -= count($limitedGrouped[$concepto][$programa]);
                }
            }
            $grouped = $limitedGrouped;
        }

        $result = [
            'grouped' => $grouped,
            'total' => $total,
            'is_partial' => $isPartial,
            'total_rows' => $totalRows,
        ];

        return $result;
    }

    /**
     * Construye dataset para preview de Total Ingresos
     * Tabla plana con SUMA acumulada
     */
    public function buildTotalIngresosDataset($startDate = null, $endDate = null, $sede = 'BARRANCABERMEJA')
    {
        // Entries
        $entriesQuery = DB::table('entries')
            ->join('costs', 'costs.id', '=', 'entries.id_cost')
            ->join('matriculas', 'matriculas.cod_alumno', '=', 'costs.cod_alumno')
            ->join('conceptos', 'conceptos.id', '=', 'entries.concepto')
            ->where('matriculas.sede', $sede)
            ->select('entries.*', 'costs.cod_alumno', 'conceptos.nombre as concepto_nombre');

        if ($startDate && $endDate) {
            $entriesQuery->whereBetween('entries.fecha_recibo', [$startDate, $endDate]);
        }

        $entries = $entriesQuery->get();

        // Other entries
        $otherEntriesQuery = DB::table('other_entries')
            ->join('costs', 'costs.id', '=', 'other_entries.id_cost')
            ->join('matriculas', 'matriculas.cod_alumno', '=', 'costs.cod_alumno')
            ->join('otros_conceptos', 'otros_conceptos.id', '=', 'other_entries.concepto')
            ->where('matriculas.sede', $sede)
            ->select('other_entries.*', 'costs.cod_alumno', 'otros_conceptos.nombre as concepto_nombre');

        if ($startDate && $endDate) {
            $otherEntriesQuery->whereBetween('other_entries.fecha_recibo', [$startDate, $endDate]);
        }

        $otherEntries = $otherEntriesQuery->get();

        // Third receipts
        $thirdEntriesQuery = ThirdReceipts::where('type', 'entry')->where('sede', $sede);

        if ($startDate && $endDate) {
            $thirdEntriesQuery->whereBetween('fecha_recibo', [$startDate, $endDate]);
        }

        $thirdEntries = $thirdEntriesQuery->with('thirdObject')->get();

        $allEntries = [];
        
        foreach ($entries as $entry) {
            $student = StudentResolverService::getStudentData($entry->cod_alumno);
            $forma = StudentResolverService::normalizePaymentForm($entry->forma ?? 'Efectivo');
            $allEntries[] = [
                'fecha' => $entry->fecha_recibo,
                'nombre' => $student ? $student->nombre : 'N/A',
                'programa' => $student ? $student->nombre_programa : 'SIN PROGRAMA',
                'tipo_ingreso' => 'ABONO',
                'tipo' => $forma,
                'concepto' => $entry->concepto_nombre ?? 'ABONO',
                'descripcion' => $entry->descripcion ?? '',
                'no_recibo' => $entry->no_recibo,
                'valor' => $entry->valor
            ];
        }

        foreach ($otherEntries as $entry) {
            $student = StudentResolverService::getStudentData($entry->cod_alumno);
            $forma = StudentResolverService::normalizePaymentForm($entry->forma ?? 'Efectivo');
            $allEntries[] = [
                'fecha' => $entry->fecha_recibo,
                'nombre' => $student ? $student->nombre : 'N/A',
                'programa' => $student ? $student->nombre_programa : 'SIN PROGRAMA',
                'tipo_ingreso' => 'OTRO',
                'tipo' => $forma,
                'concepto' => $entry->concepto_nombre ?? 'OTRO',
                'descripcion' => $entry->descripcion ?? '',
                'no_recibo' => $entry->no_recibo,
                'valor' => $entry->valor
            ];
        }

        foreach ($thirdEntries as $entry) {
            $third = $entry->thirdObject;
            $forma = StudentResolverService::normalizePaymentForm($entry->forma ?? 'Efectivo');
            $allEntries[] = [
                'fecha' => $entry->fecha_recibo,
                'nombre' => $third ? $third->nombre : 'N/A',
                'programa' => 'TERCERO',
                'tipo_ingreso' => 'TERCERO',
                'tipo' => $forma,
                'concepto' => $entry->concepto ?? 'TERCERO',
                'descripcion' => $entry->detalles ?? '',
                'no_recibo' => $entry->no_recibo,
                'valor' => $entry->valor
            ];
        }

        // Ordenar por fecha (más reciente primero)
        usort($allEntries, function($a, $b) {
            return strtotime($b['fecha']) - strtotime($a['fecha']);
        });

        // Calcular SUMA acumulada
        $suma = 0;
        foreach ($allEntries as &$entry) {
            $suma += $entry['valor'];
            $entry['suma'] = $suma;
        }

        $totalRows = count($allEntries);
        $isPartial = $totalRows > self::MAX_PREVIEW_ROWS;

        if ($isPartial) {
            $allEntries = array_slice($allEntries, 0, self::MAX_PREVIEW_ROWS);
        }

        $total = $suma; // Última suma acumulada

        return [
            'entries' => $allEntries,
            'total' => $total,
            'is_partial' => $isPartial,
            'total_rows' => $totalRows,
        ];
    }

    /**
     * Construye dataset para preview de Total Egresos
     * Tabla plana con SUMA acumulada
     */
    public function buildTotalEgresosDataset($startDate = null, $endDate = null, $sede = 'BARRANCABERMEJA')
    {
        $query = EgresoReceipt::with('provider')->where('sede', $sede);

        if ($startDate && $endDate) {
            $query->whereBetween('fecha_recibo', [$startDate, $endDate]);
        }

        $egresos = $query->orderBy('fecha_recibo', 'desc')
            ->orderBy('no_recibo', 'desc')
            ->get();

        $items = [];
        foreach ($egresos as $egreso) {
            $provider = $egreso->provider;
            $forma = StudentResolverService::normalizePaymentForm($egreso->forma ?? 'Efectivo');
            
            $items[] = [
                'fecha' => $egreso->fecha_recibo,
                'proveedor' => $provider ? $provider->nombre : 'N/A',
                'tipo' => $forma,
                'concepto' => $egreso->concepto,
                'descripcion' => $egreso->descripcion ?? '',
                'no_recibo' => $egreso->no_recibo,
                'valor' => $egreso->valor,
            ];
        }

        // Calcular SUMA acumulada
        $suma = 0;
        foreach ($items as &$item) {
            $suma += $item['valor'];
            $item['suma'] = $suma;
        }

        $totalRows = count($items);
        $isPartial = $totalRows > self::MAX_PREVIEW_ROWS;

        if ($isPartial) {
            $items = array_slice($items, 0, self::MAX_PREVIEW_ROWS);
        }

        $total = $suma;

        return [
            'items' => $items,
            'total' => $total,
            'is_partial' => $isPartial,
            'total_rows' => $totalRows,
        ];
    }

    /**
     * Construye dataset para preview de Arqueo Diario
     */
    public function buildArqueoDiarioDataset($date, $sede = 'BARRANCABERMEJA')
    {
        return $this->buildArqueoDataset($date, $date, $sede);
    }

    /**
     * Construye dataset para preview de Arqueo Semanal (legacy - usa bases diarias)
     */
    public function buildArqueoSemanalDataset($anyDate, $sede = 'BARRANCABERMEJA')
    {
        $carbon = Carbon::parse($anyDate);
        $start = $carbon->copy()->startOfWeek()->format('Y-m-d'); // Lunes
        $end = $carbon->copy()->endOfWeek()->format('Y-m-d'); // Domingo
        
        return $this->buildArqueoDataset($start, $end, $sede);
    }

    /**
     * Construye dataset para preview de Arqueo Mensual (legacy - usa bases diarias)
     */
    public function buildArqueoMensualDataset($month, $year, $sede = 'BARRANCABERMEJA')
    {
        $start = Carbon::create($year, $month, 1)->startOfMonth()->format('Y-m-d');
        $end = Carbon::create($year, $month, 1)->endOfMonth()->format('Y-m-d');
        
        return $this->buildArqueoDataset($start, $end, $sede);
    }

    /**
     * Construye dataset para Informe Semanal (nueva lógica con base inicial)
     */
    public function buildInformeSemanalDataset($anyDate, $sede = 'BARRANCABERMEJA')
    {
        $carbon = Carbon::parse($anyDate);
        $start = $carbon->copy()->startOfWeek()->format('Y-m-d'); // Lunes
        $end = $carbon->copy()->endOfWeek()->format('Y-m-d'); // Domingo
        
        return $this->buildInformeMovimientosDataset($start, $end, $sede);
    }

    /**
     * Construye dataset para Informe Mensual (nueva lógica con base inicial)
     */
    public function buildInformeMensualDataset($month, $year, $sede = 'BARRANCABERMEJA')
    {
        $start = Carbon::create($year, $month, 1)->startOfMonth()->format('Y-m-d');
        $end = Carbon::create($year, $month, 1)->endOfMonth()->format('Y-m-d');
        
        return $this->buildInformeMovimientosDataset($start, $end, $sede);
    }

    /**
     * Construye dataset para informe de movimientos (sin bases diarias)
     * Usa base inicial única y calcula saldos acumulados
     */
    public function buildInformeMovimientosDataset($startDate, $endDate, $sede = 'BARRANCABERMEJA')
    {
        // Verificar si existe base inicial
        $initialBalance = InitialBalance::getActive($sede);
        if (!$initialBalance) {
            return [
                'missing_initial_base' => true,
                'startDate' => $startDate,
                'endDate' => $endDate,
                'opening' => ['saldo_efectivo' => 0, 'saldo_banco' => 0],
                'rows' => [],
                'summary' => [
                    'total_ing_efectivo' => 0,
                    'total_ing_banco' => 0,
                    'total_egr_efectivo' => 0,
                    'total_egr_banco' => 0,
                    'saldo_final_efectivo' => 0,
                    'saldo_final_banco' => 0,
                    'saldo_final_total' => 0,
                ],
                'is_partial' => false,
                'total_rows' => 0,
            ];
        }

        // Calcular saldo de apertura del reporte
        // saldo_apertura = base_inicial + (neto de movimientos entre start_date y el día anterior a startDate)
        $openingSaldoEfectivo = $initialBalance->base_efectivo;
        $openingSaldoBanco = $initialBalance->base_banco;

        // Obtener movimientos anteriores al rango del reporte
        $startDateCarbon = Carbon::parse($startDate);
        $initialDateCarbon = Carbon::parse($initialBalance->start_date->format('Y-m-d'));
        
        if ($startDateCarbon->gt($initialDateCarbon)) {
            $previousStartDate = $initialBalance->start_date->format('Y-m-d');
            $previousEndDate = $startDateCarbon->copy()->subDay()->format('Y-m-d');
            
            $previousMovements = $this->getMovementsForArqueo($previousStartDate, $previousEndDate, $sede);

            foreach ($previousMovements as $mov) {
                $forma = StudentResolverService::normalizePaymentForm($mov['forma'] ?? 'Efectivo');
                $isIngreso = $mov['tipo'] === 'ingreso';

                if ($isIngreso) {
                    if ($forma === 'Efectivo') {
                        $openingSaldoEfectivo += $mov['valor'];
                    } else {
                        $openingSaldoBanco += $mov['valor'];
                    }
                } else {
                    if ($forma === 'Efectivo') {
                        $openingSaldoEfectivo -= $mov['valor'];
                    } else {
                        $openingSaldoBanco -= $mov['valor'];
                    }
                }
            }
        } elseif ($startDateCarbon->lt($initialDateCarbon)) {
            // Si el reporte empieza antes de la fecha inicial, no hay saldo de apertura
            $openingSaldoEfectivo = 0;
            $openingSaldoBanco = 0;
        }

        // Obtener movimientos del rango del reporte
        $movements = $this->getMovementsForArqueo($startDate, $endDate, $sede);

        // Procesar movimientos y calcular saldos acumulados
        $rows = [];
        $currentSaldoEfectivo = $openingSaldoEfectivo;
        $currentSaldoBanco = $openingSaldoBanco;

        // Totales para resumen
        $totalIngEfectivo = 0;
        $totalIngBanco = 0;
        $totalEgrEfectivo = 0;
        $totalEgrBanco = 0;

        foreach ($movements as $mov) {
            $forma = StudentResolverService::normalizePaymentForm($mov['forma'] ?? 'Efectivo');
            $isIngreso = $mov['tipo'] === 'ingreso';

            $ingEfectivo = 0;
            $ingBanco = 0;
            $egrEfectivo = 0;
            $egrBanco = 0;

            if ($isIngreso) {
                if ($forma === 'Efectivo') {
                    $ingEfectivo = $mov['valor'];
                    $currentSaldoEfectivo += $mov['valor'];
                    $totalIngEfectivo += $mov['valor'];
                } else {
                    $ingBanco = $mov['valor'];
                    $currentSaldoBanco += $mov['valor'];
                    $totalIngBanco += $mov['valor'];
                }
            } else {
                if ($forma === 'Efectivo') {
                    $egrEfectivo = $mov['valor'];
                    $currentSaldoEfectivo -= $mov['valor'];
                    $totalEgrEfectivo += $mov['valor'];
                } else {
                    $egrBanco = $mov['valor'];
                    $currentSaldoBanco -= $mov['valor'];
                    $totalEgrBanco += $mov['valor'];
                }
            }

            $rows[] = [
                'fecha' => $mov['fecha'],
                'nombre' => $mov['nombre'],
                'ocupacion' => $mov['ocupacion'],
                'concepto' => $mov['concepto'],
                'descripcion' => $mov['descripcion'],
                'no_recibo' => $mov['no_recibo'],
                'ing_efectivo' => $ingEfectivo,
                'ing_banco' => $ingBanco,
                'egr_efectivo' => $egrEfectivo,
                'egr_banco' => $egrBanco,
                'saldo_efectivo' => $currentSaldoEfectivo,
                'saldo_banco' => $currentSaldoBanco,
            ];
        }

        $totalRows = count($rows);
        $isPartial = $totalRows > self::MAX_PREVIEW_ROWS;

        if ($isPartial) {
            $rows = array_slice($rows, 0, self::MAX_PREVIEW_ROWS);
        }

        return [
            'missing_initial_base' => false,
            'startDate' => $startDate,
            'endDate' => $endDate,
            'opening' => [
                'saldo_efectivo' => $openingSaldoEfectivo,
                'saldo_banco' => $openingSaldoBanco,
            ],
            'rows' => $rows,
            'summary' => [
                'total_ing_efectivo' => $totalIngEfectivo,
                'total_ing_banco' => $totalIngBanco,
                'total_egr_efectivo' => $totalEgrEfectivo,
                'total_egr_banco' => $totalEgrBanco,
                'saldo_final_efectivo' => $currentSaldoEfectivo,
                'saldo_final_banco' => $currentSaldoBanco,
                'saldo_final_total' => $currentSaldoEfectivo + $currentSaldoBanco,
            ],
            'is_partial' => $isPartial,
            'total_rows' => $totalRows,
        ];
    }

    /**
     * Construye dataset genérico para arqueo (diario/semanal/mensual)
     */
    protected function buildArqueoDataset($startDate, $endDate, $sede = 'BARRANCABERMEJA')
    {
        // Validar bases
        $missingDates = $this->getMissingCashBases($startDate, $endDate, $sede);
        if (!empty($missingDates)) {
            return [
                'missing_dates' => $missingDates,
                'dates' => [],
                'total_rows' => 0,
            ];
        }

        // Generar rango de fechas
        $dates = [];
        $current = Carbon::parse($startDate);
        $end = Carbon::parse($endDate);
        
        while ($current->lte($end)) {
            $dates[] = $current->format('Y-m-d');
            $current->addDay();
        }

        // Obtener movimientos
        $movements = $this->getMovementsForArqueo($startDate, $endDate, $sede);

        $result = [];
        $totalRows = 0;

        foreach ($dates as $date) {
            $cashBase = CashBase::where('fecha', $date)->where('sede', $sede)->first();
            $baseEfectivo = $cashBase ? $cashBase->base_efectivo : 0;
            $baseBanco = $cashBase ? $cashBase->base_banco : 0;

            $dayData = [
                'fecha' => $date,
                'base_efectivo' => $baseEfectivo,
                'base_banco' => $baseBanco,
                'movements' => [],
                'saldo_efectivo' => $baseEfectivo,
                'saldo_banco' => $baseBanco,
            ];

            // Filtrar movimientos del día
            $dayMovements = array_filter($movements, function($m) use ($date) {
                return $m['fecha'] === $date;
            });

            // Ordenar por no_recibo
            usort($dayMovements, function($a, $b) {
                return strcmp($a['no_recibo'], $b['no_recibo']);
            });

            // Procesar movimientos y calcular saldos
            foreach ($dayMovements as $mov) {
                $forma = StudentResolverService::normalizePaymentForm($mov['forma'] ?? 'Efectivo');
                $isIngreso = $mov['tipo'] === 'ingreso';

                if ($isIngreso) {
                    if ($forma === 'Efectivo') {
                        $dayData['saldo_efectivo'] += $mov['valor'];
                    } else {
                        $dayData['saldo_banco'] += $mov['valor'];
                    }
                } else {
                    if ($forma === 'Efectivo') {
                        $dayData['saldo_efectivo'] -= $mov['valor'];
                    } else {
                        $dayData['saldo_banco'] -= $mov['valor'];
                    }
                }

                $dayData['movements'][] = [
                    'nombre' => $mov['nombre'],
                    'ocupacion' => $mov['ocupacion'],
                    'concepto' => $mov['concepto'],
                    'descripcion' => $mov['descripcion'],
                    'no_recibo' => $mov['no_recibo'],
                    'ing_efectivo' => ($isIngreso && $forma === 'Efectivo') ? $mov['valor'] : 0,
                    'ing_banco' => ($isIngreso && $forma === 'Bancos') ? $mov['valor'] : 0,
                    'egr_efectivo' => (!$isIngreso && $forma === 'Efectivo') ? $mov['valor'] : 0,
                    'egr_banco' => (!$isIngreso && $forma === 'Bancos') ? $mov['valor'] : 0,
                    'saldo_efectivo' => $dayData['saldo_efectivo'],
                    'saldo_banco' => $dayData['saldo_banco'],
                ];

                $totalRows++;
            }

            $result[] = $dayData;
        }

        $isPartial = $totalRows > self::MAX_PREVIEW_ROWS;

        return [
            'missing_dates' => [],
            'dates' => $result,
            'is_partial' => $isPartial,
            'total_rows' => $totalRows,
        ];
    }

    /**
     * Obtiene movimientos para arqueo
     */
    protected function getMovementsForArqueo($startDate, $endDate, $sede = 'BARRANCABERMEJA')
    {
        $movements = [];

        // Ingresos: entries
        $entries = DB::table('entries')
            ->join('costs', 'costs.id', '=', 'entries.id_cost')
            ->join('matriculas', 'matriculas.cod_alumno', '=', 'costs.cod_alumno')
            ->where('matriculas.sede', $sede)
            ->whereBetween('entries.fecha_recibo', [$startDate, $endDate])
            ->select('entries.*', 'costs.cod_alumno')
            ->get();

        foreach ($entries as $entry) {
            $student = StudentResolverService::getStudentData($entry->cod_alumno);
            $movements[] = [
                'fecha' => $entry->fecha_recibo,
                'nombre' => $student ? $student->nombre : 'N/A',
                'ocupacion' => $student ? $student->nombre_programa : 'SIN PROGRAMA',
                'concepto' => 'ABONO',
                'descripcion' => $entry->descripcion,
                'no_recibo' => $entry->no_recibo,
                'valor' => $entry->valor,
                'forma' => $entry->forma ?? 'Efectivo',
                'tipo' => 'ingreso'
            ];
        }

        // Ingresos: other_entries
        $otherEntries = DB::table('other_entries')
            ->join('costs', 'costs.id', '=', 'other_entries.id_cost')
            ->join('matriculas', 'matriculas.cod_alumno', '=', 'costs.cod_alumno')
            ->where('matriculas.sede', $sede)
            ->whereBetween('other_entries.fecha_recibo', [$startDate, $endDate])
            ->select('other_entries.*', 'costs.cod_alumno')
            ->get();

        foreach ($otherEntries as $entry) {
            $student = StudentResolverService::getStudentData($entry->cod_alumno);
            $concepto = DB::table('otros_conceptos')->where('id', $entry->concepto)->value('nombre');
            $movements[] = [
                'fecha' => $entry->fecha_recibo,
                'nombre' => $student ? $student->nombre : 'N/A',
                'ocupacion' => $student ? $student->nombre_programa : 'SIN PROGRAMA',
                'concepto' => $concepto ?? 'OTRO',
                'descripcion' => $entry->descripcion,
                'no_recibo' => $entry->no_recibo,
                'valor' => $entry->valor,
                'forma' => $entry->forma ?? 'Efectivo',
                'tipo' => 'ingreso'
            ];
        }

        // Ingresos: third_receipts type='entry'
        $thirdEntries = ThirdReceipts::where('type', 'entry')->where('sede', $sede)
            ->whereBetween('fecha_recibo', [$startDate, $endDate])
            ->with('thirdObject')
            ->get();

        foreach ($thirdEntries as $entry) {
            $third = $entry->thirdObject;
            $movements[] = [
                'fecha' => $entry->fecha_recibo,
                'nombre' => $third ? $third->nombre : 'N/A',
                'ocupacion' => 'TERCERO',
                'concepto' => $entry->concepto ?? 'TERCERO',
                'descripcion' => $entry->detalles ?? '',
                'no_recibo' => $entry->no_recibo,
                'valor' => $entry->valor,
                'forma' => $entry->forma ?? 'Efectivo',
                'tipo' => 'ingreso'
            ];
        }

        // Egresos: egreso_receipts
        $egresos = EgresoReceipt::with('provider')
            ->where('sede', $sede)
            ->whereBetween('fecha_recibo', [$startDate, $endDate])
            ->get();

        foreach ($egresos as $egreso) {
            $provider = $egreso->provider;
            $movements[] = [
                'fecha' => $egreso->fecha_recibo,
                'nombre' => $provider ? $provider->nombre : 'N/A',
                'ocupacion' => 'PROVEEDOR',
                'concepto' => $egreso->concepto,
                'descripcion' => $egreso->descripcion ?? '',
                'no_recibo' => $egreso->no_recibo,
                'valor' => $egreso->valor,
                'forma' => $egreso->forma ?? 'Efectivo',
                'tipo' => 'egreso'
            ];
        }

        // Ordenar por fecha y no_recibo
        usort($movements, function($a, $b) {
            $dateCompare = strcmp($a['fecha'], $b['fecha']);
            if ($dateCompare !== 0) {
                return $dateCompare;
            }
            return strcmp($a['no_recibo'], $b['no_recibo']);
        });

        return $movements;
    }

    /**
     * Obtiene fechas faltantes de bases diarias
     */
    protected function getMissingCashBases($startDate, $endDate, $sede = 'BARRANCABERMEJA')
    {
        $dates = [];
        $current = Carbon::parse($startDate);
        $end = Carbon::parse($endDate);
        
        while ($current->lte($end)) {
            $dates[] = $current->format('Y-m-d');
            $current->addDay();
        }

        // Obtener fechas existentes y normalizar al formato Y-m-d para comparación
        $existingBases = CashBase::where('sede', $sede)
            ->whereIn('fecha', $dates)
            ->get();
            
        $existingDates = $existingBases->map(function($base) {
            return $base->fecha->format('Y-m-d');
        })->toArray();
        
        return array_diff($dates, $existingDates);
    }
}
