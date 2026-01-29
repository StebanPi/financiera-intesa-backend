<?php

namespace App\Services;

use PhpOffice\PhpSpreadsheet\Spreadsheet;
use PhpOffice\PhpSpreadsheet\Writer\Xlsx;
use PhpOffice\PhpSpreadsheet\IOFactory;
use PhpOffice\PhpSpreadsheet\Style\Fill;
use PhpOffice\PhpSpreadsheet\Style\Alignment;
use PhpOffice\PhpSpreadsheet\Style\Border;
use PhpOffice\PhpSpreadsheet\Cell\DataType;
use Illuminate\Support\Facades\DB;
use App\Models\Entry;
use App\Models\OtherEntry;
use App\Models\ThirdReceipts;
use App\Models\EgresoReceipt;
use App\Models\CashBase;
use App\Models\InitialBalance;
use Carbon\Carbon;

class AccountingExcelService
{
    protected $templatePath;

    public function __construct()
    {
        $this->templatePath = resource_path('templates');
    }

    /**
     * Genera informe de Abonos
     */
    public function generateAbonosReport($startDate, $endDate, $sede = 'BARRANCABERMEJA')
    {
        $templateFile = $this->templatePath . '/informe de abonos.xlsx';
        $spreadsheet = $this->loadTemplate($templateFile);
        $sheet = $spreadsheet->getActiveSheet();

        // Limpiar datos existentes de la plantilla (desde fila 3 hasta 1000)
        $this->clearTemplateData($sheet, 3, 1000);

        // Obtener datos de entries filtrados por fecha_recibo y sede
        $entries = DB::table('entries')
            ->join('costs', 'costs.id', '=', 'entries.id_cost')
            ->join('conceptos', 'conceptos.id', '=', 'entries.concepto')
            ->where(DB::raw('UPPER(entries.sede)'), strtoupper($sede))
            ->whereBetween('entries.fecha_recibo', [$startDate, $endDate])
            ->select(
                'entries.*',
                'costs.cod_alumno',
                'conceptos.nombre as concepto_nombre'
            )
            ->orderBy('entries.fecha_recibo')
            ->get();

        // Agrupar por programa
        // Excluir abonos que no tienen estudiante válido o no tienen programa
        $grouped = [];
        foreach ($entries as $entry) {
            $student = StudentResolverService::getStudentData($entry->cod_alumno);
            $programa = ($student && !empty($student->nombre_programa)) ? $student->nombre_programa : 'SIN PROGRAMA';
            
            if (!isset($grouped[$programa])) {
                $grouped[$programa] = [];
            }
            
            $grouped[$programa][] = [
                'entry' => $entry,
                'student' => $student
            ];
        }

        // Insertar datos según plantilla - empezar desde fila 3
        // Estructura según imagen: A=No Recibo, B=Fecha, C=Cedula, D=Nombre, E=Tipo, F=Descripción, G=Valor
        $row = 3;
        foreach ($grouped as $programa => $items) {
            // Fila de subtítulo programa
            $sheet->setCellValue("A{$row}", $programa);
            $sheet->mergeCells("A{$row}:G{$row}");
            $sheet->getStyle("A{$row}")->getFont()->setBold(true);
            $row++;

            $subtotal = 0;
            foreach ($items as $item) {
                $entry = $item['entry'];
                $student = $item['student'];
                $forma = StudentResolverService::normalizePaymentForm($entry->forma ?? 'Efectivo');
                
                // A=No Recibo, B=Fecha, C=Cedula, D=Nombre, E=Tipo, F=Descripción, G=Valor
                $sheet->setCellValue("A{$row}", $entry->no_recibo);
                $sheet->setCellValue("B{$row}", date('d/m/Y', strtotime($entry->fecha_recibo)));
                $sheet->setCellValue("C{$row}", $student?->cedula ?? '');
                $sheet->setCellValue("D{$row}", $student?->nombre ?? 'N/A');
                $sheet->setCellValue("E{$row}", $forma); // Tipo = forma de pago
                $sheet->setCellValue("F{$row}", $entry->concepto_nombre); // Descripción = concepto
                $sheet->setCellValue("G{$row}", $entry->valor); // Valor numérico para fórmulas
                
                $subtotal += $entry->valor;
                $row++;
            }

            // Subtotal por programa - "SUBTOT" en columna F (Descripción), valor en columna G (Valor)
            $sheet->setCellValue("F{$row}", "SUBTOT");
            $sheet->setCellValue("G{$row}", $subtotal);
            $sheet->getStyle("F{$row}:G{$row}")->getFont()->setBold(true);
            $row++;
            $row++; // Espacio
        }

        // Total general
        $total = array_sum(array_map(function($items) {
            return array_sum(array_map(function($item) {
                return $item['entry']->valor;
            }, $items));
        }, $grouped));
        
        $sheet->setCellValue("F{$row}", "TOTAL GENERAL");
        $sheet->setCellValue("G{$row}", $total);
        $sheet->getStyle("F{$row}:G{$row}")->getFont()->setBold(true);

        return $this->download($spreadsheet, "Informe de Abonos {$startDate} a {$endDate}.xlsx");
    }

    /**
     * Genera informe de Otros Ingresos
     */
    public function generateOtrosIngresosReport($startDate, $endDate, $sede = 'BARRANCABERMEJA')
    {
        $templateFile = $this->templatePath . '/informe de otros ingresos.xlsx';
        $spreadsheet = $this->loadTemplate($templateFile);
        $sheet = $spreadsheet->getActiveSheet();

        // Limpiar datos existentes de la plantilla (desde fila 3 hasta 1000)
        $this->clearTemplateData($sheet, 3, 1000);

        $otherEntries = DB::table('other_entries')
            ->join('costs', 'costs.id', '=', 'other_entries.id_cost')
            ->join('otros_conceptos', 'otros_conceptos.id', '=', 'other_entries.concepto')
            ->where(DB::raw('UPPER(other_entries.sede)'), strtoupper($sede))
            ->whereBetween('other_entries.fecha_recibo', [$startDate, $endDate])
            ->select(
                'other_entries.*',
                'costs.cod_alumno',
                'otros_conceptos.nombre as concepto_nombre'
            )
            ->orderBy('otros_conceptos.nombre')
            ->orderBy('other_entries.fecha_recibo')
            ->get();

        // Agrupar por CONCEPTO -> PROGRAMA
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
            
            $grouped[$concepto][$programa][] = [
                'entry' => $entry,
                'student' => $student
            ];
        }

        // Estructura según plantilla: No de recibo, Fecha recibo, Cedula, Nombre(s), Concepto, Tipo, Descripción, Valor
        // Columnas: A=No recibo, B=Fecha, C=Cedula, D=Nombre, E=Concepto, F=Tipo, G=Descripción, H=Valor
        $row = 3; // Empezar desde fila 3
        foreach ($grouped as $concepto => $programas) {
            // Título concepto (fila de encabezado del concepto)
            $sheet->setCellValue("E{$row}", $concepto);
            $sheet->mergeCells("E{$row}:H{$row}");
            $sheet->getStyle("E{$row}")->getFont()->setBold(true);
            $row++;

            $conceptoSubtotal = 0;
            foreach ($programas as $programa => $items) {
                // Subtotal por programa dentro del concepto
                $programaSubtotal = 0;
                foreach ($items as $item) {
                    $entry = $item['entry'];
                    $student = $item['student'];
                    $forma = StudentResolverService::normalizePaymentForm($entry->forma ?? 'Efectivo');
                    
                    // A=No de recibo, B=Fecha recibo, C=Cedula, D=Nombre(s), E=Concepto, F=Tipo, G=Descripción, H=Valor
                    $sheet->setCellValue("A{$row}", $entry->no_recibo);
                    $sheet->setCellValue("B{$row}", date('d/m/Y', strtotime($entry->fecha_recibo)));
                    $sheet->setCellValue("C{$row}", $student ? $student->cedula : '');
                    $sheet->setCellValue("D{$row}", $student ? $student->nombre : 'N/A');
                    $sheet->setCellValue("E{$row}", $concepto);
                    $sheet->setCellValue("F{$row}", $forma); // Tipo = forma de pago
                    $sheet->setCellValue("G{$row}", $entry->descripcion ?? '');
                    $sheet->setCellValue("H{$row}", $entry->valor); // Valor numérico para fórmulas
                    
                    $programaSubtotal += $entry->valor;
                    $conceptoSubtotal += $entry->valor;
                    $row++;
                }

                // Subtotal programa
                $sheet->setCellValue("D{$row}", "Subtotal {$programa}");
                $sheet->setCellValue("H{$row}", $programaSubtotal);
                $sheet->getStyle("D{$row}:H{$row}")->getFont()->setBold(true);
                $row++;
            }

            // Subtotal concepto
            $sheet->setCellValue("E{$row}", "SUBTOTAL {$concepto}");
            $sheet->setCellValue("H{$row}", $conceptoSubtotal);
            $sheet->getStyle("E{$row}:H{$row}")->getFont()->setBold(true);
            $row++;
            $row++; // Espacio
        }

        // Total general
        $total = array_sum(array_map(function($programas) {
            return array_sum(array_map(function($items) {
                return array_sum(array_map(function($item) {
                    return $item['entry']->valor;
                }, $items));
            }, $programas));
        }, $grouped));
        
        $sheet->setCellValue("E{$row}", "TOTAL GENERAL");
        $sheet->setCellValue("H{$row}", $total);
        $sheet->getStyle("E{$row}:H{$row}")->getFont()->setBold(true);

        return $this->download($spreadsheet, "Informe de Otros Ingresos {$startDate} a {$endDate}.xlsx");
    }

    /**
     * Genera informe Total Ingresos
     */
    public function generateTotalIngresosReport($startDate, $endDate, $sede = 'BARRANCABERMEJA')
    {
        $templateFile = $this->templatePath . '/informe total ingresos.xlsx';
        $spreadsheet = $this->loadTemplate($templateFile);
        $sheet = $spreadsheet->getActiveSheet();

        // Limpiar datos existentes de la plantilla (desde fila 2 hasta 1000)
        $this->clearTemplateData($sheet, 2, 1000);

        // Entries con concepto
        $entries = DB::table('entries')
            ->join('costs', 'costs.id', '=', 'entries.id_cost')
            ->join('conceptos', 'conceptos.id', '=', 'entries.concepto')
            ->where(DB::raw('UPPER(entries.sede)'), strtoupper($sede))
            ->whereBetween('entries.fecha_recibo', [$startDate, $endDate])
            ->select('entries.*', 'costs.cod_alumno', 'conceptos.nombre as concepto_nombre')
            ->get();

        // Other entries con concepto
        $otherEntries = DB::table('other_entries')
            ->join('costs', 'costs.id', '=', 'other_entries.id_cost')
            ->join('otros_conceptos', 'otros_conceptos.id', '=', 'other_entries.concepto')
            ->where(DB::raw('UPPER(other_entries.sede)'), strtoupper($sede))
            ->whereBetween('other_entries.fecha_recibo', [$startDate, $endDate])
            ->select('other_entries.*', 'costs.cod_alumno', 'otros_conceptos.nombre as concepto_nombre')
            ->get();

        // Third receipts type='entry'
        $thirdEntries = ThirdReceipts::where('type', 'entry')
            ->where('sede', $sede)
            ->whereBetween('fecha_recibo', [$startDate, $endDate])
            ->with('thirdObject')
            ->get();

        // Unificar y ordenar por fecha_recibo
        // Estructura según plantilla: FECHA, ESTUDIANTE/REGISTRO, PROGRAMA/TIPO, TIPO DE INGRESO, TIPO, CONCEPTO, DESCRIPCIÓN, N°RECIBO, VALOR, SUMA
        $allEntries = [];
        
        foreach ($entries as $entry) {
            $student = StudentResolverService::getStudentData($entry->cod_alumno);
            $forma = StudentResolverService::normalizePaymentForm($entry->forma ?? 'Efectivo');
            $allEntries[] = [
                'fecha' => $entry->fecha_recibo,
                'nombre' => $student ? $student->nombre : 'N/A',
                'programa' => $student ? $student->nombre_programa : 'SIN PROGRAMA',
                'tipo_ingreso' => 'ABONO',
                'tipo' => $forma, // Forma de pago: Efectivo/Bancos
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
                'tipo' => $forma, // Forma de pago: Efectivo/Bancos
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
                'tipo' => $forma, // Forma de pago: Efectivo/Bancos
                'concepto' => $entry->concepto ?? 'TERCERO',
                'descripcion' => $entry->detalles ?? '',
                'no_recibo' => $entry->no_recibo,
                'valor' => $entry->valor
            ];
        }

        // Ordenar por fecha
        usort($allEntries, function($a, $b) {
            return strtotime($a['fecha']) - strtotime($b['fecha']);
        });

        // Empezar desde fila 2 (después de encabezados en fila 1)
        // Estructura: A=FECHA, B=ESTUDIANTE/REGISTRO, C=PROGRAMA/TIPO, D=TIPO DE INGRESO, E=TIPO, F=CONCEPTO, G=DESCRIPCIÓN, H=N°RECIBO, I=VALOR, J=SUMA
        $row = 2;
        $firstDataRow = $row;
        foreach ($allEntries as $item) {
            $sheet->setCellValue("A{$row}", date('d/m/Y', strtotime($item['fecha'])));
            $sheet->setCellValue("B{$row}", $item['nombre']);
            $sheet->setCellValue("C{$row}", $item['programa']);
            $sheet->setCellValue("D{$row}", $item['tipo_ingreso']);
            $sheet->setCellValue("E{$row}", $item['tipo']); // Forma de pago
            $sheet->setCellValue("F{$row}", $item['concepto']);
            $sheet->setCellValue("G{$row}", $item['descripcion']);
            $sheet->setCellValue("H{$row}", $item['no_recibo']);
            $sheet->setCellValue("I{$row}", $item['valor']); // Valor numérico para fórmulas
            
            // Fórmula SUMA acumulada en columna J
            if ($row == $firstDataRow) {
                $sheet->setCellValue("J{$row}", $item['valor']);
            } else {
                $prevRow = $row - 1;
                $sheet->setCellValue("J{$row}", "=J{$prevRow}+I{$row}");
            }
            
            $row++;
        }

        // TOTAL GENERAL
        if ($row > $firstDataRow) {
            $lastDataRow = $row - 1;
            $sheet->setCellValue("A{$row}", "SUMA TOTAL");
            $sheet->setCellValue("J{$row}", "=SUM(I{$firstDataRow}:I{$lastDataRow})");
            $sheet->getStyle("A{$row}:J{$row}")->getFont()->setBold(true);
        }

        return $this->download($spreadsheet, "Informe Total Ingresos {$startDate} a {$endDate}.xlsx");
    }

    /**
     * Genera informe Total Egresos
     */
    public function generateTotalEgresosReport($startDate, $endDate, $sede = 'BARRANCABERMEJA')
    {
        $templateFile = $this->templatePath . '/informe total egresos.xlsx';
        $spreadsheet = $this->loadTemplate($templateFile);
        $sheet = $spreadsheet->getActiveSheet();

        // Limpiar datos existentes de la plantilla (desde fila 2 hasta 1000)
        $this->clearTemplateData($sheet, 2, 1000);

        $egresos = EgresoReceipt::with('provider')
            ->where('sede', $sede)
            ->whereBetween('fecha_recibo', [$startDate, $endDate])
            ->orderBy('fecha_recibo')
            ->orderBy('no_recibo')
            ->get();

        // Empezar en la fila 2 (justo después de los encabezados en fila 1)
        // Esto evita dejar filas vacías arriba
        $row = 2;
        $firstDataRow = $row;
        
        foreach ($egresos as $egreso) {
            $provider = $egreso->provider;
            $forma = StudentResolverService::normalizePaymentForm($egreso->forma ?? 'Efectivo');
            
            // Columnas según plantilla: A-FECHA, B-PROVEEDOR, C-TIPO, D-CONCEPTO, E-DESCRIPCIÓN, F-N°RECIBO, G-VALOR, H-SUMA
            $sheet->setCellValue("A{$row}", date('d/m/Y', strtotime($egreso->fecha_recibo)));
            $sheet->setCellValue("B{$row}", $provider ? $provider->nombre : 'N/A');
            $sheet->setCellValue("C{$row}", $forma); // TIPO (Efectivo/Bancos)
            $sheet->setCellValue("D{$row}", $egreso->concepto);
            $sheet->setCellValue("E{$row}", $egreso->descripcion ?? '');
            $sheet->setCellValue("F{$row}", $egreso->no_recibo);
            
            // Usar valor numérico para las fórmulas (columna G - VALOR)
            $sheet->setCellValue("G{$row}", $egreso->valor);
            
            // Fórmula SUMA acumulada (columna H - SUMA)
            // La primera fila es el valor mismo, las siguientes usan fórmula acumulada
            if ($row == $firstDataRow) {
                $sheet->setCellValue("H{$row}", $egreso->valor);
            } else {
                $prevRow = $row - 1;
                $sheet->setCellValue("H{$row}", "=H{$prevRow}+G{$row}");
            }
            
            $row++;
        }

        // TOTAL GENERAL - Usar fórmula en lugar de valor estático
        if ($row > $firstDataRow) {
            $lastDataRow = $row - 1;
            $sheet->setCellValue("A{$row}", "SUMA TOTAL DE EGRESOS");
            $sheet->setCellValue("H{$row}", "=SUM(G{$firstDataRow}:G{$lastDataRow})");
            $sheet->getStyle("A{$row}:H{$row}")->getFont()->setBold(true);
        }

        return $this->download($spreadsheet, "Informe Total Egresos {$startDate} a {$endDate}.xlsx");
    }

    /**
     * Genera Arqueo Diario
     */
    public function generateArqueoDiario($date, $sede = 'BARRANCABERMEJA')
    {
        return $this->generateArqueo($date, $date, 'ARQUEO DIARIO', $sede);
    }

    /**
     * Genera Arqueo Semanal (legacy - usa bases diarias)
     */
    public function generateArqueoSemanal($anyDate, $sede = 'BARRANCABERMEJA')
    {
        $carbon = Carbon::parse($anyDate);
        $start = $carbon->startOfWeek()->format('Y-m-d'); // Lunes
        $end = $carbon->endOfWeek()->format('Y-m-d'); // Domingo
        
        return $this->generateArqueo($start, $end, 'ARQUEO SEMANAL', $sede);
    }

    /**
     * Genera Arqueo Mensual (legacy - usa bases diarias)
     */
    public function generateArqueoMensual($month, $year, $sede = 'BARRANCABERMEJA')
    {
        $start = Carbon::create($year, $month, 1)->startOfMonth()->format('Y-m-d');
        $end = Carbon::create($year, $month, 1)->endOfMonth()->format('Y-m-d');
        
        return $this->generateArqueo($start, $end, 'ARQUEO MENSUAL', $sede);
    }

    /**
     * Genera Informe Semanal (nueva lógica con base inicial)
     */
    public function generateInformeSemanal($anyDate, $sede = 'BARRANCABERMEJA')
    {
        $carbon = Carbon::parse($anyDate);
        $start = $carbon->startOfWeek()->format('Y-m-d'); // Lunes
        $end = $carbon->endOfWeek()->format('Y-m-d'); // Domingo
        
        return $this->generateInformeMovimientos($start, $end, 'INFORME SEMANAL', $sede);
    }

    /**
     * Genera Informe Mensual (nueva lógica con base inicial)
     */
    public function generateInformeMensual($month, $year, $sede = 'BARRANCABERMEJA')
    {
        $start = Carbon::create($year, $month, 1)->startOfMonth()->format('Y-m-d');
        $end = Carbon::create($year, $month, 1)->endOfMonth()->format('Y-m-d');
        
        return $this->generateInformeMovimientos($start, $end, 'INFORME MENSUAL', $sede);
    }

    /**
     * Genera informe de movimientos (sin bases diarias)
     */
    protected function generateInformeMovimientos($startDate, $endDate, $title, $sede = 'BARRANCABERMEJA')
    {
        // Verificar base inicial
        $initialBalance = InitialBalance::getActive($sede);
        if (!$initialBalance) {
            throw new \Exception('Debe configurar la base inicial para generar el informe. Por favor, configure la base inicial desde el menú de contabilidad.');
        }

        $templateFile = $this->templatePath . '/arqueo_diario informe_semanal informe_mensual.xlsx';
        $spreadsheet = $this->loadTemplate($templateFile);
        $sheet = $spreadsheet->getActiveSheet();

        // Limpiar datos existentes de la plantilla (desde fila 4 hasta 1000)
        $this->clearTemplateData($sheet, 4, 1000);

        // Cambiar título
        $sheet->setCellValue('A1', $title);

        // Verificar base inicial
        $initialBalance = InitialBalance::getActive();
        if (!$initialBalance) {
            throw new \Exception('Debe configurar la base inicial para generar el informe. Por favor, configure la base inicial desde el menú de contabilidad.');
        }

        // Calcular saldo de apertura (igual que en buildInformeMovimientosDataset)
        $openingSaldoEfectivo = $initialBalance->base_efectivo;
        $openingSaldoBanco = $initialBalance->base_banco;

        $startDateCarbon = Carbon::parse($startDate);
        $initialDateCarbon = Carbon::parse($initialBalance->start_date);
        
        if ($startDateCarbon->gt($initialDateCarbon)) {
            $previousStartDate = Carbon::parse($initialBalance->start_date)->format('Y-m-d');
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
            $openingSaldoEfectivo = 0;
            $openingSaldoBanco = 0;
        }

        // Empezar desde fila 4
        $row = 4;

        // Fila SALDO APERTURA EFECTIVO
        $sheet->setCellValue("A{$row}", date('d/m/Y', strtotime($startDate)));
        $sheet->setCellValue("B{$row}", "SALDO APERTURA EFECTIVO");
        $sheet->setCellValue("G{$row}", $openingSaldoEfectivo); // Valor en columna G
        $sheet->setCellValue("K{$row}", $openingSaldoEfectivo); // Saldo inicial en columna K
        $sheet->getStyle("A{$row}:L{$row}")->getFont()->setBold(true);
        $sheet->getStyle("A{$row}:L{$row}")->getFill()->setFillType(Fill::FILL_SOLID)
            ->getStartColor()->setARGB('FFE2E3E5');
        $row++;

        // Fila SALDO APERTURA BANCO
        $sheet->setCellValue("A{$row}", date('d/m/Y', strtotime($startDate)));
        $sheet->setCellValue("B{$row}", "SALDO APERTURA BANCO");
        $sheet->setCellValue("H{$row}", $openingSaldoBanco); // Valor en columna H
        $sheet->setCellValue("L{$row}", $openingSaldoBanco); // Saldo inicial en columna L
        $sheet->getStyle("A{$row}:L{$row}")->getFont()->setBold(true);
        $sheet->getStyle("A{$row}:L{$row}")->getFill()->setFillType(Fill::FILL_SOLID)
            ->getStartColor()->setARGB('FFE2E3E5');
        $row++;

        // Filas de saldo de apertura (para fórmulas)
        // Fila de saldo apertura efectivo está en $row - 2 (fila 4)
        // Fila de saldo apertura banco está en $row - 1 (fila 5)
        $firstSaldoRowEfectivo = $row - 2; // Fila 4: saldo apertura efectivo
        $firstSaldoRowBanco = $row - 1;    // Fila 5: saldo apertura banco
        $firstMovRow = $row;

        // Obtener movimientos
        $movements = $this->getMovementsForArqueo($startDate, $endDate, $sede);

        // Calcular totales mientras procesamos
        $totalIngEfectivo = 0;
        $totalIngBanco = 0;
        $totalEgrEfectivo = 0;
        $totalEgrBanco = 0;
        $currentSaldoEfectivo = $openingSaldoEfectivo;
        $currentSaldoBanco = $openingSaldoBanco;

        // Procesar movimientos
        foreach ($movements as $mov) {
            $forma = StudentResolverService::normalizePaymentForm($mov['forma'] ?? 'Efectivo');
            $isIngreso = $mov['tipo'] === 'ingreso';
            
            $sheet->setCellValue("A{$row}", date('d/m/Y', strtotime($mov['fecha'])));
            $sheet->setCellValue("B{$row}", $mov['nombre']);
            $sheet->setCellValue("C{$row}", $mov['ocupacion']);
            $sheet->setCellValue("D{$row}", $mov['concepto']);
            $sheet->setCellValue("E{$row}", $mov['descripcion']);
            $sheet->setCellValue("F{$row}", $mov['no_recibo']);

            if ($isIngreso) {
                if ($forma === 'Efectivo') {
                    $sheet->setCellValue("G{$row}", $mov['valor']);
                    $totalIngEfectivo += $mov['valor'];
                    $currentSaldoEfectivo += $mov['valor'];
                } else {
                    $sheet->setCellValue("H{$row}", $mov['valor']);
                    $totalIngBanco += $mov['valor'];
                    $currentSaldoBanco += $mov['valor'];
                }
            } else {
                if ($forma === 'Efectivo') {
                    $sheet->setCellValue("I{$row}", $mov['valor']);
                    $totalEgrEfectivo += $mov['valor'];
                    $currentSaldoEfectivo -= $mov['valor'];
                } else {
                    $sheet->setCellValue("J{$row}", $mov['valor']);
                    $totalEgrBanco += $mov['valor'];
                    $currentSaldoBanco -= $mov['valor'];
                }
            }

            // Fórmulas de saldo acumulado fila por fila
            // K{row} = K{anterior} + G{row} - I{row}
            // L{row} = L{anterior} + H{row} - J{row}
            if ($row == $firstMovRow) {
                // Primera fila de movimiento: referenciar saldo de apertura
                $sheet->setCellValue("K{$row}", "=K{$firstSaldoRowEfectivo}+G{$row}-I{$row}");
                $sheet->setCellValue("L{$row}", "=L{$firstSaldoRowBanco}+H{$row}-J{$row}");
            } else {
                // Filas siguientes: referenciar fila anterior
                $prevRow = $row - 1;
                $sheet->setCellValue("K{$row}", "=K{$prevRow}+G{$row}-I{$row}");
                $sheet->setCellValue("L{$row}", "=L{$prevRow}+H{$row}-J{$row}");
            }
            
            $row++;
        }

        // RESUMEN FINAL
        // La última fila de movimiento es $row - 1 (si hay movimientos)
        // Si no hay movimientos, usar la fila del saldo de apertura de banco
        $lastMovRow = $row > $firstMovRow ? $row - 1 : $firstSaldoRowBanco;
        
        $row++;
        $sheet->setCellValue("A{$row}", "RESUMEN");
        $sheet->mergeCells("A{$row}:F{$row}");
        $sheet->getStyle("A{$row}:L{$row}")->getFont()->setBold(true);
        $sheet->getStyle("A{$row}:L{$row}")->getFill()->setFillType(Fill::FILL_SOLID)
            ->getStartColor()->setARGB('FFD1ECF1');
        $row++;

        // Totales calculados
        $sheet->setCellValue("B{$row}", "Total Ingresos Efectivo");
        $sheet->setCellValue("G{$row}", $totalIngEfectivo);
        $row++;

        $sheet->setCellValue("B{$row}", "Total Ingresos Banco");
        $sheet->setCellValue("H{$row}", $totalIngBanco);
        $row++;

        $sheet->setCellValue("B{$row}", "Total Egresos Efectivo");
        $sheet->setCellValue("I{$row}", $totalEgrEfectivo);
        $row++;

        $sheet->setCellValue("B{$row}", "Total Egresos Banco");
        $sheet->setCellValue("J{$row}", $totalEgrBanco);
        $row++;

        $sheet->setCellValue("B{$row}", "Saldo Final Efectivo");
        $sheet->setCellValue("K{$row}", "=K{$lastMovRow}");
        $sheet->getStyle("B{$row}:K{$row}")->getFont()->setBold(true);
        $row++;

        $sheet->setCellValue("B{$row}", "Saldo Final Banco");
        $sheet->setCellValue("L{$row}", "=L{$lastMovRow}");
        $sheet->getStyle("B{$row}:L{$row}")->getFont()->setBold(true);
        $row++;

        $sheet->setCellValue("B{$row}", "Saldo Final Total");
        // Referenciar: Saldo Final Efectivo está en K{row-2}, Saldo Final Banco está en L{row-1}
        $rowSaldoFinalEfectivo = $row - 2;
        $rowSaldoFinalBanco = $row - 1;
        $sheet->setCellValue("K{$row}", "=K{$rowSaldoFinalEfectivo}+L{$rowSaldoFinalBanco}");
        $sheet->mergeCells("K{$row}:L{$row}");
        $sheet->getStyle("B{$row}:L{$row}")->getFont()->setBold(true);
        $sheet->getStyle("B{$row}:L{$row}")->getFill()->setFillType(Fill::FILL_SOLID)
            ->getStartColor()->setARGB('FF0D6EFD');
        $sheet->getStyle("B{$row}:L{$row}")->getFont()->getColor()->setARGB('FFFFFFFF');

        return $this->download($spreadsheet, "{$title} {$startDate} a {$endDate}.xlsx");
    }

    /**
     * Genera arqueo (diario/semanal/mensual)
     */
    protected function generateArqueo($startDate, $endDate, $title, $sede = 'BARRANCABERMEJA')
    {
        // Validar que existan bases para todos los días del rango
        $missingDates = $this->getMissingCashBases($startDate, $endDate, $sede);
        if (!empty($missingDates)) {
            throw new \Exception('Faltan bases diarias para las siguientes fechas: ' . implode(', ', $missingDates));
        }

        $templateFile = $this->templatePath . '/arqueo_diario informe_semanal informe_mensual.xlsx';
        $spreadsheet = $this->loadTemplate($templateFile);
        $sheet = $spreadsheet->getActiveSheet();

        // Limpiar datos existentes de la plantilla (desde fila 4 hasta 1000)
        $this->clearTemplateData($sheet, 4, 1000);

        // Cambiar título según tipo
        $sheet->setCellValue('A1', $title);

        // Generar rango de fechas
        $dates = [];
        $current = Carbon::parse($startDate);
        $end = Carbon::parse($endDate);
        
        while ($current->lte($end)) {
            $dates[] = $current->format('Y-m-d');
            $current->addDay();
        }

        // Obtener todos los movimientos
        $movements = $this->getMovementsForArqueo($startDate, $endDate, $sede);

        // Empezar desde fila 4
        $row = 4;

        foreach ($dates as $date) {
            // Obtener base del día (cada día inicia con su base)
            $cashBase = CashBase::where('fecha', $date)->where('sede', $sede)->first();
            $baseEfectivo = $cashBase ? $cashBase->base_efectivo : 0;
            $baseBanco = $cashBase ? $cashBase->base_banco : 0;

            // Fila BASE DE EFECTIVO
            $sheet->setCellValue("A{$row}", date('d/m/Y', strtotime($date)));
            $sheet->setCellValue("B{$row}", "BASE DE EFECTIVO");
            $sheet->setCellValue("G{$row}", $baseEfectivo); // Valor numérico para fórmulas
            $sheet->setCellValue("K{$row}", $baseEfectivo); // Saldo inicial del día
            $row++;

            // Fila BASE DE BANCO
            $sheet->setCellValue("A{$row}", date('d/m/Y', strtotime($date)));
            $sheet->setCellValue("B{$row}", "BASE DE BANCO");
            $sheet->setCellValue("H{$row}", $baseBanco); // Valor numérico para fórmulas
            $sheet->setCellValue("L{$row}", $baseBanco); // Saldo inicial del día
            $row++;

            // Movimientos del día
            $dayMovements = array_filter($movements, function($m) use ($date) {
                return $m['fecha'] === $date;
            });

            // Obtener primera fila de saldo del día (después de las bases)
            $firstSaldoRow = $row - 1; // La última fila de bases es el saldo inicial
            $firstMovRow = $row; // Primera fila de movimientos del día
            
            foreach ($dayMovements as $mov) {
                $forma = StudentResolverService::normalizePaymentForm($mov['forma'] ?? 'Efectivo');
                $isIngreso = $mov['tipo'] === 'ingreso';
                
                $sheet->setCellValue("A{$row}", date('d/m/Y', strtotime($mov['fecha'])));
                $sheet->setCellValue("B{$row}", $mov['nombre']);
                $sheet->setCellValue("C{$row}", $mov['ocupacion']);
                $sheet->setCellValue("D{$row}", $mov['concepto']);
                $sheet->setCellValue("E{$row}", $mov['descripcion']);
                $sheet->setCellValue("F{$row}", $mov['no_recibo']);

                if ($isIngreso) {
                    if ($forma === 'Efectivo') {
                        $sheet->setCellValue("G{$row}", $mov['valor']);
                    } else {
                        $sheet->setCellValue("H{$row}", $mov['valor']);
                    }
                } else {
                    if ($forma === 'Efectivo') {
                        $sheet->setCellValue("I{$row}", $mov['valor']);
                    } else {
                        $sheet->setCellValue("J{$row}", $mov['valor']);
                    }
                }

                // Fórmulas de saldo acumulado desde el inicio del día
                $sheet->setCellValue("K{$row}", "=K{$firstSaldoRow}+SUM(G{$firstMovRow}:G{$row})-SUM(I{$firstMovRow}:I{$row})");
                $sheet->setCellValue("L{$row}", "=L{$firstSaldoRow}+SUM(H{$firstMovRow}:H{$row})-SUM(J{$firstMovRow}:J{$row})");
                
                $row++;
            }

            $row++; // Espacio entre días
        }

        return $this->download($spreadsheet, "{$title} {$startDate} a {$endDate}.xlsx");
    }

    /**
     * Obtiene movimientos para arqueo (ingresos y egresos)
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
        $thirdEntries = ThirdReceipts::where('type', 'entry')
            ->where('sede', $sede)
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
    public function getMissingCashBases($startDate, $endDate, $sede = 'BARRANCABERMEJA')
    {
        $dates = [];
        $current = Carbon::parse($startDate);
        $end = Carbon::parse($endDate);
        
        while ($current->lte($end)) {
            $dates[] = $current->format('Y-m-d');
            $current->addDay();
        }

        // Obtener fechas existentes y normalizar al formato Y-m-d para comparación
        $existingBases = CashBase::where('sede', $sede)->whereIn('fecha', $dates)->get();
        $existingDates = $existingBases->map(function($base) {
            return $base->fecha->format('Y-m-d');
        })->toArray();
        
        return array_diff($dates, $existingDates);
    }

    /**
     * Carga plantilla Excel
     */
    protected function loadTemplate($templateFile)
    {
        if (!file_exists($templateFile)) {
            // Si no existe plantilla, crear una básica
            return new Spreadsheet();
        }
        
        return IOFactory::load($templateFile);
    }

    /**
     * Limpia los datos de ejemplo de la plantilla Excel
     * Elimina el contenido desde startRow hasta endRow, pero mantiene los encabezados
     */
    protected function clearTemplateData($sheet, $startRow, $endRow)
    {
        try {
            // Obtener el rango completo que queremos limpiar
            $highestRow = $sheet->getHighestRow();
            $highestCol = $sheet->getHighestColumn();
            
            // Asegurarnos de limpiar desde startRow hasta el máximo entre endRow y highestRow
            $maxRow = max($endRow, $highestRow);
            
            // Método 1: Limpiar rango completo usando setCellValue directo
            for ($row = $startRow; $row <= $maxRow; $row++) {
                // Limpiar todas las columnas desde A hasta Z
                for ($colNum = ord('A'); $colNum <= ord('Z'); $colNum++) {
                    $col = chr($colNum);
                    $cell = $col . $row;
                    
                    try {
                        // Obtener la celda y limpiarla completamente
                        $cellObj = $sheet->getCell($cell);
                        
                        // Eliminar cualquier valor o fórmula
                        $cellObj->setValue(null);
                        
                        // Forzar el tipo de dato a NULL
                        try {
                            $cellObj->setDataType(DataType::TYPE_NULL);
                        } catch (\Exception $e) {
                            // Ignorar si falla
                        }
                        
                        // También limpiar el valor directamente usando el método del sheet
                        $sheet->setCellValue($cell, null);
                        
                    } catch (\Exception $e) {
                        // Continuar si hay error en una celda específica
                        continue;
                    }
                }
            }
            
            // Método 2: Intentar limpiar usando range de estilo (limpiar formato de relleno)
            try {
                $range = 'A' . $startRow . ':' . $highestCol . $maxRow;
                $sheet->getStyle($range)->getFill()->setFillType(Fill::FILL_NONE);
            } catch (\Exception $e) {
                // Ignorar si falla
            }
            
            // Método 3: Forzar recálculo de dimensiones para que Excel recalcule el área usada
            try {
                $sheet->calculateWorksheetDimension();
            } catch (\Exception $e) {
                // Ignorar si falla
            }
            
        } catch (\Exception $e) {
            // Si hay error general, registrar pero continuar
            \Log::warning('Error al limpiar plantilla: ' . $e->getMessage());
        }
    }

    /**
     * Descarga el archivo Excel
     */
    protected function download($spreadsheet, $filename)
    {
        $writer = new Xlsx($spreadsheet);
        
        $tempFile = tempnam(sys_get_temp_dir(), 'excel_');
        $writer->save($tempFile);
        
        return response()->download($tempFile, $filename)->deleteFileAfterSend(true);
    }
}
