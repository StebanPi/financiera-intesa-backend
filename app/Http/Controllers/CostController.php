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
    public function store(Request $request)
    {
        $this->construct();
        $cod_alumno = $request->cod_alumno;
        $semestresData = $request->input('semestres', []);

        // Si no vienen semestres como array, intentar procesar como el formato anterior (un solo semestre)
        if (empty($semestresData)) {
            $semestresData = [
                [
                    'numero_semestre' => $request->numero_semestre,
                    'valor_semestre' => $request->valor_semestre,
                    'valor_total_semestre' => $request->valor_total_semestre,
                    'descuento' => $request->descuento,
                    'valor_neto' => $request->valor_neto,
                    'saldo_financiar' => $request->saldo_financiar,
                    'periodo' => $request->periodo,
                    'numero_cuotas' => $request->numero_cuotas,
                    'valor_cuotas' => $request->valor_cuotas,
                    'fecha_pago' => $request->fecha_pago,
                    'detalles' => $request->detalles
                ]
            ];
        }

        $semestresRecibidos = [];
        foreach ($semestresData as $data) {
            $numSemestre = $data['numero_semestre'];
            $semestresRecibidos[] = $numSemestre;
            
            // Limpiar valores (quitar puntos)
            $data['valor_semestre'] = str_replace(['.', ','], '', $data['valor_semestre'] ?? '0');
            $data['valor_total_semestre'] = str_replace(['.', ','], '', $data['valor_total_semestre'] ?? '0');
            $data['descuento'] = str_replace(['.', ','], '', $data['descuento'] ?? '0');
            $data['valor_neto'] = str_replace(['.', ','], '', $data['valor_neto'] ?? '0');
            $data['saldo_financiar'] = str_replace(['.', ','], '', $data['saldo_financiar'] ?? '0');
            $data['valor_cuotas'] = str_replace(['.', ','], '', $data['valor_cuotas'] ?? '0');
            $data['cod_alumno'] = $cod_alumno;

            // Validar que fecha_pago esté presente y no sea null
            if (empty($data['fecha_pago']) || is_null($data['fecha_pago'])) {
                return redirect()->back()
                    ->withInput()
                    ->withErrors(['fecha_pago' => 'La fecha de pago es requerida para el semestre ' . $numSemestre]);
            }

            // Asegurar que fecha_pago sea una fecha válida
            if (!strtotime($data['fecha_pago'])) {
                return redirect()->back()
                    ->withInput()
                    ->withErrors(['fecha_pago' => 'La fecha de pago no es válida para el semestre ' . $numSemestre]);
            }

            $Cost = Cost::where('cod_alumno', $cod_alumno)->where('numero_semestre', $numSemestre)->first();

            if (!$Cost) {
                $Cost = Cost::create($data);
                TableChangeController::StoreAdd('costs', $Cost->id);
                $this->regeneratePurses($Cost);
            } else {
                $Cost->update($data);
                TableChangeController::StoreEdit('costs', $Cost->id);
                // Siempre regenerar las cuotas para asegurar que estén actualizadas
                $this->regeneratePurses($Cost);
            }
        }

        // Eliminar semestres que ya no están en la lista y todas sus relaciones
        $costsAEliminar = Cost::where('cod_alumno', $cod_alumno)
            ->whereNotIn('numero_semestre', $semestresRecibidos)
            ->get();
        
        foreach ($costsAEliminar as $costEliminar) {
            // Eliminar history_purses asociados
            $purseIds = Purse::where('id_cost', $costEliminar->id)->pluck('id');
            if ($purseIds->isNotEmpty()) {
                historyPurse::whereIn('id_purse', $purseIds)->delete();
            }
            // Eliminar purses asociados
            Purse::where('id_cost', $costEliminar->id)->delete();
            // Eliminar entries asociados
            Entry::where('id_cost', $costEliminar->id)->delete();
            // Eliminar other_entries asociados
            OtherEntry::where('id_cost', $costEliminar->id)->delete();
            // Eliminar el cost
            $costEliminar->delete();
        }

        $message = "Configuración de costos guardada correctamente";
        // Determinar a dónde redirigir según el origen
        if($request->has('redirect_to') && $request->redirect_to == 'matricula'){
            return redirect()->route('matricula.estudiante', $request->cod_alumno)->with('success', $message);
        }
        
        return redirect()->route('cost.show',$request->cod_alumno)->with('success',$message);
    }

    /**
     * Regenera las cuotas (purses) para un registro de costo
     */
    private function regeneratePurses($Cost)
    {
        // Eliminar existentes
        $pursesToDelete = Purse::where('id_cost', $Cost->id)->get();
        if($pursesToDelete->count() > 0) {
            historyPurse::whereIn('id_purse', $pursesToDelete->pluck('id'))->delete();
        }
        Purse::where('id_cost', $Cost->id)->delete();

        // Crear nuevas cuotas
        $fechaActual = explode("-", $Cost->fecha_pago);
        $Mes = $fechaActual[1];
        $Año = $fechaActual[0];
        $Dia = $fechaActual[2] ?? 1;

        for ($i=0; $i < $Cost->numero_cuotas; $i++) {
            if($i > 0){
                $Mes = DateController::nextMes($Mes,true);
                $Año = DateController::Is_nextYear($Año,$Mes);
            }
            if($Mes < 10 && strlen($Mes) == 1){
                $Mes = "0".$Mes;
            }

            $fechaPago = $this->validateAndAdjustDate((int)$Año, (int)$Mes, (int)$Dia);

            Purse::create([
                'id_cost' => $Cost->id,
                'fecha_pago' => $fechaPago,
                'estado' => 'Pendiente',
                'cuota' => $Cost->valor_cuotas,
                'abonado' => 0,
                'comentario' => 'Cuota generada automáticamente para el semestre ' . $Cost->numero_semestre
            ]);
        }
    }

    /**
     * Actualiza o regenera cuotas según si cambió el número de cuotas
     */
    private function updateOrRegeneratePurses($Cost)
    {
        $numActual = Purse::where('id_cost', $Cost->id)->count();
        if ($numActual == $Cost->numero_cuotas) {
            $purses = Purse::where('id_cost', $Cost->id)->orderBy('fecha_pago', 'asc')->get();
            $fechaBase = explode("-", $Cost->fecha_pago);
            $Mes = $fechaBase[1];
            $Año = $fechaBase[0];
            $Dia = $fechaBase[2] ?? 1;

            foreach ($purses as $k => $item) {
                $item->cuota = $Cost->valor_cuotas;
                if ($k == 0) {
                    $item->fecha_pago = $Cost->fecha_pago;
                } else {
                    for($j=0; $j<$k; $j++) {
                        $Mes = DateController::nextMes($Mes,true);
                        $Año = DateController::Is_nextYear($Año,$Mes);
                    }
                    $item->fecha_pago = $this->validateAndAdjustDate((int)$Año, (int)$Mes, (int)$Dia);
                    $Mes = $fechaBase[1];
                    $Año = $fechaBase[0];
                }
                $item->save();
            }
        } else {
            $this->regeneratePurses($Cost);
        }
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
