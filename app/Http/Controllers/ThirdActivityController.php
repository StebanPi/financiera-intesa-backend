<?php

namespace App\Http\Controllers;
use App\Models\thirdActivity;
use Illuminate\Http\Request;

class ThirdActivityController extends Controller
{
    public function list(){
       $listActivity = thirdActivity::orderBy('created_at', 'desc')->get();
       return json_encode($listActivity);
    }

    public function store(Request $request){
        $item = thirdActivity::create($request->all());
        return redirect()->route('third.entry')->with('success','Actividad agregada Correctamente');
    }

    public function update(Request $request, $id){
        $item = thirdActivity::where('id', $id)->first();
        $item->nombre = $request->nombre;
        $item->save();
        return redirect()->route('third.entry')->with('success','Actividad editada Correctamente');
    }

    public function destroy($id){
        try {
            $item = thirdActivity::where('id', $id)->first();
            if ($item) {
                // Deshabilitar verificaciones de claves foráneas temporalmente
                \Illuminate\Support\Facades\DB::statement('SET FOREIGN_KEY_CHECKS=0;');
                
                // Actualizar todos los terceros que usan esta actividad a NULL o a otra actividad
                $otraActividad = thirdActivity::where('id', '!=', $id)->first();
                if ($otraActividad) {
                    \App\Models\thirdEntry::where('actividad', $id)->update(['actividad' => $otraActividad->id]);
                } else {
                    // Si no hay otra actividad, establecer a NULL (requiere modificar la migración o usar un valor por defecto)
                    // Por ahora, eliminamos los terceros que usan esta actividad
                    \App\Models\thirdEntry::where('actividad', $id)->delete();
                }
                
                // Eliminar la actividad
                $item->delete();
                
                // Rehabilitar verificaciones de claves foráneas
                \Illuminate\Support\Facades\DB::statement('SET FOREIGN_KEY_CHECKS=1;');
                
                return redirect()->route('third.entry')->with('success','Actividad eliminada Correctamente');
            }
            return redirect()->route('third.entry')->with('error','Actividad no encontrada');
        } catch (\Exception $e) {
            // Asegurarse de rehabilitar las claves foráneas en caso de error
            try {
                \Illuminate\Support\Facades\DB::statement('SET FOREIGN_KEY_CHECKS=1;');
            } catch (\Exception $e2) {
                // Ignorar errores al rehabilitar
            }
            return redirect()->route('third.entry')->with('error','Error al eliminar la actividad: ' . $e->getMessage());
        }
    }
}
