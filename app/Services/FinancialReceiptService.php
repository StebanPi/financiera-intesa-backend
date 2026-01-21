<?php

namespace App\Services;

use App\Models\Cost;
use App\Models\Entry;
use App\Models\EgresoConcept;
use App\Models\EgresoReceipt;
use App\Models\Matricula;
use App\Models\OtherEntry;
use App\Models\ThirdReceipts;
use App\Models\ConceptEntryReceipt;
use Illuminate\Support\Facades\DB;

class FinancialReceiptService
{
    /** @return array<string, mixed>|null */
    public function getReceiptData(string $type, int $id): ?array
    {
        return match ($type) {
            'entry' => $this->getEntryData($id),
            'other-entry' => $this->getOtherEntryData($id),
            'egreso' => $this->getEgresoData($id),
            'third' => $this->getThirdReceiptData($id),
            default => null,
        };
    }

    public function formatDate($fecha): string
    {
        if (!$fecha) {
            return '';
        }
        $fechaStr = is_string($fecha) ? explode(' ', $fecha)[0] : $fecha->format('Y-m-d');
        $fechaParts = explode('-', $fechaStr);
        if (count($fechaParts) >= 3) {
            $meses = ['01' => 'ene', '02' => 'feb', '03' => 'mar', '04' => 'abr', '05' => 'may', '06' => 'jun', '07' => 'jul', '08' => 'ago', '09' => 'sep', '10' => 'oct', '11' => 'nov', '12' => 'dic'];
            $dia = $fechaParts[2] ?? '';
            $mes = $meses[$fechaParts[1] ?? ''] ?? $fechaParts[1] ?? '';
            $anio = substr($fechaParts[0] ?? '', -2);
            return $dia . '-' . $mes . '.-' . $anio;
        }
        return $fechaStr;
    }

    /** @return array<string, mixed>|null */
    private function getEntryData(int $id): ?array
    {
        $entry = Entry::where('id', $id)->first();
        if (!$entry) {
            return null;
        }
        $concepto = DB::connection('mysql')->select('SELECT * FROM conceptos WHERE id = ?', [$entry->concepto]);
        $sqlCodAlumno = DB::connection('mysql')->select('SELECT costs.cod_alumno FROM entries INNER JOIN costs ON costs.id = entries.id_cost WHERE entries.id = ?', [$id]);
        $estudianteCedula = $estudianteNombre = $programaNombre = $tipoDocumento = null;
        if (!empty($sqlCodAlumno) && isset($sqlCodAlumno[0]->cod_alumno)) {
            try {
                $sqlAlumno = DB::connection('mysql2')->select(
                    'SELECT alumno.nombre, alumno.cedula, programa.nombre_programa FROM alumno INNER JOIN relacion_programa_estudiante ON relacion_programa_estudiante.Alumno_cod = alumno.cod_alumno INNER JOIN programa ON programa.cod_programa = relacion_programa_estudiante.programa_cod WHERE alumno.cod_alumno = ?',
                    [$sqlCodAlumno[0]->cod_alumno]
                );
                if (!empty($sqlAlumno)) {
                    $estudianteCedula = $sqlAlumno[0]->cedula ?? null;
                    $estudianteNombre = $sqlAlumno[0]->nombre ?? null;
                    $programaNombre = $sqlAlumno[0]->nombre_programa ?? null;
                } else {
                    $m = Matricula::where('cod_alumno', $sqlCodAlumno[0]->cod_alumno)->first();
                    if ($m) {
                        $estudianteCedula = $m->numero_documento ?? null;
                        $estudianteNombre = $m->nombre_completo ?? null;
                        $programaNombre = $m->programa ?? null;
                        $tipoDocumento = $m->tipo_documento ?? null;
                    }
                }
            } catch (\Throwable $e) {
                $m = Matricula::where('cod_alumno', $sqlCodAlumno[0]->cod_alumno)->first();
                if ($m) {
                    $estudianteCedula = $m->numero_documento ?? null;
                    $estudianteNombre = $m->nombre_completo ?? null;
                    $programaNombre = $m->programa ?? null;
                    $tipoDocumento = $m->tipo_documento ?? null;
                }
            }
        }
        return [
            'consecutivo' => $entry->no_recibo ?? $entry->id,
            'fecha' => $entry->fecha_recibo ?? null,
            'valor' => $entry->valor ?? null,
            'concepto' => isset($concepto[0]) ? ($concepto[0]->nombre ?? null) : null,
            'descripcion' => $entry->descripcion ?? null,
            'estudiante_cedula' => $estudianteCedula,
            'estudiante_nombre' => $estudianteNombre,
            'tipo_documento' => $tipoDocumento,
            'programa' => $programaNombre,
        ];
    }

    /** @return array<string, mixed>|null */
    private function getOtherEntryData(int $id): ?array
    {
        $entry = OtherEntry::where('id', $id)->first();
        if (!$entry) {
            return null;
        }
        $concepto = DB::connection('mysql')->select('SELECT * FROM otros_conceptos WHERE id = ?', [$entry->concepto]);
        $infoCost = Cost::where('id', $entry->id_cost)->first();
        $personaData = null;
        $programaNombre = $tipoDocumento = null;
        if ($infoCost && $infoCost->cod_alumno) {
            try {
                $personaData = DB::connection('mysql2')->select(
                    'SELECT alumno.cedula, alumno.nombre, programa.nombre_programa FROM alumno INNER JOIN relacion_programa_estudiante ON relacion_programa_estudiante.Alumno_cod = alumno.cod_alumno INNER JOIN programa ON programa.cod_programa = relacion_programa_estudiante.programa_cod WHERE alumno.cod_alumno = ?',
                    [$infoCost->cod_alumno]
                );
                if (empty($personaData)) {
                    $m = Matricula::where('cod_alumno', $infoCost->cod_alumno)->first();
                    if ($m) {
                        $personaData = [(object)['cedula' => $m->numero_documento ?? '', 'nombre' => $m->nombre_completo ?? 'N/A']];
                        $programaNombre = $m->programa ?? null;
                        $tipoDocumento = $m->tipo_documento ?? null;
                    }
                } else {
                    $programaNombre = $personaData[0]->nombre_programa ?? null;
                    $m = Matricula::where('cod_alumno', $infoCost->cod_alumno)->first();
                    if ($m) {
                        $tipoDocumento = $m->tipo_documento ?? null;
                    }
                }
            } catch (\Throwable $e) {
                $m = Matricula::where('cod_alumno', $infoCost->cod_alumno)->first();
                if ($m) {
                    $personaData = [(object)['cedula' => $m->numero_documento ?? '', 'nombre' => $m->nombre_completo ?? 'N/A']];
                    $programaNombre = $m->programa ?? null;
                    $tipoDocumento = $m->tipo_documento ?? null;
                }
            }
        }
        return [
            'consecutivo' => $entry->no_recibo ?? $entry->id,
            'fecha' => $entry->fecha_recibo ?? null,
            'valor' => $entry->valor ?? null,
            'concepto' => isset($concepto[0]) ? ($concepto[0]->nombre ?? null) : null,
            'descripcion' => $entry->descripcion ?? null,
            'estudiante_cedula' => isset($personaData[0]) ? ($personaData[0]->cedula ?? null) : null,
            'estudiante_nombre' => isset($personaData[0]) ? ($personaData[0]->nombre ?? null) : null,
            'tipo_documento' => $tipoDocumento,
            'programa' => $programaNombre,
        ];
    }

    /** @return array<string, mixed>|null */
    private function getEgresoData(int $id): ?array
    {
        $receipt = EgresoReceipt::where('id', $id)->with(['provider', 'conceptoObject'])->first();
        if (!$receipt) {
            return null;
        }
        $conceptoNombre = $receipt->conceptoObject?->nombre ?? null;
        if (!$conceptoNombre && $receipt->concepto) {
            $c = EgresoConcept::find($receipt->concepto);
            $conceptoNombre = $c->nombre ?? null;
        }
        return [
            'consecutivo' => $receipt->no_recibo ?? $receipt->id,
            'fecha' => $receipt->fecha_recibo ?? null,
            'valor' => $receipt->valor ?? null,
            'concepto' => $conceptoNombre,
            'descripcion' => $receipt->descripcion ?? null,
            'proveedor_nombre' => $receipt->provider?->nombre ?? null,
            'forma' => $receipt->forma ?? null,
        ];
    }

    /** @return array<string, mixed>|null */
    private function getThirdReceiptData(int $id): ?array
    {
        $receipt = ThirdReceipts::where('id', $id)->with(['thirdObject', 'conceptoObject'])->first();
        if (!$receipt) {
            return null;
        }
        $terceroNombre = $receipt->thirdObject?->nombre ?? null;
        $terceroDocumento = $receipt->thirdObject?->cedula ?? $receipt->thirdObject?->documento ?? null;
        $conceptoNombre = $receipt->conceptoObject?->name ?? null;
        if (!$conceptoNombre && $receipt->concepto) {
            $c = ConceptEntryReceipt::find($receipt->concepto);
            $conceptoNombre = $c->name ?? null;
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
}
