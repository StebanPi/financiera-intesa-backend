<?php

namespace App\Http\Controllers;
use App\Http\Requests\CostRequest;
use App\Models\Cost;
use App\Models\Matricula;
use Illuminate\Support\Facades\DB;
use Illuminate\Http\Request;
use App\Models\consecutive;
use App\Models\table_change;
use App\Http\Controllers\TableChangeController;
use App\Http\Controllers\DateController;
use App\Models\Purse;
use App\Models\historyPurse;
use App\Models\Entry;
use App\Models\OtherEntry;
use App\Http\Controllers\MoneyController;

class CostController extends Controller
{

    private $tableChange;
    //
    public function construct()
    {
        $this->tableChange = new TableChangeController();
    }
    
    /**
     * Valida y ajusta una fecha para asegurar que sea válida
     * Si el día no existe en el mes, lo ajusta al último día válido del mes
     * 
     * @param int $year Año
     * @param int $month Mes (1-12)
     * @param int $day Día
     * @return string Fecha válida en formato Y-m-d
     */
    private function validateAndAdjustDate($year, $month, $day)
    {
        // Validar que la fecha sea válida
        if (checkdate($month, $day, $year)) {
            return sprintf('%04d-%02d-%02d', $year, $month, $day);
        }
        
        // Si no es válida, obtener el último día del mes
        $lastDay = date('t', mktime(0, 0, 0, $month, 1, $year));
        return sprintf('%04d-%02d-%02d', $year, $month, $lastDay);
    } 
    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function index()
    {
        //
    }

    /**
     * Show the form for creating a new resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function create()
    {
        //
    }

    /**
     * Store a newly created resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\Response
     */
    public function store(CostRequest $request)
    {
        $message = '';
        $Cost = Cost::where('cod_alumno',$request->cod_alumno)->first();
        $this->construct();
        if(empty($Cost) == true){
            $cost1 = Cost::create($request->all());
            $message = "Añadido Correctamente";
            TableChangeController::StoreAdd('costs',$cost1->id);

            $arrayCost = DB::table('costs')->where('id',$cost1->id)->first();
            $rowsPurses = DB::table('purses')->where('id_cost',$arrayCost->id)->count();
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
                    $fechaPago = $this->validateAndAdjustDate((int)$Año, (int)$Mes, (int)$fechaActual[2]);
                    
                    $obj = Purse::create([
                        'id_cost' => $arrayCost->id,
                        'fecha_pago' => $fechaPago,
                        'estado' => 'Pendiente',
                        'cuota' => $arrayCost->valor_cuotas,
                        'abonado' => 0,
                        'comentario' => 'Fecha de pago establecidas con sus cuotas iniciales.'
                    ]);
                    /*if($obj){
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
                    }*/
                    
                }
            }
            
        }else{
            $Cost->valor_semestre = $request->valor_semestre;
            $Cost->numero_semestre = $request->numero_semestre;
            $Cost->valor_total_semestre = $request->valor_total_semestre;
            $Cost->descuento = $request->descuento;
            $Cost->valor_neto = $request->valor_neto;
            $Cost->saldo_financiar = $request->saldo_financiar;
            $Cost->periodo = $request->periodo;
            $Cost->numero_cuotas = $request->numero_cuotas;
            $Cost->valor_cuotas = $request->valor_cuotas;
            $Cost->fecha_pago = $request->fecha_pago;
            $Cost->detalles = $request->detalles;
            $Cost->save();

            $numCost = Purse::where('id_cost', $Cost->id)->count();

            if($numCost == $Cost->numero_cuotas){
                $Costs = Purse::where('id_cost', $Cost->id)->get();
                $k = 0;
                foreach ($Costs as $item) {
          
                    $item->cuota = $Cost->valor_cuotas;
                    if($k == 0){
                        $item->fecha_pago = $Cost->fecha_pago;  
                    }else{
                        $fechaActual = explode("-",$Cost->fecha_pago);
                        $Mes = $fechaActual[1];
                        $nameMes = DateController::getMes($Mes);
                        $Año = $fechaActual[0]; 
                        $Mes = DateController::nextMes($Mes,true);
                        $Año = DateController::Is_nextYear($Año,$Mes);
                        $nameMes = DateController::getMes($Mes);

                        if($Mes < 10 && strlen($Mes) == 1){
                            $Mes = "0".$Mes;
                        }

                        // Validar y ajustar la fecha para evitar días inválidos (ej: 30 de febrero)
                        $item->fecha_pago = $this->validateAndAdjustDate((int)$Año, (int)$Mes, (int)$fechaActual[2]);
                    }
                    $item->save();
                    $k++;
                }
            }else{
                // Obtener todos los purses que se van a eliminar
                $pursesToDelete = Purse::where('id_cost', $Cost->id)->get();
                
                // Eliminar primero los history_purses relacionados para evitar violación de clave foránea
                if($pursesToDelete->count() > 0) {
                    $purseIds = $pursesToDelete->pluck('id')->toArray();
                    historyPurse::whereIn('id_purse', $purseIds)->delete();
                }
                
                // Ahora eliminar los purses
                Purse::where('id_cost', $Cost->id)->delete();
                
                // Crear las nuevas cuotas
                $fechaActual = explode("-",$Cost->fecha_pago);
                $Mes = $fechaActual[1];
                $nameMes = DateController::getMes($Mes);
                $Año = $fechaActual[0]; 
                for ($i=0; $i < $Cost->numero_cuotas ; $i++) { 
                    if($i > 0){
                        $Mes = DateController::nextMes($Mes,true);
                        $Año = DateController::Is_nextYear($Año,$Mes);
                        $nameMes = DateController::getMes($Mes);
                    }
                    if($Mes < 10 && strlen($Mes) == 1){
                        $Mes = "0".$Mes;
                    }
                    
                    // Validar y ajustar la fecha para evitar días inválidos (ej: 30 de febrero)
                    $fechaPago = $this->validateAndAdjustDate((int)$Año, (int)$Mes, (int)$fechaActual[2]);
                    
                    $obj = Purse::create([
                        'id_cost' => $Cost->id,
                        'fecha_pago' => $fechaPago,
                        'estado' => 'Pendiente',
                        'cuota' => $Cost->valor_cuotas,
                        'abonado' => 0,
                        'comentario' => 'Fecha de pago establecidas con sus cuotas iniciales.'
                    ]);
                }
            }


            $message = "Editado Correctamente";
            //table_change::create(['table' => 'costs','id_change' => $Cost->id, 'add' => 0,'edit' => 1, 'delete' => 0]);
            TableChangeController::StoreEdit('costs',$Cost->id);

      
            
        }
        // Determinar a dónde redirigir según el origen
        if($request->has('redirect_to') && $request->redirect_to == 'matricula'){
            return redirect()->route('matricula.estudiante', $request->cod_alumno)->with('success', $message);
        }
        
        return redirect()->route('cost.show',$request->cod_alumno)->with('success',$message);
    }

    /**
     * Display the specified resource.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function show($id)
    {
        //
        $num = Cost::where('cod_alumno',$id)->count();
        $array = "SELECT cod_alumno, nombre FROM alumno WHERE cod_alumno = '".$id."'";
        $Student = DB::connection('mysql2')->select($array);
        
        // Si no se encuentra en mysql2, buscar en la tabla matriculas
        if(empty($Student)){
            $matricula = Matricula::where('cod_alumno', $id)->first();
            if($matricula){
                $Student = [
                    (object)[
                        'cod_alumno' => $matricula->cod_alumno,
                        'nombre' => $matricula->nombre_completo
                    ]
                ];
            } else {
                // Si no se encuentra en ninguna tabla, crear un objeto vacío para evitar errores
                $Student = [
                    (object)[
                        'cod_alumno' => $id,
                        'nombre' => 'Estudiante no encontrado'
                    ]
                ];
            }
        }
        
        if($num > 0){
            $content = Cost::where('cod_alumno',$id)->first();
            $content = [
                "id" => $content->id,
                "cod_alumno" => $content->cod_alumno,
                "valor_semestre" => MoneyController::main($content->valor_semestre),
                "numero_semestre" => $content->numero_semestre,
                "valor_total_semestre" => MoneyController::main($content->valor_total_semestre),
                "descuento" => MoneyController::main($content->descuento),
                "valor_neto" => MoneyController::main($content->valor_neto),
                "saldo_financiar" => MoneyController::main($content->saldo_financiar),
                "periodo" => $content->periodo,
                "numero_cuotas" => $content->numero_cuotas,
                "valor_cuotas" => MoneyController::main($content->valor_cuotas),
                "fecha_pago" => $content->fecha_pago,
                "detalles" => $content->detalles
            ];
        }else{
            $content = [
                "id" => "",
                "cod_alumno" => $id,
                "valor_semestre" => "",
                "numero_semestre" => "",
                "valor_total_semestre" => "",
                "descuento" => "",
                "valor_neto" => "",
                "saldo_financiar" => "",
                "periodo" => "",
                "numero_cuotas" => "",
                "valor_cuotas" => "",
                "fecha_pago" => "",
                "detalles" => "",
                "created_at" => "",
                "updated_at" => ""
            ];
        }
        return view('viewStudent.financiera.show', ['content' => json_encode($content), 'alumno' =>  json_encode($Student)]);

    }

    /**
     * Show the form for editing the specified resource.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function edit($id)
    {
        //
    }

    /**
     * Update the specified resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function update(Request $request, $id)
    {
        //
    }

    /**
     * Remove the specified resource from storage.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function destroy($id)
    {
        //
    }

    /**
     * Eliminar todos los costos y relaciones de un estudiante específico
     * Solo accesible para superadmin
     *
     * @param  string  $cod_alumno
     * @return \Illuminate\Http\Response
     */
    public function eliminarCostosEstudiante($cod_alumno)
    {
        // Verificar que el usuario sea superadmin
        if (!auth()->check() || !auth()->user()->hasRole('super-admin')) {
            return response()->json([
                'success' => false,
                'message' => 'No tiene permisos para realizar esta acción.'
            ], 403);
        }

        try {
            DB::beginTransaction();

            $eliminados = [
                'history_purses' => 0,
                'purses' => 0,
                'entries' => 0,
                'other_entries' => 0,
                'costs' => 0
            ];

            // Buscar el cost del estudiante
            $cost = Cost::where('cod_alumno', $cod_alumno)->first();

            if ($cost) {
                $costId = $cost->id;

                // 1. Eliminar history_purses asociados a los purses del cost
                $purseIds = Purse::where('id_cost', $costId)->pluck('id')->toArray();
                if (!empty($purseIds)) {
                    $eliminados['history_purses'] = historyPurse::whereIn('id_purse', $purseIds)->count();
                    historyPurse::whereIn('id_purse', $purseIds)->delete();
                }

                // 2. Eliminar purses asociados al cost
                $eliminados['purses'] = Purse::where('id_cost', $costId)->count();
                Purse::where('id_cost', $costId)->delete();

                // 3. Eliminar entries asociados al cost
                $eliminados['entries'] = Entry::where('id_cost', $costId)->count();
                Entry::where('id_cost', $costId)->delete();

                // 4. Eliminar other_entries asociados al cost
                $eliminados['other_entries'] = OtherEntry::where('id_cost', $costId)->count();
                OtherEntry::where('id_cost', $costId)->delete();

                // 5. Eliminar el cost
                $eliminados['costs'] = 1;
                $cost->delete();
            }

            DB::commit();

            return response()->json([
                'success' => true,
                'message' => 'Los costos del estudiante han sido eliminados exitosamente.',
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
}
