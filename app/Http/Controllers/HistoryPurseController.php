<?php

namespace App\Http\Controllers;
use App\Models\historyPurse;
use App\Models\Purse;
use App\Models\PasswordPrivileges;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use App\Models\table_change;
use Illuminate\Support\Str;
use App\Http\Controllers\TableChangeController;
use function PHPUnit\Framework\isEmpty;

class HistoryPurseController extends Controller
{
    //

    public function search(Request $request){
        $array = [];
        $id = $request->id_cost;
        $historyCount = DB::connection('mysql')->select("SELECT NuevaTabla.id_purse, count(*) AS total_duplicados FROM (SELECT history_purses.id, history_purses.id_purse, history_purses.fecha_pago, history_purses.estado, history_purses.cuota,history_purses.abonado, history_purses.comentario FROM purses INNER JOIN history_purses ON history_purses.id_purse = purses.id where purses.id_cost = '".$request->id_cost."') AS NuevaTabla GROUP BY NuevaTabla.id_purse HAVING COUNT(*)>1");
        if(!empty($historyCount)){
            foreach ($historyCount as $item) {
                $history = DB::connection('mysql')->select('SELECT * FROM history_purses WHERE id_purse = "'.$item->id_purse.'"');
                array_push($array,$history);
            }
            
        }

        echo json_encode($array);

    }

    public function delete(Request $request){
        $content = DB::table('password_privileges')->where('id','1')->first();
        if($content->password == $request->password){
            $first = historyPurse::where('id',$request->id)->first();
            $first->delete();
            TableChangeController::StoreDelete('history_purses',$first->id);
            $searchHistory = historyPurse::where('id_purse',$first->id_purse)->orderBy('id', 'desc')->first();
            $Purse = Purse::where('id',$first->id_purse)->first();
            $Purse->fecha_pago = $searchHistory->fecha_pago;
            $Purse->estado = $searchHistory->estado;
            $Purse->cuota = $searchHistory->cuota;
            $Purse->abonado = $searchHistory->abonado;
            $Purse->comentario = $searchHistory->comentario;
            $Purse->save();
            TableChangeController::StoreEdit('history_purses',$Purse->id);
            echo json_encode('ok');
        }else{
            echo 'false';
        }
    }
}
