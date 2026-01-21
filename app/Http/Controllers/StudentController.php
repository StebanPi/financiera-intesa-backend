<?php

namespace App\Http\Controllers;
use Illuminate\Support\Facades\DB;
use App\Models\Cost;
use Illuminate\Http\Request;

class StudentController extends Controller
{
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
        //
    }

    /**
     * Display the specified resource.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function search($name)
    {
        //$list = thirdEntry::where('nombre', 'like', '%'.$id.'%')->get();
        $Sql = "SELECT cod_alumno, nombre FROM alumno WHERE nombre LIKE '%".$name."%'";
        $Student = DB::connection('mysql2')->select($Sql);
        $array = [];
        for ($i=0; $i < count($Student); $i++) { 
            $cod = $Student[$i]->cod_alumno; 
            $infoCost = Cost::where('cod_alumno',$cod)->first();
            $infoCount = Cost::where('cod_alumno',$cod)->count();
            if($infoCount > 0){
                $alumno = [
                    'cod_alumno' => $Student[$i]->cod_alumno,
                    'nombre' => $Student[$i]->nombre,
                    'id_cost' => $infoCost->id
                ];
                array_push($array,$alumno);
            }
        }
        return json_encode($array);
    }

    public function searchAll($name){
        $Sql = "SELECT cod_alumno, nombre FROM alumno WHERE nombre LIKE '%".$name."%'";
        $Student = DB::connection('mysql2')->select($Sql);
        echo json_encode($Student);
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
}
