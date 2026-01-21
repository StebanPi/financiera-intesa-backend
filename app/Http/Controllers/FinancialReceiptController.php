<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use App\Models\Entry;
use App\Models\OtherEntry;
use App\Models\EgresoReceipt;
use App\Models\ThirdReceipts;
use App\Models\Matricula;
use App\Models\Cost;

class FinancialReceiptController extends Controller
{
    /**
     * Imprimir recibo financiero unificado
     * 
     * @param string $type - Tipo de recibo: 'entry', 'other-entry', 'egreso', 'third'
     * @param int $id - ID del recibo
     * @param Request $request - Request con parámetros paper y offset
     */
    public function print($type, $id, Request $request)
    {
        // Validar tipo de recibo
        $validTypes = ['entry', 'other-entry', 'egreso', 'third'];
        if (!in_array($type, $validTypes)) {
            abort(404, 'Tipo de recibo no válido');
        }

        // Obtener parámetros
        $paper = $request->query('paper', '80'); // Default 80mm
        $paper = in_array($paper, ['76', '80']) ? $paper : '80';
        $offsetLeft = $request->query('offset', null); // Offset opcional en mm
        
        // Valores default de offset según tamaño de papel (Epson TM-U220PD)
        if ($offsetLeft === null) {
            $offsetLeft = '8'; // 8mm para ambos tamaños
        }

        // Obtener datos del recibo según el tipo
        $receiptData = $this->getReceiptData($type, $id);
        
        if (!$receiptData) {
            abort(404, 'Recibo no encontrado');
        }

        // Formatear fecha
        $fechaFormateada = $this->formatDate($receiptData['fecha'] ?? null);

        // Preparar datos para la vista
        $viewData = [
            'paper' => $paper,
            'paperWidth' => $paper . 'mm',
            'offsetLeft' => $offsetLeft . 'mm',
            'consecutivo' => $receiptData['consecutivo'] ?? null,
            'fecha' => $fechaFormateada,
            'valor' => $receiptData['valor'] ?? null,
            'concepto' => $receiptData['concepto'] ?? null,
            'descripcion' => $receiptData['descripcion'] ?? null,
            'tipo_recibo' => $type,
        ];

        // Agregar datos específicos según el tipo
        if ($type == 'entry' || $type == 'other-entry') {
            $viewData['estudiante_cedula'] = $receiptData['estudiante_cedula'] ?? null;
            $viewData['estudiante_nombre'] = $receiptData['estudiante_nombre'] ?? null;
            $viewData['tipo_documento'] = $receiptData['tipo_documento'] ?? null;
            $viewData['programa'] = $receiptData['programa'] ?? null;
        }

        if ($type == 'egreso') {
            $viewData['proveedor_nombre'] = $receiptData['proveedor_nombre'] ?? null;
            $viewData['forma'] = $receiptData['forma'] ?? null;
        }

        if ($type == 'third') {
            $viewData['tercero_nombre'] = $receiptData['tercero_nombre'] ?? null;
            $viewData['tercero_documento'] = $receiptData['tercero_documento'] ?? null;
            $viewData['forma'] = $receiptData['forma'] ?? null;
        }

        return view('prints.financial-receipt-pos', $viewData);
    }

    /**
     * Obtener datos del recibo según el tipo
     */
    private function getReceiptData($type, $id)
    {
        switch ($type) {
            case 'entry':
                return $this->getEntryData($id);
            case 'other-entry':
                return $this->getOtherEntryData($id);
            case 'egreso':
                return $this->getEgresoData($id);
            case 'third':
                return $this->getThirdReceiptData($id);
            default:
                return null;
        }
    }

    /**
     * Obtener datos de Entry (Ingreso)
     */
    private function getEntryData($id)
    {
        $entry = Entry::where('id', $id)->first();
        if (!$entry) {
            return null;
        }

        $concepto = DB::connection('mysql')->select('SELECT * FROM conceptos WHERE id = "' . $entry->concepto . '"');
        $sqlCodAlumno = DB::connection('mysql')->select('SELECT costs.cod_alumno FROM `entries` INNER JOIN costs ON costs.id = entries.id_cost WHERE entries.id = "' . $id . '"');

        $estudianteCedula = null;
        $estudianteNombre = null;
        $programaNombre = null;
        $tipoDocumento = null;

        if (!empty($sqlCodAlumno) && isset($sqlCodAlumno[0]->cod_alumno)) {
            try {
                $sqlAlumno = DB::connection('mysql2')->select(
                    'SELECT alumno.nombre, alumno.cedula, programa.nombre_programa 
                     FROM alumno 
                     INNER JOIN relacion_programa_estudiante ON relacion_programa_estudiante.Alumno_cod = alumno.cod_alumno 
                     INNER JOIN programa ON programa.cod_programa = relacion_programa_estudiante.programa_cod 
                     WHERE alumno.cod_alumno = "' . $sqlCodAlumno[0]->cod_alumno . '"'
                );

                if (!empty($sqlAlumno)) {
                    $estudianteCedula = $sqlAlumno[0]->cedula ?? null;
                    $estudianteNombre = $sqlAlumno[0]->nombre ?? null;
                    $programaNombre = $sqlAlumno[0]->nombre_programa ?? null;
                } else {
                    $matricula = Matricula::where('cod_alumno', $sqlCodAlumno[0]->cod_alumno)->first();
                    if ($matricula) {
                        $estudianteCedula = $matricula->numero_documento ?? null;
                        $estudianteNombre = $matricula->nombre_completo ?? null;
                        $programaNombre = $matricula->programa ?? null;
                        $tipoDocumento = $matricula->tipo_documento ?? null;
                    }
                }
            } catch (\Exception $e) {
                \Log::error('Error al obtener datos del estudiante: ' . $e->getMessage());
                $matricula = Matricula::where('cod_alumno', $sqlCodAlumno[0]->cod_alumno)->first();
                if ($matricula) {
                    $estudianteCedula = $matricula->numero_documento ?? null;
                    $estudianteNombre = $matricula->nombre_completo ?? null;
                    $programaNombre = $matricula->programa ?? null;
                    $tipoDocumento = $matricula->tipo_documento ?? null;
                }
            }
        }

        return [
            'consecutivo' => $entry->no_recibo ?? $entry->id,
            'fecha' => $entry->fecha_recibo ?? null,
            'valor' => $entry->valor ?? null,
            'concepto' => $concepto[0]->nombre ?? null,
            'descripcion' => $entry->descripcion ?? null,
            'estudiante_cedula' => $estudianteCedula,
            'estudiante_nombre' => $estudianteNombre,
            'tipo_documento' => $tipoDocumento,
            'programa' => $programaNombre,
        ];
    }

    /**
     * Obtener datos de OtherEntry (Otros Ingresos)
     */
    private function getOtherEntryData($id)
    {
        $entry = OtherEntry::where('id', $id)->first();
        if (!$entry) {
            return null;
        }

        $concepto = DB::connection('mysql')->select('SELECT * FROM otros_conceptos WHERE id = "' . $entry->concepto . '"');
        $infoCost = Cost::where('id', $entry->id_cost)->first();

        $personaData = null;
        $programaNombre = null;
        $tipoDocumento = null;

        if ($infoCost && $infoCost->cod_alumno) {
            try {
                $personaData = DB::connection('mysql2')->select(
                    'SELECT alumno.cedula, alumno.nombre, programa.nombre_programa 
                     FROM alumno 
                     INNER JOIN relacion_programa_estudiante ON relacion_programa_estudiante.Alumno_cod = alumno.cod_alumno 
                     INNER JOIN programa ON programa.cod_programa = relacion_programa_estudiante.programa_cod 
                     WHERE alumno.cod_alumno = "' . $infoCost->cod_alumno . '"'
                );

                if (empty($personaData)) {
                    $matricula = Matricula::where('cod_alumno', $infoCost->cod_alumno)->first();
                    if ($matricula) {
                        $personaData = [
                            (object)[
                                'cedula' => $matricula->numero_documento ?? '',
                                'nombre' => $matricula->nombre_completo ?? 'N/A',
                            ]
                        ];
                        $programaNombre = $matricula->programa ?? null;
                        $tipoDocumento = $matricula->tipo_documento ?? null;
                    }
                } else {
                    $programaNombre = $personaData[0]->nombre_programa ?? null;
                    $matricula = Matricula::where('cod_alumno', $infoCost->cod_alumno)->first();
                    if ($matricula) {
                        $tipoDocumento = $matricula->tipo_documento ?? null;
                    }
                }
            } catch (\Exception $e) {
                \Log::error('Error al obtener datos: ' . $e->getMessage());
                $matricula = Matricula::where('cod_alumno', $infoCost->cod_alumno)->first();
                if ($matricula) {
                    $personaData = [
                        (object)[
                            'cedula' => $matricula->numero_documento ?? '',
                            'nombre' => $matricula->nombre_completo ?? 'N/A',
                        ]
                    ];
                    $programaNombre = $matricula->programa ?? null;
                    $tipoDocumento = $matricula->tipo_documento ?? null;
                }
            }
        }

        return [
            'consecutivo' => $entry->no_recibo ?? $entry->id,
            'fecha' => $entry->fecha_recibo ?? null,
            'valor' => $entry->valor ?? null,
            'concepto' => $concepto[0]->nombre ?? null,
            'descripcion' => $entry->descripcion ?? null,
            'estudiante_cedula' => $personaData[0]->cedula ?? null,
            'estudiante_nombre' => $personaData[0]->nombre ?? null,
            'tipo_documento' => $tipoDocumento,
            'programa' => $programaNombre,
        ];
    }

    /**
     * Obtener datos de EgresoReceipt (Egreso)
     */
    private function getEgresoData($id)
    {
        $receipt = EgresoReceipt::where('id', $id)->with(['provider', 'conceptoObject'])->first();
        if (!$receipt) {
            return null;
        }

        $conceptoNombre = null;
        if ($receipt->conceptoObject) {
            $conceptoNombre = $receipt->conceptoObject->nombre ?? null;
        } elseif ($receipt->concepto) {
            try {
                $concepto = \App\Models\EgresoConcept::find($receipt->concepto);
                $conceptoNombre = $concepto->nombre ?? null;
            } catch (\Exception $e) {
                \Log::error('Error al obtener concepto: ' . $e->getMessage());
            }
        }

        return [
            'consecutivo' => $receipt->no_recibo ?? $receipt->id,
            'fecha' => $receipt->fecha_recibo ?? null,
            'valor' => $receipt->valor ?? null,
            'concepto' => $conceptoNombre,
            'descripcion' => $receipt->descripcion ?? null,
            'proveedor_nombre' => $receipt->provider->nombre ?? null,
            'forma' => $receipt->forma ?? null,
        ];
    }

    /**
     * Obtener datos de ThirdReceipts (Terceros)
     */
    private function getThirdReceiptData($id)
    {
        $receipt = ThirdReceipts::where('id', $id)->with(['thirdObject', 'conceptoObject'])->first();
        if (!$receipt) {
            return null;
        }

        // Obtener datos del tercero
        $terceroNombre = null;
        $terceroDocumento = null;
        if ($receipt->thirdObject) {
            $terceroNombre = $receipt->thirdObject->nombre ?? null;
            $terceroDocumento = $receipt->thirdObject->cedula ?? $receipt->thirdObject->documento ?? null;
        }

        // Obtener nombre del concepto
        $conceptoNombre = null;
        if ($receipt->conceptoObject) {
            $conceptoNombre = $receipt->conceptoObject->name ?? null;
        } elseif ($receipt->concepto) {
            try {
                $concepto = \App\Models\ConceptEntryReceipt::find($receipt->concepto);
                $conceptoNombre = $concepto->name ?? null;
            } catch (\Exception $e) {
                \Log::error('Error al obtener concepto de tercero: ' . $e->getMessage());
            }
        }

        return [
            'consecutivo' => $receipt->no_recibo ?? $receipt->id,
            'fecha' => $receipt->fecha_recibo ?? null,
            'valor' => $receipt->valor ?? null,
            'concepto' => $conceptoNombre,
            'descripcion' => $receipt->detalles ?? null,
            'tercero_nombre' => $terceroNombre,
            'tercero_documento' => $terceroDocumento,
            'forma' => $receipt->forma ?? null,
        ];
    }

    /**
     * Formatear fecha al formato esperado (ej: 03-may.-25)
     */
    private function formatDate($fecha)
    {
        if (!$fecha) {
            return '';
        }

        $fechaStr = explode(' ', $fecha)[0];
        $fechaParts = explode('-', $fechaStr);
        
        if (count($fechaParts) >= 3) {
            $meses = [
                '01' => 'ene', '02' => 'feb', '03' => 'mar', '04' => 'abr',
                '05' => 'may', '06' => 'jun', '07' => 'jul', '08' => 'ago',
                '09' => 'sep', '10' => 'oct', '11' => 'nov', '12' => 'dic'
            ];
            $dia = $fechaParts[2] ?? '';
            $mes = $meses[$fechaParts[1] ?? ''] ?? $fechaParts[1] ?? '';
            $anio = substr($fechaParts[0] ?? '', -2);
            return $dia . '-' . $mes . '.-' . $anio;
        }
        
        return $fechaStr;
    }
}
