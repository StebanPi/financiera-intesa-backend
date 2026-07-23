<?php

namespace App\Http\Controllers;
use App\Models\Entry;
use App\Models\Purse;
use App\Models\historyPurse;
use Illuminate\Http\Request;
use App\Models\Cost;
use App\Models\consecutive;
use App\Http\Requests\EntryRequest;
use Illuminate\Support\Facades\DB;
use App\Http\Controllers\MoneyController;
use App\Http\Controllers\DateController;
use Mike42\Escpos\PrintConnectors\FilePrintConnector;
use Mike42\Escpos\PrintConnectors\WindowsPrintConnector;
use Mike42\Escpos\EscposImage;
use Mike42\Escpos\Printer;
use App\Models\table_change;
use App\Http\Controllers\TableChangeController;
use App\Models\InstitutionSetting;
use Dompdf\Dompdf;


class EntryController extends Controller
{
    /**
     * Valida y ajusta una fecha para asegurar que sea válida
     * Si el día no existe en el mes, lo ajusta al último día válido del mes
     * 
     * @param int $year Año
     * @param int $month Mes (1-12)
     * @param int $day Día
     * @return string Fecha válida en formato Y-m-d
     */
    private static function validateAndAdjustDate($year, $month, $day)
    {
        // Validar que la fecha sea válida
        if (checkdate($month, $day, $year)) {
            return sprintf('%04d-%02d-%02d', $year, $month, $day);
        }
        
        // Si no es válida, obtener el último día del mes
        $lastDay = date('t', mktime(0, 0, 0, $month, 1, $year));
        return sprintf('%04d-%02d-%02d', $year, $month, $lastDay);
    }
  
    public static function getEntry($id,$conPuntos){
        $Entries = DB::connection('mysql')->select('SELECT entries.id, entries.id_cost, conceptos.nombre AS concepto, entries.descripcion, entries.no_recibo, entries.fecha_recibo, entries.valor,elaborados.nombre AS elaborado_por, CONCAT(debes.cuenta, " - ", debes.nombre) AS debe , CONCAT(habers.cuenta, " - ", habers.nombre) AS haber, entries.created_at FROM entries INNER JOIN costs ON costs.id = entries.id_cost INNER JOIN conceptos ON conceptos.id = entries.concepto INNER JOIN elaborados ON elaborados.id = entries.elaborado_por INNER JOIN debes ON debes.id = entries.debe INNER JOIN habers ON habers.id = entries.haber WHERE costs.cod_alumno ="'.$id.'" ORDER BY entries.no_recibo ASC');
        if($conPuntos){
            for ($i=0; $i < count($Entries); $i++) { 
                $Entries[$i] = MoneyController::datas($Entries[$i],['valor']);
            }
        }
        return $Entries;
    }

    public function all(Request $request){
        //$all = Entry::where('id_cost',$request->id)->get();
        $all = DB::connection('mysql')->select('SELECT entries.id, entries.id_cost, conceptos.nombre AS concepto, entries.descripcion, entries.no_recibo, entries.fecha_recibo, entries.valor,elaborados.nombre AS elaborado_por, CONCAT(debes.cuenta, " - ", debes.nombre) AS debe , CONCAT(habers.cuenta, " - ", habers.nombre) AS haber, entries.created_at FROM entries INNER JOIN conceptos ON conceptos.id = entries.concepto INNER JOIN elaborados ON elaborados.id = entries.elaborado_por INNER JOIN debes ON debes.id = entries.debe INNER JOIN habers ON habers.id = entries.haber WHERE entries.id_cost ="'.$request->id.'" ORDER BY entries.no_recibo ASC');
        echo json_encode($all);
    }

    public function show($id){
        $i = Entry::where('no_recibo',$id)->count();
        if($i > 0){
            $item = Entry::where('no_recibo',$id)->first();
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
            
            return view('viewStudent.abonos.show')->with('content', json_encode($struct));
        }else{
            return redirect()->route('abonos');
        }
    }

    public function store(EntryRequest $request){
        $con = consecutive::where('type','entry')->first();
        // Normalizar forma de pago: Consignación -> Bancos
        $forma = $request->forma ?? 'Efectivo';
        if ($forma === 'Consignación') {
            $forma = 'Bancos';
        }
        
        if($request->concepto == 1){
            $is_Entry = Entry::create([
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
        }
        if($request->concepto == 2){
            $is_Entry = Entry::create([
                'id_cost' => $request->id_cost,
                'concepto' => $request->concepto,
                'descripcion' => $request->descripcion,
                'fecha_recibo' => $request->fecha_recibo,
                'valor' => str_replace(".","",$request->valor),
                'elaborado_por' => $request->elaborado_por,
                'debe' => $request->debe,
                'haber' => $request->haber,
                'forma' => $forma
            ]);
        }
        
        if($request->concepto != 2 && $request->no_recibo != "" && $request->no_recibo >= $con->num_start){
            $new_current = intval($request->no_recibo)+1;
            $modificacion = DB::connection('mysql')->select('UPDATE consecutives SET num_current = "'.$new_current.'" WHERE id = "1"');
            //table_change::create(['table' => 'consecutives','id_change' => '1', 'add' => 0,'edit' => 1, 'delete' => 0]);
            TableChangeController::StoreEdit('consecutives',1);
        }
        if($is_Entry){
            //table_change::create(['table' => 'entries','id_change' => $is_Entry->id, 'add' => 1,'edit' => 0, 'delete' => 0]);
            TableChangeController::StoreAdd('entries',$is_Entry->id);
            $arrayCost = DB::table('costs')->where('id',$request->id_cost)->first();
            $rowsPurses = DB::table('purses')->where('id_cost',$request->id_cost)->count();
            if($rowsPurses == 0){
                $fechaActual = explode("-",$arrayCost->fecha_pago);
                $Mes = $fechaActual[1];
                $nameMes = DateController::getMes($Mes);
                $Año = $fechaActual[0]; 
                for ($i=0; $i < $arrayCost->numero_cuotas ; $i++) { 
                    if($i > 0){
                        $Mes = DateController::nextMes($Mes,true);
                        $Año = DateController::Is_nextYear($Año,$Mes);
                        $nameMes = DateController::getMes($Mes);
                    }
                    if($Mes < 10 && strlen($Mes) == 1){
                        $Mes = "0".$Mes;
                    }
                    
                    // Validar y ajustar la fecha para evitar días inválidos (ej: 30 de febrero)
                    $fechaPago = self::validateAndAdjustDate((int)$Año, (int)$Mes, (int)$fechaActual[2]);
                    
                    $obj = Purse::create([
                        'id_cost' => $request->id_cost,
                        'fecha_pago' => $fechaPago,
                        'estado' => 'Pendiente',
                        'cuota' => $arrayCost->valor_cuotas,
                        'abonado' => 0,
                        'comentario' => 'Fecha de pago establecidas con sus cuotas iniciales.'
                    ]);
                    if($obj){
                        //table_change::create(['table' => 'purses','id_change' => $obj->id, 'add' => 1,'edit' => 0, 'delete' => 0]);
                        TableChangeController::StoreAdd('purses',$obj->id);
                        $obj1 = historyPurse::create([
                            'id_purse' => $obj->id,
                            'fecha_pago'=> $obj->fecha_pago,
                            'estado'=> $obj->estado,
                            'cuota'=> $obj->cuota,
                            'abonado'=> $obj->abonado,
                            'comentario'=> $obj->comentario
                        ]);
                        if($obj1){
                            TableChangeController::StoreAdd('history_purses',$obj1->id);
                            //table_change::create(['table' => 'history_purses','id_change' => $obj1->id, 'add' => 1,'edit' => 0, 'delete' => 0]);
                        }
                    }
                    
                }
            }
        }
        
        // Si es una petición AJAX, retornar JSON
        if($request->ajax() || $request->wantsJson()) {
            return response()->json(['success' => true, 'message' => 'Abono guardado correctamente']);
        }
        
        // Si no es AJAX, redirigir de vuelta o mostrar mensaje
        // Obtener el id_cost para redirigir
        $arrayCost = DB::table('costs')->where('id',$request->id_cost)->first();
        if($arrayCost && $arrayCost->cod_alumno) {
            // Redirigir a la página del estudiante para refrescar
            return redirect()->back()->with('success', 'Abono guardado correctamente');
        }
        
        return response()->json(['success' => true, 'message' => 'OK']);
    }

    public function get($id){
        $entry = Entry::where('id',$id)->first();
        if(!$entry){
            return response()->json(['error' => 'Entry not found'], 404);
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
            $entry = Entry::where('id',$id)->firstOrFail();
            
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
            if($request->has('no_recibo') && $request->no_recibo){
                $entry->no_recibo = $request->no_recibo;
            }
            $entry->elaborado_por = $request->elaborado_por;
            $entry->debe = $request->debe;
            $entry->haber = $request->haber;
            $entry->forma = $forma;
            $entry->save();
            
            TableChangeController::StoreEdit('entries',$entry->id);
            $con = consecutive::where('type','entry')->first();
            if($request->has('no_recibo') && $request->no_recibo == $con->num_current){
                $new_current = intval($request->no_recibo)+1;
                $modificacion = DB::connection('mysql')->select('UPDATE consecutives SET num_current = "'.$new_current.'" WHERE id = "1"');
                TableChangeController::StoreEdit('consecutives',1);
            }
            
            echo 'OK';
        } catch (\Exception $e) {
            \Log::error('Error al actualizar entry: ' . $e->getMessage());
            return response()->json(['error' => $e->getMessage()], 500);
        }
    }

    public function destroy($id){
        // Solo permitir eliminación a admin y super-admin
        if (!auth()->check() || (!auth()->user()->hasRole('admin') && !auth()->user()->hasRole('super-admin'))) {
            return response()->json(['error' => 'No tienes permisos para eliminar abonos.'], 403);
        }
        
        try {
            $entry = Entry::where('id',$id)->first();
            if (!$entry) {
                return response()->json(['error' => 'Abono no encontrado.'], 404);
            }
            $entry->delete();
            TableChangeController::StoreDelete('entries',$entry->id);
            return view('viewStudent.close');
        } catch (\Exception $e) {
            return response()->json(['error' => 'Error al eliminar el abono: ' . $e->getMessage()], 500);
        }
    }


    public function print($id){
        $entry = Entry::where('id',$id)->firstOrFail();
        $concepto =  DB::connection('mysql')->select('SELECT * FROM conceptos WHERE id = "'.$entry->concepto.'"');
        $sqlCodAlumno = DB::connection('mysql')->select('SELECT costs.cod_alumno FROM `entries` INNER JOIN costs ON costs.id = entries.id_cost WHERE entries.id = "'.$id.'"');
        
        // Obtener datos del estudiante con manejo de errores mejorado
        $estudianteCedula = null;
        $estudianteNombre = null;
        $programaNombre = null;
        
        if (!empty($sqlCodAlumno) && isset($sqlCodAlumno[0]->cod_alumno)) {
            try {
                // Intentar obtener desde mysql2 con programa
                $sqlAlumno = DB::connection('mysql2')->select(
                    'SELECT alumno.nombre, alumno.cedula, programa.nombre_programa 
                     FROM alumno 
                     INNER JOIN relacion_programa_estudiante ON relacion_programa_estudiante.Alumno_cod = alumno.cod_alumno 
                     INNER JOIN programa ON programa.cod_programa = relacion_programa_estudiante.programa_cod 
                     WHERE alumno.cod_alumno = "'.$sqlCodAlumno[0]->cod_alumno.'"'
                );
                
                if (!empty($sqlAlumno)) {
                    $estudianteCedula = $sqlAlumno[0]->cedula ?? null;
                    $estudianteNombre = $sqlAlumno[0]->nombre ?? null;
                    $programaNombre = $sqlAlumno[0]->nombre_programa ?? null;
                } else {
                    // Fallback: buscar sin programa
                    $sqlAlumnoSinPrograma = DB::connection('mysql2')->select(
                        'SELECT alumno.nombre, alumno.cedula 
                         FROM alumno 
                         WHERE alumno.cod_alumno = "'.$sqlCodAlumno[0]->cod_alumno.'"'
                    );
                    if (!empty($sqlAlumnoSinPrograma)) {
                        $estudianteCedula = $sqlAlumnoSinPrograma[0]->cedula ?? null;
                        $estudianteNombre = $sqlAlumnoSinPrograma[0]->nombre ?? null;
                    }
                    
                    // Fallback: buscar en tabla matriculas
                    if (!$estudianteCedula || !$estudianteNombre) {
                        $matricula = \App\Models\Matricula::where('cod_alumno', $sqlCodAlumno[0]->cod_alumno)->first();
                        if ($matricula) {
                            $estudianteCedula = $matricula->numero_documento ?? null;
                            $estudianteNombre = $matricula->nombre_completo ?? null;
                            $programaNombre = $matricula->programa ?? null;
                        }
                    }
                }
            } catch (\Exception $e) {
                \Log::error('Error al obtener datos del estudiante en EntryController::print: ' . $e->getMessage());
                // Fallback: buscar en tabla matriculas
                try {
                    $matricula = \App\Models\Matricula::where('cod_alumno', $sqlCodAlumno[0]->cod_alumno)->first();
                    if ($matricula) {
                        $estudianteCedula = $matricula->numero_documento ?? null;
                        $estudianteNombre = $matricula->nombre_completo ?? null;
                        $programaNombre = $matricula->programa ?? null;
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
        
        return view('prints.entry-pos', [
            'consecutivo' => $entry->no_recibo ?? $entry->id, // Usar no_recibo como consecutivo
            'estudiante_cedula' => $estudianteCedula,
            'estudiante_nombre' => $estudianteNombre,
            'programa' => $programaNombre,
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
            
            // Obtener datos para abonos (sin Livewire)
            $cost = DB::table('costs')->where('id', $id)->get();
            $entries = DB::connection('mysql')->select(
                'SELECT entries.id, entries.id_cost, conceptos.nombre AS concepto, entries.descripcion, entries.no_recibo, entries.fecha_recibo, entries.valor, elaborados.nombre AS elaborado_por, CONCAT(debes.cuenta, " - ", debes.nombre) AS debe, CONCAT(habers.cuenta, " - ", habers.nombre) AS haber, entries.created_at 
                 FROM entries 
                 INNER JOIN conceptos ON conceptos.id = entries.concepto 
                 INNER JOIN elaborados ON elaborados.id = entries.elaborado_por 
                 INNER JOIN debes ON debes.id = entries.debe 
                 INNER JOIN habers ON habers.id = entries.haber 
                 WHERE entries.id_cost = ? 
                 ORDER BY entries.no_recibo ASC',
                [$id]
            );
            
            // Obtener configuración de la institución
            $institucion = InstitutionSetting::getSettings();
            
            // Generar el PDF
        $dompdf = new Dompdf();
            $html = view('PDFs.pdf_abonos', [
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
            return $dompdf->stream('informe-abonos-' . $nombreEstudiante . '.pdf', [
                'Attachment' => true
            ]);

        } catch (\Exception $e) {
            \Log::error('Error al generar PDF de abonos: ' . $e->getMessage());
            \Log::error('Stack trace: ' . $e->getTraceAsString());
            abort(500, 'Error al generar el PDF: ' . $e->getMessage());
        }
    }

    public function ViewPdfUnitedOther($id){
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
            
            // Obtener datos para abonos unidos (sin Livewire)
            $cost = DB::table('costs')->where('id', $id)->get();
            $entries = DB::connection('mysql')->select(
                'SELECT entries.id, entries.id_cost, conceptos.nombre AS concepto, entries.descripcion, entries.no_recibo, entries.fecha_recibo, entries.valor, elaborados.nombre AS elaborado_por, CONCAT(debes.cuenta, " - ", debes.nombre) AS debe, CONCAT(habers.cuenta, " - ", habers.nombre) AS haber, entries.created_at 
                 FROM entries 
                 INNER JOIN conceptos ON conceptos.id = entries.concepto 
                 INNER JOIN elaborados ON elaborados.id = entries.elaborado_por 
                 INNER JOIN debes ON debes.id = entries.debe 
                 INNER JOIN habers ON habers.id = entries.haber 
                 WHERE entries.id_cost = ? 
                 ORDER BY entries.no_recibo ASC',
                [$id]
            );
            $others = DB::connection('mysql')->select(
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
            $html = view('PDFs.pdf_abonosUother', [
                'id_cost' => $id,
                'student' => $data,
                'cost' => $cost,
                'entries' => $entries,
                'others' => $others,
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
            return $dompdf->stream('informe-abonosYotros-' . $nombreEstudiante . '.pdf', [
                'Attachment' => true
            ]);

        } catch (\Exception $e) {
            \Log::error('Error al generar PDF de abonos unidos: ' . $e->getMessage());
            \Log::error('Stack trace: ' . $e->getTraceAsString());
            abort(500, 'Error al generar el PDF: ' . $e->getMessage());
        }
    }      

    
}
