<?php

namespace App\Http\Controllers;
use App\Models\consecutive;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use App\Models\OtherEntry;
use App\Http\Controllers\MoneyController;
use App\Http\Requests\OtherEntryRequest;
use Dompdf\Dompdf;
use App\Models\table_change;
use App\Models\Cost;
use App\Http\Controllers\TableChangeController;
use App\Models\InstitutionSetting;

class OtherEntryController extends Controller
{
    //

    public static function getOtherEntry($id,$conPuntos){
        $OtherEntries = DB::connection('mysql')->select('SELECT other_entries.id, other_entries.id_cost, otros_conceptos.nombre AS concepto, other_entries.descripcion, other_entries.no_recibo, other_entries.fecha_recibo, other_entries.valor,elaborados.nombre AS elaborado_por, CONCAT(debes.cuenta, " - ", debes.nombre) AS debe , CONCAT(habers.cuenta, " - ", habers.nombre) AS haber, other_entries.created_at FROM other_entries INNER JOIN costs ON costs.id = other_entries.id_cost INNER JOIN otros_conceptos ON otros_conceptos.id = other_entries.concepto INNER JOIN elaborados ON elaborados.id = other_entries.elaborado_por INNER JOIN debes ON debes.id = other_entries.debe INNER JOIN habers ON habers.id = other_entries.haber WHERE costs.cod_alumno ="'.$id.'" ORDER BY other_entries.no_recibo ASC');
        if($conPuntos){
            for ($i=0; $i < count($OtherEntries); $i++) { 
                $OtherEntries[$i] = MoneyController::datas($OtherEntries[$i],['valor']);
            }
        }
        return $OtherEntries;
    }

    public function all(Request $request){
        //$all = Entry::where('id_cost',$request->id)->get();
        $all = DB::connection('mysql')->select('SELECT other_entries.id, other_entries.id_cost, otros_conceptos.nombre AS concepto, other_entries.descripcion, other_entries.no_recibo, other_entries.fecha_recibo, other_entries.valor,elaborados.nombre AS elaborado_por, CONCAT(debes.cuenta, " - ", debes.nombre) AS debe , CONCAT(habers.cuenta, " - ", habers.nombre) AS haber, other_entries.created_at FROM other_entries INNER JOIN otros_conceptos ON otros_conceptos.id = other_entries.concepto INNER JOIN elaborados ON elaborados.id = other_entries.elaborado_por INNER JOIN debes ON debes.id = other_entries.debe INNER JOIN habers ON habers.id = other_entries.haber WHERE other_entries.id_cost ="'.$request->id.'" ORDER BY other_entries.no_recibo ASC');
        echo json_encode($all);
    }

    public function store(OtherEntryRequest $request){
        $con = consecutive::where('type','entry')->first();
        // Normalizar forma de pago: Consignación -> Bancos
        $forma = $request->forma ?? 'Efectivo';
        if ($forma === 'Consignación') {
            $forma = 'Bancos';
        }
        $sql = OtherEntry::create([
            'id_cost' => $request->id_cost,
            'concepto' => $request->concepto,
            'descripcion' => $request->descripcion,
            'no_recibo' => $request->no_recibo,
            'fecha_recibo' => $request->fecha_recibo,
            'valor' => str_replace(".","",$request->valor),
            'elaborado_por' => $request->elaborado_por,
            'debe' => $request->debe,
            'haber' => $request->haber,
            'forma' => $forma
        ]);
        //table_change::create(['table' => 'other_entries','id_change' => $sql->id, 'add' => 1,'edit' => 0, 'delete' => 0]);
        TableChangeController::StoreAdd('other_entries',$sql->id);
        if($request->no_recibo != "" && $request->no_recibo >= $con->num_start){
            $new_current = intval($request->no_recibo)+1;
            $modificacion = DB::connection('mysql')->select('UPDATE consecutives SET num_current = "'.$new_current.'" WHERE id = "1"');
            //table_change::create(['table' => 'consecutives','id_change' => '1', 'add' => 0,'edit' => 1, 'delete' => 0]);
            TableChangeController::StoreEdit('consecutives',1);
        }
        
        // Si es una petición AJAX, retornar JSON
        if($request->ajax() || $request->wantsJson()) {
            return response()->json(['success' => true, 'message' => 'Otro ingreso guardado correctamente']);
        }
        
        // Si no es AJAX, redirigir de vuelta para refrescar
        return redirect()->back()->with('success', 'Otro ingreso guardado correctamente');
    }

    public function get($id){
        $entry = OtherEntry::where('id',$id)->first();
        if(!$entry){
            return response()->json(['error' => 'OtherEntry not found'], 404);
        }
        
        $infoCost = Cost::where('id', $entry->id_cost)->first();
        $entryData = [
            'id' => $entry->id,
            'id_cost' => $entry->id_cost,
            'cod_alumno' => $infoCost ? $infoCost->cod_alumno : '',
            'concepto' => $entry->concepto,
            'descripcion' => $entry->descripcion,
            'no_recibo' => $entry->no_recibo,
            'fecha_recibo' => $entry->fecha_recibo,
            'valor' => $entry->valor,
            'elaborado_por' => $entry->elaborado_por,
            'debe' => $entry->debe,
            'haber' => $entry->haber,
            'forma' => $entry->forma ?? 'Efectivo'
        ];
        
        return response()->json($entryData);
    }

    public function update(Request $request,$id){
        try {
            $entry = OtherEntry::where('id',$id)->firstOrFail();
            
            // Normalizar forma de pago: Consignación -> Bancos
            $forma = $request->forma ?? $entry->forma ?? 'Efectivo';
            if ($forma === 'Consignación') {
                $forma = 'Bancos';
            }
            
            // Limpiar el valor (quitar puntos de formato de miles)
            $valor = str_replace(".","",$request->valor);
            
            $entry->concepto = $request->concepto;
            $entry->descripcion = $request->descripcion;
            $entry->fecha_recibo = $request->fecha_recibo;
            $entry->valor = $valor;
            $entry->elaborado_por = $request->elaborado_por;
            $entry->debe = $request->debe;
            $entry->haber = $request->haber;
            $entry->forma = $forma;
            $entry->save();
            
            TableChangeController::StoreEdit('other_entries',$entry->id);
            
            echo 'OK';
        } catch (\Exception $e) {
            \Log::error('Error al actualizar other_entry: ' . $e->getMessage());
            return response()->json(['error' => $e->getMessage()], 500);
        }
    }

    public function show($id){
        $i = OtherEntry::where('no_recibo',$id)->count();
        if($i > 0){
            $item = OtherEntry::where('no_recibo',$id)->first();
            $infoCost = Cost::where('id', $item->id_cost)->first();
            $alumno = DB::connection('mysql2')->select('SELECT nombre FROM alumno WHERE cod_alumno = "'.$infoCost->cod_alumno.'"');

            $struct = [
                "id" => $item->id,
                "id_cost" => $item->id_cost,
                "cod_alumno" => $infoCost->cod_alumno,
                "nombre" => $alumno[0]->nombre,
                "concepto" => $item->concepto,
                "descripcion" => $item->descripcion,
                "no_recibo" => $item->no_recibo,
                "fecha_recibo" => $item->fecha_recibo,
                "valor" => $item->valor,
                "elaborado_por" => $item->elaborado_por,
                "debe" => $item->debe,
                "haber" => $item->haber,
                "forma" => $item->forma
            ];
            
            return view('viewStudent.otrosAbonos.show')->with('content', json_encode($struct));
        }else{
            return redirect()->route('otros.abonos');
        }
    }

    public function destroy($id){
        // Solo permitir eliminación a admin y super-admin
        if (!auth()->check() || (!auth()->user()->hasRole('admin') && !auth()->user()->hasRole('super-admin'))) {
            return response()->json(['error' => 'No tienes permisos para eliminar otros abonos.'], 403);
        }
        
        try {
            $entry = OtherEntry::where('id',$id)->first();
            if (!$entry) {
                return response()->json(['error' => 'Otro abono no encontrado.'], 404);
            }
            $entry->delete();
            //table_change::create(['table' => 'other_entries','id_change' => $entry->id, 'add' => 0,'edit' => 0, 'delete' => 0]);
            TableChangeController::StoreDelete('other_entries',$entry->id);
            return view('viewStudent.close');
        } catch (\Exception $e) {
            return response()->json(['error' => 'Error al eliminar el otro abono: ' . $e->getMessage()], 500);
        }
    }

    public function print($id){
        $entry = OtherEntry::where('id',$id)->firstOrFail();
        $concepto = DB::connection('mysql')->select('SELECT * FROM otros_conceptos WHERE id = "'.$entry->concepto.'"');
        $infoCost = Cost::where('id', $entry->id_cost)->first();
        
        // Obtener datos del estudiante/persona
        $personaData = null;
        $programaNombre = null;
        $tipoDocumento = null;
        
        if ($infoCost && $infoCost->cod_alumno) {
            try {
                // Intentar obtener desde la base de datos mysql2 con programa
                $personaData = DB::connection('mysql2')->select(
                    'SELECT alumno.cedula, alumno.nombre, programa.nombre_programa 
                     FROM alumno 
                     INNER JOIN relacion_programa_estudiante ON relacion_programa_estudiante.Alumno_cod = alumno.cod_alumno 
                     INNER JOIN programa ON programa.cod_programa = relacion_programa_estudiante.programa_cod 
                     WHERE alumno.cod_alumno = "' . $infoCost->cod_alumno . '"'
                );
                
                if (empty($personaData)) {
                    // Intentar sin programa
                    $personaData = DB::connection('mysql2')->select(
                        'SELECT alumno.cedula, alumno.nombre 
                         FROM alumno 
                         WHERE alumno.cod_alumno = "' . $infoCost->cod_alumno . '"'
                    );
                }
                
                if (empty($personaData)) {
                    // Fallback: buscar en la tabla matriculas
                    $matricula = \App\Models\Matricula::where('cod_alumno', $infoCost->cod_alumno)->first();
                    if ($matricula) {
                        $personaData = [
                            (object)[
                                'cedula' => $matricula->numero_documento ?? '',
                                'nombre' => $matricula->nombre_completo ?? 'N/A',
                                'nombre_programa' => $matricula->programa ?? null
                            ]
                        ];
                        $programaNombre = $matricula->programa ?? null;
                        $tipoDocumento = $matricula->tipo_documento ?? null;
                    }
                } else {
                    // Si se obtuvo el programa desde la consulta
                    if (isset($personaData[0]->nombre_programa)) {
                        $programaNombre = $personaData[0]->nombre_programa;
                    }
                    // Intentar obtener tipo de documento desde matrícula si existe
                    $matricula = \App\Models\Matricula::where('cod_alumno', $infoCost->cod_alumno)->first();
                    if ($matricula) {
                        $tipoDocumento = $matricula->tipo_documento ?? null;
                    }
                }
            } catch (\Exception $e) {
                \Log::error('Error al obtener datos de la persona: ' . $e->getMessage());
                // Fallback: buscar en tabla matriculas
                try {
                    $matricula = \App\Models\Matricula::where('cod_alumno', $infoCost->cod_alumno)->first();
                    if ($matricula) {
                        $personaData = [
                            (object)[
                                'cedula' => $matricula->numero_documento ?? '',
                                'nombre' => $matricula->nombre_completo ?? 'N/A',
                                'nombre_programa' => $matricula->programa ?? null
                            ]
                        ];
                        $programaNombre = $matricula->programa ?? null;
                        $tipoDocumento = $matricula->tipo_documento ?? null;
                    }
                } catch (\Exception $e2) {
                    \Log::error('Error en fallback de matricula: ' . $e2->getMessage());
                }
            }
        }
        
        // Formatear fecha según el formato esperado (ej: 03-may.-25)
        $fechaFormateada = '';
        if ($entry->fecha_recibo) {
            // Extraer solo la parte de la fecha (antes del espacio si existe hora)
            $fechaStr = explode(' ', $entry->fecha_recibo)[0];
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
                $fechaFormateada = $dia . '-' . $mes . '.-' . $anio;
            } else {
                $fechaFormateada = $fechaStr;
            }
        }
        
        return view('prints.other-entry-pos', [
            'consecutivo' => $entry->no_recibo ?? $entry->id, // Usar no_recibo como consecutivo
            'persona_cedula' => $personaData[0]->cedula ?? null,
            'persona_nombre' => $personaData[0]->nombre ?? null,
            'tipo_documento' => $tipoDocumento,
            'programa' => $programaNombre ?? ($personaData[0]->nombre_programa ?? null),
            'concepto' => $concepto[0]->nombre ?? null,
            'descripcion' => $entry->descripcion ?? null,
            'valor' => $entry->valor ?? null,
            'fecha' => $fechaFormateada,
        ]);
    }

    public function ViewPdf($id){
        try {
            // Obtener el costo
            $arrayCost = DB::table('costs')->where('id', $id)->first();
            
            if (!$arrayCost) {
                \Log::error("Costo no encontrado para ID: {$id}");
                abort(404, 'Costo no encontrado');
            }
            
            // Obtener datos del estudiante (usando el mismo formato que viewStudentController)
            $data = [];
            if (isset($arrayCost->cod_alumno) && !empty($arrayCost->cod_alumno)) {
                try {
                    // Usar el mismo formato que viewStudentController::carteraTable
                    $data = DB::connection('mysql2')->select(
                        'SELECT alumno.cedula, alumno.nombre, programa.nombre_programa
                         FROM alumno
                         INNER JOIN relacion_programa_estudiante ON relacion_programa_estudiante.Alumno_cod = alumno.cod_alumno
                         INNER JOIN programa ON programa.cod_programa = relacion_programa_estudiante.programa_cod
                         WHERE alumno.cod_alumno = "' . $arrayCost->cod_alumno . '"'
                    );
                    if (empty($data)) {
                        // Intentar sin la relación de programa (por si no tiene programa asignado)
                        $data = DB::connection('mysql2')->select(
                            'SELECT alumno.cedula, alumno.nombre, "" AS nombre_programa
                             FROM alumno
                             WHERE alumno.cod_alumno = "' . $arrayCost->cod_alumno . '"'
                        );
                    }
                    if (empty($data)) {
                        // Fallback: buscar en la tabla matriculas (como en CostController)
                        $matricula = \App\Models\Matricula::where('cod_alumno', $arrayCost->cod_alumno)->first();
                        if ($matricula) {
                            $data = [
                                (object)[
                                    'cedula' => $matricula->numero_documento ?? '',
                                    'nombre' => $matricula->nombre_completo ?? 'N/A',
                                    'nombre_programa' => $matricula->programa ?? ''
                                ]
                            ];
                        }
                    }
                } catch (\Exception $e) {
                    \Log::error('Error al obtener datos del estudiante: ' . $e->getMessage());
                    \Log::error('Stack trace: ' . $e->getTraceAsString());
                    $data = [];
                }
            } else {
                \Log::warning('cod_alumno no está definido o está vacío en arrayCost');
            }
            
            // Obtener datos para otros abonos (sin Livewire)
            $cost = DB::table('costs')->where('id', $id)->get();
            $entries = DB::connection('mysql')->select(
                'SELECT other_entries.id, other_entries.id_cost, otros_conceptos.nombre AS concepto, other_entries.descripcion, other_entries.no_recibo, other_entries.fecha_recibo, other_entries.valor, elaborados.nombre AS elaborado_por, CONCAT(debes.cuenta, " - ", debes.nombre) AS debe, CONCAT(habers.cuenta, " - ", habers.nombre) AS haber, other_entries.created_at 
                 FROM other_entries 
                 INNER JOIN otros_conceptos ON otros_conceptos.id = other_entries.concepto 
                 INNER JOIN elaborados ON elaborados.id = other_entries.elaborado_por 
                 INNER JOIN debes ON debes.id = other_entries.debe 
                 INNER JOIN habers ON habers.id = other_entries.haber 
                 WHERE other_entries.id_cost = ? 
                 ORDER BY other_entries.no_recibo ASC',
                [$id]
            );
            
            // Obtener configuración de la institución
            $institucion = InstitutionSetting::getSettings();
            
            // Generar el PDF
            $dompdf = new Dompdf();
            $html = view('PDFs.pdf_otrosAbonos', [
                'id_cost' => $id,
                'student' => $data,
                'cost' => $cost,
                'entries' => $entries,
                'institucion' => $institucion // Pasar configuración de institución
            ])->render();
            
            $dompdf->loadHtml($html);
            $dompdf->setPaper('A4', 'portrait');
            $dompdf->render();
            
            // Nombre del archivo
            $nombreEstudiante = 'estudiante';
            if (!empty($data) && isset($data[0]) && isset($data[0]->nombre)) {
                $nombreEstudiante = $data[0]->nombre;
            }
            
            // Enviar el PDF al navegador
            return $dompdf->stream('informe-otros-abonos-' . $nombreEstudiante . '.pdf', [
                'Attachment' => true
            ]);
            
        } catch (\Exception $e) {
            \Log::error('Error al generar PDF de otros abonos: ' . $e->getMessage());
            \Log::error('Stack trace: ' . $e->getTraceAsString());
            abort(500, 'Error al generar el PDF: ' . $e->getMessage());
        }
    }
    
       
}
