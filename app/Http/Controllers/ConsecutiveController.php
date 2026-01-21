<?php

namespace App\Http\Controllers;
use App\Models\consecutive;
use App\Models\table_change;
use App\Http\Requests\ConsecutiveRequest;
use App\Http\Controllers\TableChangeController;
use Illuminate\Http\Request;

class ConsecutiveController extends Controller
{
    //

    public function index()
    {
        //
        $entry = consecutive::where('type','entry')->first();
        $discharge = consecutive::where('type','discharge')->first();
        if(empty($entry) == true){
            $entry = [ 'num_start' => '', 'num_current' => ''];
        }
        if(empty($discharge) == true){
            $discharge = [ 'num_start' => '', 'num_current' => ''];
        }
        return view('consecutives.index',['entry' => $entry, 'discharge' => $discharge ]);
    }

    public function store(ConsecutiveRequest $request)
    {
        // Verificar que el usuario sea admin o super-admin
        $user = auth()->user();
        if (!$user->hasRole('admin') && !$user->hasRole('super-admin')) {
            return redirect()->route('consecutive.index')
                ->with('error', 'No tienes permisos para modificar los consecutivos. Solo los administradores pueden realizar esta acción.');
        }

        //
        $con = consecutive::where('type',$request->type)->first();
        if(empty($con) == true){
            $con1 = consecutive::create(['type' => $request->type , 'num_start' => $request->num_start, 'num_current' => $request->num_start]);
            //table_change::create(['table' => 'consecutives','id_change' => $con1->id, 'add' => 1,'edit' => 0, 'delete' => 0]);
            TableChangeController::StoreAdd('consecutives',$con1->id);
        }else{
            $con->num_start = $request->num_start;
            $con->num_current = $request->num_start;
            $con->save();
            //table_change::create(['table' => 'consecutives','id_change' => $con->id, 'add' => 0,'edit' => 1, 'delete' => 0]);
            TableChangeController::StoreEdit('consecutives',$con->id);
        }
        return redirect()->route('consecutive.index')->with('success','Guardado Correctamente');
    }
}
