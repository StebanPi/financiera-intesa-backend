<?php

namespace App\Http\Controllers;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use App\Http\Controllers\MoneyController;
use App\Http\Controllers\OtherEntryController;
use App\Http\Controllers\EntryController;
use App\Http\Controllers\PurseController;
use App\Models\consecutive;
use App\Models\concepto;
use App\Models\elaborado;
use App\Models\haber;
use App\Models\debe;
use App\Models\otrosConcepto;
use App\Models\Purse;

class viewStudentController extends Controller
{
    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function index(Request $request)
    {
        $Sql = 'SELECT alumno.cod_alumno ,alumno.nombre, alumno.cedula, programa.nombre_programa FROM alumno INNER JOIN relacion_programa_estudiante ON alumno.cod_alumno = relacion_programa_estudiante.Alumno_cod INNER JOIN programa ON relacion_programa_estudiante.programa_cod = programa.cod_programa ';
        if($request->estado > 0 && $request->estado <= 6){
            $Sql .= 'WHERE alumno.estado = "'.$request->estado.'";';
        }else{
            $Sql .= ';';
        }
        $Student = DB::connection('mysql2')->select($Sql);
        
        return view('viewStudent.index',['all' => $Student, 'estado' => $request->estado]);
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
        //
    }

    /**
     * Display the specified resource.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function show($id)
    {
        $Sql = 'SELECT alumno.cod_alumno ,alumno.nombre, alumno.foto, alumno.cedula, programa.nombre_programa, estado.estado FROM alumno INNER JOIN relacion_programa_estudiante ON alumno.cod_alumno = relacion_programa_estudiante.Alumno_cod INNER JOIN programa ON relacion_programa_estudiante.programa_cod = programa.cod_programa INNER JOIN estado ON estado.cod_estado = alumno.estado  WHERE cod_alumno = "'.$id.'"';
        $Student = DB::connection('mysql2')->select($Sql);
        $Cost = DB::table('costs')->where('cod_alumno',$id)->first();
        if(empty($Cost) == true){
            $Cost =  ["id"=> '',
            "cod_alumno"=> '',
            "valor_semestre" => '',
            "numero_semestre"=> '',
            "valor_total_semestre"=> '',
            "descuento"=> '',
            "valor_neto"=> '',
            "saldo_financiar"=> '',
            "periodo"=> '',
            "numero_cuotas"=> '',
            "valor_cuotas"=> '',
            'fecha_pago'=> '',
            "created_at"=> '',
            "updated_at"=> ''
            ];
            $obj = json_encode($Cost);
            $Cost = json_decode($obj);
        }else{
            $Cost = MoneyController::datas($Cost,['valor_semestre','valor_total_semestre','descuento','valor_neto','saldo_financiar','valor_cuotas']);
        }
        $con = consecutive::where('type','entry')->first();
        $Entries = EntryController::getEntry($id,true);
        $OtherEntries = OtherEntryController::getOtherEntry($id,true);

        if(empty($con) == true){
            return redirect()->route('view.student.index',7)->with('warning','No existe consecutivo disponible.');
        }else{
            $conceptos = concepto::all();
            $elaborado = elaborado::getUnique();
            $haber = haber::getUnique();
            $debe = debe::getUnique();
            $otrosConceptos = otrosConcepto::all();
            $sql_conseOcupados = 'SELECT entries.no_recibo FROM entries ORDER BY entries.no_recibo ASC';
            $ConsecutivosOcupados = DB::connection('mysql')->select($sql_conseOcupados);
            if($Cost->id != ""){
                $Purses = PurseController::getPurse($Cost->id);
            }else{
                $Purses = "";
            }
            return view('viewStudent.show',['Purses' => $Purses,'otrosConceptos' => $otrosConceptos,'OtherEntries' => $OtherEntries,'ConsecutivosOcupados' => $ConsecutivosOcupados,'student' => $Student, 'cost' => $Cost, 'con' => $con, 'entry' => $Entries, 'conceptos' => $conceptos,'elaborados'=> $elaborado, 'haber' =>$haber, 'debe' => $debe]);
        }
        
    }


    public function loginPrivilegies($no,$route){
        return view('viewStudent.privileges',['no_recibo' => $no,'route' => $route]);
    }

    public function privileges(Request $request){
        $request->validate([
            'password' => 'required'
        ]);
        $sqlConsult = DB::connection('mysql')->select('SELECT * FROM password_privileges WHERE password = "'.$request->password.'"');
        if(empty($sqlConsult) == true){
            return redirect()->route('login.privileges',$request->no_recibo,$request->route);
        }else{
            session(["permission" => "true"]);
            return redirect()->route($request->route,$request->no_recibo);
        }
    }

    public function viewAbonos($no) {

        $sql1 = 'SELECT costs.cod_alumno FROM entries INNER JOIN costs ON entries.id_cost = costs.id WHERE no_recibo = "'.$no.'"';
        $SearchStudent = DB::connection('mysql')->select($sql1);
        $sql2 = 'SELECT * FROM entries WHERE no_recibo = "'.$no.'"';
        $SearchStudent2 = DB::connection('mysql')->select($sql2);
        $id = $SearchStudent[0]->cod_alumno;
        $Sql = 'SELECT alumno.cod_alumno ,alumno.nombre, alumno.foto, alumno.cedula, programa.nombre_programa, estado.estado FROM alumno INNER JOIN relacion_programa_estudiante ON alumno.cod_alumno = relacion_programa_estudiante.Alumno_cod INNER JOIN programa ON relacion_programa_estudiante.programa_cod = programa.cod_programa INNER JOIN estado ON estado.cod_estado = alumno.estado  WHERE cod_alumno = "'.$id.'"';
        $Student = DB::connection('mysql2')->select($Sql);
        $Cost = DB::table('costs')->where('cod_alumno',$id)->first();
        if(empty($Cost) == true){
            $Cost =  ["id"=> '',"cod_alumno"=> '',"valor_semestre" => '',"numero_semestre"=> '',"valor_total_semestre"=> '',"descuento"=> '',"valor_neto"=> '',"saldo_financiar"=> '',"periodo"=> '',"numero_cuotas"=> '',"valor_cuotas"=> '',"created_at"=> '',"updated_at"=> ''];
            $obj = json_encode($Cost);
            $Cost = json_decode($obj);
        }else{
            $Cost = MoneyController::datas($Cost,['valor_semestre','valor_total_semestre','descuento','valor_neto','saldo_financiar','valor_cuotas']);
        }
        $con = consecutive::where('type','entry')->first();
        $Entries = DB::connection('mysql')->select('SELECT entries.id, entries.id_cost, entries.concepto, entries.descripcion, entries.no_recibo, entries.fecha_recibo, entries.valor, entries.elaborado_por, entries.debe ,entries.haber , entries.created_at FROM entries INNER JOIN costs ON costs.id = entries.id_cost WHERE costs.cod_alumno ="'.$id.'"');
        
        for ($i=0; $i < count($Entries); $i++) { 
            $Entries[$i] = MoneyController::datas($Entries[$i],['valor']);
        }
        if(session()->has('permission')){
            $conceptos = concepto::all();
            $elaborado = elaborado::getUnique();
            $haber = haber::getUnique();
            $debe = debe::getUnique();
            return view('viewStudent.edit',['no_recibo' => $no,'num_current' => $con->num_current , 'entry' => $SearchStudent2[0],'student' => $Student, 'cost' => $Cost, 'con' => $con, 'conceptos' => $conceptos,'elaborados'=> $elaborado, 'haber' =>$haber, 'debe' => $debe]);
        }else{
            return redirect()->route('login.privileges',$no,'show.admin');
        }
        
    }

    public function viewOtros($no) {
        if(session()->has('permission')){
            $SearchStudent = DB::connection('mysql')->select('SELECT costs.cod_alumno FROM other_entries INNER JOIN costs ON other_entries.id_cost = costs.id WHERE no_recibo = "'.$no.'"');
            $id = $SearchStudent[0]->cod_alumno;
            $otherEntry = DB::connection('mysql')->select('SELECT * FROM other_entries WHERE no_recibo = "'.$no.'"');
            $Student = DB::connection('mysql2')->select('SELECT alumno.cod_alumno ,alumno.nombre, alumno.foto, alumno.cedula, programa.nombre_programa, estado.estado FROM alumno INNER JOIN relacion_programa_estudiante ON alumno.cod_alumno = relacion_programa_estudiante.Alumno_cod INNER JOIN programa ON relacion_programa_estudiante.programa_cod = programa.cod_programa INNER JOIN estado ON estado.cod_estado = alumno.estado  WHERE cod_alumno = "'.$id.'"');
            $Cost = DB::table('costs')->where('cod_alumno',$id)->first();
            if(empty($Cost) == true){
                $Cost =  ["id"=> '',"cod_alumno"=> '',"valor_semestre" => '',"numero_semestre"=> '',"valor_total_semestre"=> '',"descuento"=> '',"valor_neto"=> '',"saldo_financiar"=> '',"periodo"=> '',"numero_cuotas"=> '',"valor_cuotas"=> '',"created_at"=> '',"updated_at"=> ''];
                $obj = json_encode($Cost);
                $Cost = json_decode($obj);
            }else{
                $Cost = MoneyController::datas($Cost,['valor_semestre','valor_total_semestre','descuento','valor_neto','saldo_financiar','valor_cuotas']);
            }
            $conceptos = otrosConcepto::all();
            $elaborado = elaborado::all();
            $haber = haber::all();
            $debe = debe::all();
            $con = consecutive::where('type','entry')->first();
            return view('viewStudent.editOtros',['no_recibo' => $no,'num_current' => $con->num_current , 'entry' => $otherEntry[0],'student' => $Student, 'cost' => $Cost, 'con' => $con, 'conceptos' => $conceptos,'elaborados'=> $elaborado, 'haber' =>$haber, 'debe' => $debe]);
        }else{
            return redirect()->route('login.privileges',$no,'otros.admin');
        }
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
    
    public function carteraTable($id)
    {
        $arrayCost = DB::table('costs')->where('cod_alumno',$id)->first();
        $data = DB::connection('mysql2')->SELECT('SELECT alumno.cedula , alumno.nombre, programa.nombre_programa FROM alumno INNER JOIN relacion_programa_estudiante ON relacion_programa_estudiante.Alumno_cod = alumno.cod_alumno INNER JOIN programa ON programa.cod_programa = relacion_programa_estudiante.programa_cod WHERE alumno.cod_alumno = "'.$arrayCost->cod_alumno.'"');
        $entries = DB::connection('mysql')->select('SELECT SUM(valor) AS TotalAbono FROM entries WHERE id_cost ="'.$arrayCost->id.'"');
        $purses = DB::connection('mysql')->select('SELECT * FROM purses WHERE id_cost = "'.$arrayCost->id.'"');
        return view('viewStudent.cartera.show',['id_cost' => $id, 'student' => $data, 'cost' => $arrayCost, 'entries' => $entries, 'purses' => $purses]);
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

    public function search(Request $request){
        $Cedula = "";
        if($request->cedula != ""){
            $Cedula = $request->cedula;
        }
        $Nombre = "";
        if($request->nombre != ""){
            $Nombre = $request->nombre;
        }
        // TODO: Implementar búsqueda
        return response()->json([]);
    }

    public function cartera(Request $request){
        $id_cost = $request->id;
        if(empty($id_cost)){
            echo json_encode([]);
            return;
        }
        
        // Usar el servicio para obtener los datos calculados
        $carteraData = \App\Services\CarteraService::calcularCartera($id_cost);
        
        // Formatear los datos para que sean compatibles con el JavaScript existente
        $pursesFormateados = [];
        foreach($carteraData['cuotas'] as $cuota){
            // Formatear fecha usando DateController
            $fechaFormateada = \App\Http\Controllers\DateController::getMesSubtr($cuota['fecha_pago']);
            
            $pursesFormateados[] = [
                'id' => $cuota['id'],
                'id_cost' => $id_cost,
                'fecha_pago' => $fechaFormateada,
                'estado' => $cuota['estado'],
                'estado_pago' => $cuota['estado_pago'],
                'is_vencida' => $cuota['is_vencida'],
                'cuota' => $cuota['cuota'], // Devolver como número sin formato
                'abonado' => $cuota['abonado'], // Devolver como número sin formato
                'comentario' => $cuota['comentario'] ?? '',
                'created_at' => '',
                'updated_at' => ''
            ];
        }
        
        return response()->json($pursesFormateados);
    }
}
