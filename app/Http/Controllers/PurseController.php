<?php

namespace App\Http\Controllers;
use App\Http\Controllers\MoneyController;
use App\Http\Controllers\DateController;
use Illuminate\Http\Request;
use App\Models\Purse;
use Illuminate\Support\Str;
use App\Models\historyPurse;
use Illuminate\Support\Facades\DB;
use App\Models\table_change;
use App\Http\Controllers\TableChangeController;
use App\Models\InstitutionSetting;
use Dompdf\Dompdf;

class PurseController extends Controller
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
    
    //
    public static function getPurse($id_cost){
        $Purses = Purse::where('id_cost',$id_cost)->get();
        for ($i=0; $i < count($Purses); $i++) { 
            $Purses[$i] = MoneyController::datas($Purses[$i],['cuota','abonado']);
            $Purses[$i] = DateController::transformMonth($Purses[$i],['fecha_pago']);
        }
        return $Purses;
    } 

    public function edit(Request $request){

        //Modifico el purse
        $Purse = Purse::where('id',$request->id)->first();
        $Purse->fecha_pago = $request->fecha_pago;
        $Purse->cuota = Str::replace('.','',$request->cuota);
        $Purse->comentario = $request->comentario;
        $Purse->save();
        TableChangeController::StoreEdit('purses',$Purse->id);
        $new = historyPurse::create([
            'id_purse' => $Purse->id,
            'fecha_pago'=> $Purse->fecha_pago,
            'estado'=> $Purse->estado,
            'cuota'=> $Purse->cuota,
            'abonado'=> $Purse->abonado,
            'comentario'=> $Purse->comentario
        ]);
        if($new){
            TableChangeController::StoreAdd('history_purses',$new->id);
        }

        //Necesito modificar los demas purses

        if($request->ModifyInputLabel == "todos"){
            $ArrrayPurses =Purse::where([ ['id_cost',"=", $Purse->id_cost] , ['id',">", $Purse->id] ])->get();
            $fechaActuals = $Purse->fecha_pago;
            foreach ($ArrrayPurses as $item) {


                $fechaActual = explode("-",$fechaActuals);
                $Mes = $fechaActual[1];
                $nameMes = DateController::getMes($Mes);
                $Año = $fechaActual[0]; 
                $Año = DateController::Is_nextYear($Año,$Mes);
                $Mes = DateController::nextMes($Mes,true);
                $nameMes = DateController::getMes($Mes);
                if($Mes < 10 && strlen($Mes) == 1){
                    $Mes = "0".$Mes;
                }
                
                // Validar y ajustar la fecha para evitar días inválidos (ej: 30 de febrero)
                $fechaActuals = self::validateAndAdjustDate((int)$Año, (int)$Mes, (int)$fechaActual[2]);

                $item->fecha_pago = $fechaActuals;
                $item->cuota = Str::replace('.','',$request->cuota);
                $item->comentario = $request->comentario;
                $item->save();

                TableChangeController::StoreEdit('purses',$item->id);
                $new1 = historyPurse::create([
                    'id_purse' => $item->id,
                    'fecha_pago'=> $fechaActuals,
                    'estado'=> $item->estado,
                    'cuota'=> $item->cuota,
                    'abonado'=> $item->abonado,
                    'comentario'=> $item->comentario
                ]);

                if($new1){
                    TableChangeController::StoreAdd('history_purses',$new1->id);
                }
            }
        }
        
        // Retornar respuesta JSON para peticiones AJAX
        if($request->expectsJson() || $request->ajax()) {
            return response()->json(['success' => true, 'message' => 'Pago actualizado correctamente']);
        }
        
        return response('OK', 200);
    }

    public function total (Request $request){
        // Usar el servicio para obtener el total calculado
        $carteraData = \App\Services\CarteraService::calcularCartera($request->id);
        $total = $carteraData['totales']['total_abono'];
        
        echo json_encode([['total' => $total]]);
    }
    
    /**
     * Nuevo endpoint para obtener todos los totales calculados
     */
    public function totales(Request $request){
        try {
            $id_cost = $request->id ?? $request->input('id');
            $cod_alumno = $request->cod_alumno ?? $request->input('cod_alumno');
            
            if(empty($id_cost) && empty($cod_alumno)){
                return response()->json([
                    'error' => 'id_cost o cod_alumno es requerido'
                ], 400);
            }
            
            $ids_cost = [];
            if ($cod_alumno) {
                $ids_cost = DB::table('costs')->where('cod_alumno', $cod_alumno)->pluck('id')->toArray();
            } elseif ($id_cost) {
                $ids_cost = [$id_cost];
            }

            // Obtener datos raw de la base de datos para debug
            $entriesRaw = DB::connection('mysql')
                ->table('entries')
                ->whereIn('id_cost', $ids_cost)
                ->get(['id', 'valor', 'fecha_recibo', 'no_recibo']);
            
            $pursesRaw = DB::connection('mysql')
                ->table('purses')
                ->whereIn('id_cost', $ids_cost)
                ->orderBy('fecha_pago', 'asc')
                ->get(['id', 'fecha_pago', 'cuota', 'abonado', 'comentario']);
            
            $carteraData = \App\Services\CarteraService::calcularCartera($id_cost, $cod_alumno);
            
            return response()->json([
                'total_abono' => $carteraData['totales']['total_abono'],
                'cuotas_total' => $carteraData['totales']['cuotas_total'],
                'total_abonado' => $carteraData['totales']['total_abonado'],
                'saldo_pendiente' => $carteraData['totales']['saldo_pendiente'],
                'saldo_a_favor' => $carteraData['totales']['saldo_a_favor'],
                'saldo_en_mora' => $carteraData['totales']['saldo_en_mora'],
                // Datos raw para debug
                'debug' => [
                    'entries' => $entriesRaw->map(function($e) {
                        return [
                            'id' => $e->id,
                            'valor' => $e->valor,
                            'fecha_recibo' => $e->fecha_recibo,
                            'no_recibo' => $e->no_recibo
                        ];
                    })->toArray(),
                    'purses' => $pursesRaw->map(function($p) {
                        return [
                            'id' => $p->id,
                            'fecha_pago' => $p->fecha_pago,
                            'cuota' => $p->cuota,
                            'abonado' => $p->abonado ?? 0,
                            'comentario' => $p->comentario ?? ''
                        ];
                    })->toArray(),
                    'suma_entries' => $entriesRaw->sum('valor'),
                    'suma_total_abono' => $entriesRaw->sum('valor'),
                    'suma_cuotas' => $pursesRaw->sum('cuota'),
                    'nota' => 'other_entries no se incluyen en el cálculo de cartera (son otros ingresos separados)'
                ]
            ]);
        } catch(\Exception $e) {
            \Log::error('Error en PurseController::totales', [
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString()
            ]);
            
            return response()->json([
                'error' => 'Error al calcular totales: ' . $e->getMessage()
            ], 500);
        }
    }

    public function ViewPdf($id){
        try {
            // Obtener el costo
            $arrayCost = DB::table('costs')->where('id', $id)->first();
            
            if (!$arrayCost) {
                \Log::error("Costo no encontrado para ID: {$id}");
                abort(404, 'Costo no encontrado');
            }
            
            // Obtener datos del estudiante
            // En producción, mysql2 no tiene las tablas, así que buscamos primero en matriculas
            $data = [];
            if (isset($arrayCost->cod_alumno) && !empty($arrayCost->cod_alumno)) {
                try {
                    $codAlumno = strval($arrayCost->cod_alumno);

                    // PRIMERO: Buscar en la tabla local matriculas (siempre disponible)
                    $matricula = \App\Models\Matricula::where('cod_alumno', $codAlumno)->first();
                    if ($matricula) {
                        $data = [
                            (object)[
                                'cedula' => $matricula->numero_documento ?? '',
                                'nombre' => $matricula->nombre_completo ?? 'N/A',
                                'nombre_programa' => $matricula->programa ?? ''
                            ]
                        ];
                    } else {
                        // FALLBACK: Intentar en mysql2 (solo si está disponible)
                        try {
                            $Sql = 'SELECT alumno.cedula, alumno.nombre, programa.nombre_programa
                                    FROM alumno
                                    INNER JOIN relacion_programa_estudiante ON relacion_programa_estudiante.Alumno_cod = alumno.cod_alumno
                                    INNER JOIN programa ON programa.cod_programa = relacion_programa_estudiante.programa_cod
                                    WHERE alumno.cod_alumno = "'.$codAlumno.'"';
                            $Student = DB::connection('mysql2')->select($Sql);

                            if (!empty($Student) && isset($Student[0])) {
                                $data = $Student;
                            } else {
                                \Log::warning('No se encontró información del estudiante para cod_alumno: ' . $codAlumno);
                                $data = [];
                            }
                        } catch (\Exception $e2) {
                            // Si mysql2 falla (tabla no existe), solo loguear y continuar con array vacío
                            \Log::warning('No se pudo acceder a mysql2 para buscar estudiante: ' . $e2->getMessage());
                            $data = [];
                        }
                    }
                } catch (\Exception $e) {
                    \Log::error('Error al obtener datos del estudiante: ' . $e->getMessage());
                    \Log::error('Stack trace: ' . $e->getTraceAsString());
                    \Log::error('cod_alumno: ' . ($arrayCost->cod_alumno ?? 'NULL'));
                    $data = [];
                }
            } else {
                \Log::warning('cod_alumno no está definido o está vacío en arrayCost. ID cost: ' . $id);
            }
            
            // Obtener todos los costs del estudiante para mostrar todas las cuotas
            $codAlumno = $arrayCost->cod_alumno;
            $cost = DB::table('costs')->where('cod_alumno', $codAlumno)->orderBy('numero_semestre', 'asc')->get();
            
            // Usar el servicio para calcular cartera con todos los semestres del estudiante
            $carteraData = \App\Services\CarteraService::calcularCartera(null, $codAlumno);
            
            // Preparar datos para el PDF (mantener compatibilidad con la vista)
            $entries = [ (object)['TotalAbono' => $carteraData['totales']['total_abono']] ];
            $purses = [];
            foreach($carteraData['cuotas'] as $cuota) {
                $purses[] = (object)[
                    'id' => $cuota['id'],
                    'id_cost' => $cuota['id_cost'],
                    'numero_semestre' => $cuota['numero_semestre'] ?? 1,
                    'fecha_pago' => $cuota['fecha_pago'],
                    'cuota' => $cuota['cuota'],
                    'abonado' => $cuota['abonado'],
                    'estado_pago' => $cuota['estado_pago'],
                    'estado' => $cuota['estado'],
                    'is_vencida' => $cuota['is_vencida'],
                    'comentario' => $cuota['comentario'] ?? ''
                ];
            }
            
            // Obtener configuración de la institución
            $institucion = InstitutionSetting::getSettings();
            
            // Generar el PDF
        $dompdf = new Dompdf();
            $html = view('PDFs.pdf_cartera', [
                'id_cost' => $id,
                'student' => $data,
                'cost' => $cost,
                'entries' => $entries,
                'purses' => $purses,
                'totales' => $carteraData['totales'], // Pasar totales calculados
                'hoy' => $carteraData['hoy'], // Pasar fecha de hoy
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
            return $dompdf->stream('informe-cartera-' . $nombreEstudiante . '.pdf', [
                'Attachment' => true
            ]);
            
        } catch (\Exception $e) {
            \Log::error('Error al generar PDF de cartera: ' . $e->getMessage());
            \Log::error('Stack trace: ' . $e->getTraceAsString());
            abort(500, 'Error al generar el PDF: ' . $e->getMessage());
        }
    }
    
}
