<?php

namespace App\Http\Controllers;
use Illuminate\Support\Facades\DB;
use Illuminate\Http\Request;
use App\Http\Controllers\GeoController;
use App\Http\Controllers\TableChangeController;
use App\Models\concepto;
use App\Models\consecutive;
use App\Models\Cost;
use App\Models\debe;
use App\Models\elaborado;
use App\Models\Entry;
use App\Models\haber;
use App\Models\historyPurse;
use App\Models\OtherEntry;
use App\Models\otrosConcepto;
use App\Models\PasswordPrivileges;
use App\Models\Purse;
use App\Models\User;

class SynchronizationController extends Controller
{
    //
    private $DBto;
    private $DBfrom, $DBfrom_NAME;
    private $Is_Local_Cloud;
    private $tables = [];

    public function count_local_cloud(){
        $this->DBto = DB::connection('mysql');
        $sql = $this->sql_table_changes(true);
        $count = $this->DBto->select($sql);
        echo json_encode($count);
    }

    public function transfer_local_cloud(){
        $this->DBto = DB::connection('mysql');
        $this->DBfrom = DB::connection('mysql3');
        $this->DBfrom_NAME = 'mysql3';
        $this->Is_Local_Cloud = true;
        $this->name_table();
        $geo = json_decode(GeoController::data());
        $this->comprobate();
        return redirect()->route('home')->with('success','Sincronizado correctamente con la Base de datos.');
    }

    public function transfer_cloud_local(){
        $this->DBto = DB::connection('mysql3');
        $this->DBfrom = DB::connection('mysql');
        $this->DBfrom_NAME = 'mysql';
        $this->Is_Local_Cloud = false;
        $this->name_table();
    }

    private function name_table(){
        $tables = $this->DBto->select('SELECT * FROM migrations');
        for ($i=0; $i < count($tables); $i++) {
            $subtr = substr(($tables[$i])->migration,18);
            $nombre = str_replace('table','',str_replace('create','',$subtr)); 

            if($nombre[0] == "_"){
                    $nombre = substr($nombre,1);
            }
            if($nombre[intval(strlen($nombre))-1] == "_"){
                    $nombre = substr($nombre,0,-1);
            }
            if($nombre != "_changes"){
                array_push($this->tables,$nombre);
            }
 
        }
    }

    private function sql_table_changes($isCount = false){
        date_default_timezone_set("America/Bogota");
        $fecha = explode("-",date('Y-m-d'));
        if($isCount){
            $sql = "SELECT COUNT(id) AS cantidad FROM table_changes WHERE YEAR(created_at) = '$fecha[0]' AND MONTH(created_at) = '$fecha[1]' AND DAY(created_at) = '$fecha[2]' AND is_synchronized = 0";
        }else{
            $sql = "SELECT * FROM table_changes WHERE YEAR(created_at) = '$fecha[0]' AND MONTH(created_at) = '$fecha[1]' AND DAY(created_at) = '$fecha[2]' AND is_synchronized = 0";
        }
        
        return $sql;
    }

    private function comprobate(){
        if($this->Is_Local_Cloud){
            $sql = $this->sql_table_changes();
            $changes = $this->DBto->SELECT($sql);
            foreach ($changes as $item) {
                $response = false;
                $itemSend = $this->DBto->SELECT("SELECT * FROM $item->table WHERE id = '$item->id_change'");
                $exist_id = $this->DBfrom->SELECT("SELECT * FROM $item->table WHERE id = '$item->id_change'");

                if(count($exist_id) > 0){
                    if($item->delete == "1"){
                        $response = $this->ModelToTable($item->table,$item->id_change,"",3);
                    }
                    if($item->edit == "1"){
                        $response = $this->ModelToTable($item->table,$item->id_change,$itemSend[0],1);
                    }
                }
                if($item->add == "1"){
                    $response = $this->ModelToTable($item->table,$item->id_change,$itemSend[0],2);
                }
                if($response){
                    $this->DBto->SELECT("UPDATE table_changes SET is_synchronized = '1'  WHERE id = '$item->id'");
                } 
                
            }
            
        }else{

        }
        
    }

    private function ModelToTable($table,$id,$item,$accion){
        switch ($table) {
            case 'conceptos':
                $obj = new concepto();
                $obj->construct($this->DBfrom_NAME);
                if($accion == 1){
                    $element = $obj->where('id',$id)->first();
                    $element->nombre = $item->nombre;
                    $element->estado = $item->estado;
                    $element->orderTable = $item->orderTable;
                    $element->consecutivo = $item->consecutivo;
                    $element->save();
                    return true;
                }
                if($accion == 2){
                    $itemNew = $obj->create(['nombre' => $item->nombre, 'estado' => $item->estado, 'orderTable' => $item->orderTable, 'consecutivo' => $item->consecutivo]);
                    return true;
                }
                if($accion ==  3){
                    $element = $obj->where('id',$id)->first();
                    if(!is_null($element)){
                        $element->delete();
                        return true;
                    }
                    
                }
                return false;
                break;
            case 'consecutives':
                $obj = new consecutive();
                $obj->construct($this->DBfrom_NAME);
                if($accion == 1){
                    $element = $obj->where('id',$id)->first();
                    $element->num_start = $item->num_start;
                    $element->num_current = $item->num_current;
                    $element->save();
                    return true;
                }
                if($accion == 2){
                    $itemNew = $obj->create(['num_start' => $item->num_start, 'num_current' => $item->num_current]);
                    return true;
                }
                if($accion ==  3){
                    $element = $obj->where('id',$id)->first();
                    if(!is_null($element)){
                        $element->delete();
                        return true;
                    }
                }
                return false;
                break;
            case 'costs':
                $obj = new Cost();
                $obj->construct($this->DBfrom_NAME);
                if($accion == 1){
                    $element = $obj->where('id',$id)->first();
                    $element->cod_alumno = $item->cod_alumno;
                    $element->valor_semestre = $item->valor_semestre;
                    $element->numero_semestre = $item->numero_semestre;
                    $element->valor_total_semestre = $item->valor_total_semestre;
                    $element->descuento = $item->descuento;
                    $element->valor_neto = $item->valor_neto;
                    $element->saldo_financiar = $item->saldo_financiar;
                    $element->periodo = $item->periodo;
                    $element->numero_cuotas = $item->numero_cuotas;
                    $element->valor_cuotas = $item->valor_cuotas;
                    $element->fecha_pago = $item->fecha_pago;
                    $element->save();
                    return true;
                }
                if($accion == 2){
                    $itemNew = $obj->create(['cod_alumno' => $item->cod_alumno, 'valor_semestre' => $item->valor_semestre, 'numero_semestre' => $item->numero_semestre,'valor_total_semestre' => $item->valor_total_semestre, 'descuento' => $item->descuento, 'valor_neto' => $item->valor_neto, 'saldo_financiar' => $item->saldo_financiar, 'periodo' => $item->periodo, 'numero_cuotas' => $item->numero_cuotas, 'valor_cuotas' => $item->valor_cuotas, 'fecha_pago' => $item->fecha_pago]);
                    return true;
                }
                if($accion ==  3){
                    $element = $obj->where('id',$id)->first();
                    if(!is_null($element)){
                        $element->delete();
                        return true;
                    }
                }
                return false;
                break;
            case 'debes':
                $obj = new debe();
                $obj->construct($this->DBfrom_NAME);
                if($accion == 1){
                    $element = $obj->where('id',$id)->first();
                    $element->cuenta = $item->cuenta;
                    $element->nombre = $item->nombre;
                    $element->save();
                    return true;
                }
                if($accion == 2){
                    $itemNew = $obj->create(['cuenta' => $item->cuenta, 'nombre' => $item->nombre]);
                    return true;
                }
                if($accion ==  3){
                    $element = $obj->where('id',$id)->first();
                    if(!is_null($element)){
                        $element->delete();
                        return true;
                    }
                }
                return false;
                # code...
                break;
            case 'elaborados':
                $obj = new elaborado();
                $obj->construct($this->DBfrom_NAME);
                if($accion == 1){
                    $element = $obj->where('id',$id)->first();
                    $element->nombre = $item->nombre;
                    $element->estado = $item->estado;
                    $element->save();
                    return true;
                }
                if($accion == 2){
                    $itemNew = $obj->create(['nombre' => $item->nombre, 'estado' => $item->estado]);
                    return true;
                }
                if($accion ==  3){
                    $element = $obj->where('id',$id)->first();
                    if(!is_null($element)){
                        $element->delete();
                        return true;
                    }
                }
                return false;
                # code...
                break;  
            case 'entries':
                $obj = new Entry();
                $obj->construct($this->DBfrom_NAME);
                if($accion == 1){
                    $element = $obj->where('id',$id)->first();
                    $element->id_cost = $item->id_cost;
                    $element->concepto = $item->concepto;
                    $element->descripcion = $item->descripcion;
                    $element->no_recibo = $item->no_recibo;
                    $element->fecha_recibo = $item->fecha_recibo;
                    $element->valor = $item->valor;
                    $element->elaborado_por = $item->elaborado_por;
                    $element->debe = $item->debe;
                    $element->haber = $item->haber;
                    $element->save();
                    return true;
                }
                if($accion == 2){
                    $itemNew = $obj->create(['id_cost' => $item->id_cost, 'concepto' => $item->concepto, 'descripcion' =>  $item->descripcion, 'no_recibo' =>  $item->no_recibo, 'fecha_recibo' => $item->fecha_recibo, 'valor' => $item->valor, 'elaborado_por' => $item->elaborado_por, 'debe' => $item->debe, 'haber' => $item->haber ]);
                    return true;
                }
                if($accion ==  3){
                    $element = $obj->where('id',$id)->first();
                    if(!is_null($element)){
                        $element->delete();
                        return true;
                    }
     
                   
                }
                return false;
                # code...
                break;  
            case 'habers':
                $obj = new haber();
                $obj->construct($this->DBfrom_NAME);
                if($accion == 1){
                    $element = $obj->where('id',$id)->first();
                    $element->cuenta = $item->cuenta;
                    $element->nombre = $item->nombre;
                    $element->save();
                    return true;
                }
                if($accion == 2){
                    $itemNew = $obj->create(['cuenta' => $item->cuenta, 'nombre' => $item->nombre]);
                    return true;
                }
                if($accion ==  3){
                    $element = $obj->where('id',$id)->first();
                    if(!is_null($element)){
                        $element->delete();
                        return true;
                    }
                }
                return false;
                # code...
                break; 
            case 'history_purses':
                $obj = new historyPurse();
                $obj->construct($this->DBfrom_NAME);
                if($accion == 1){
                    $element = $obj->where('id',$id)->first();
                    $element->id_purse = $item->id_purse;
                    $element->fecha_pago = $item->fecha_pago;
                    $element->estado = $item->estado;
                    $element->cuota = $item->cuota;
                    $element->abonado = $item->abonado;
                    $element->comentario = $item->comentario;
                    $element->save();
                    return true;
                }
                if($accion == 2){
                    $itemNew = $obj->create(['id_purse' => $item->id_purse, 'fecha_pago' => $item->fecha_pago, 'estado' => $item->estado, 'cuota' => $item->cuota,'abonado' => $item->abonado, 'comentario' => $item->comentario ]);
                    return true;
                }
                if($accion ==  3){
                    $element = $obj->where('id',$id)->first();
                    if(!is_null($element)){
                        $element->delete();
                        return true;
                    }
                }
                return false;
                # code...
                break; 
            case 'other_entries':
                $obj = new OtherEntry();
                $obj->construct($this->DBfrom_NAME);
                if($accion == 1){
                    $element = $obj->where('id',$id)->first();
                    $element->id_cost = $item->id_cost;
                    $element->concepto = $item->concepto;
                    $element->descripcion = $item->descripcion;
                    $element->no_recibo = $item->no_recibo;
                    $element->fecha_recibo = $item->fecha_recibo;
                    $element->valor = $item->valor;
                    $element->elaborado_por = $item->elaborado_por;
                    $element->debe = $item->debe;
                    $element->haber = $item->haber;
                    $element->save();
                    return true;
                }
                if($accion == 2){
                    $itemNew = $obj->create(['id_cost' => $item->id_cost, 'concepto' => $item->concepto, 'descripcion' => $item->descripcion, 'no_recibo' => $item->no_recibo,'fecha_recibo' => $item->fecha_recibo, 'valor' => $item->valor, 'elaborado_por' => $item->elaborado_por, 'debe' => $item->debe, 'haber' => $item->haber]);
                    return true;
                }
                if($accion ==  3){
                    $element = $obj->where('id',$id)->first();
                    if(!is_null($element)){
                        $element->delete();
                        return true;
                    }
                }
                return false;
                # code...
                break; 
            case 'otros_conceptos':
                $obj = new otrosConcepto();
                $obj->construct($this->DBfrom_NAME);
                if($accion == 1){
                    $element = $obj->where('id',$id)->first();
                    $element->nombre = $item->nombre;
                    $element->estado = $item->estado;
                    $element->save();
                    return true;
                }
                if($accion == 2){
                    $itemNew = $obj->create(['nombre' => $item->nombre, 'estado' => $item->estado]);
                    return true;
                }
                if($accion ==  3){
                    $element = $obj->where('id',$id)->first();
                    if(!is_null($element)){
                        $element->delete();
                        return true;
                    }
                }
                return false;
                # code...
                break;
            case 'password_privileges':
                $obj = new PasswordPrivileges();
                $obj->construct($this->DBfrom_NAME);
                if($accion == 1){
                    $element = $obj->where('id',$id)->first();
                    $element->password = $item->password;
                    $element->save();
                    return true;
                }
                if($accion == 2){
                    $itemNew = $obj->create(['password' => $item->password]);
                    return true;
                }
                if($accion ==  3){
                    $element = $obj->where('id',$id)->first();
                    if(!is_null($element)){
                        $element->delete();
                        return true;
                    }
                }
                return false;
                # code...
                break;
            case 'purses':
                $obj = new Purse();
                $obj->construct($this->DBfrom_NAME);
                if($accion == 1){
                    $element = $obj->where('id',$id)->first();
                    $element->id_cost = $item->id_cost;
                    $element->fecha_pago = $item->fecha_pago;
                    $element->estado = $item->estado;
                    $element->cuota = $item->cuota;
                    $element->abonado = $item->abonado;
                    $element->comentario = $item->comentario;
                    $element->save();
                    return true;
                }
                if($accion == 2){
                    $itemNew = $obj->create(['id_cost' => $item->id_cost, 'fecha_pago' => $item->fecha_pago , 'estado' => $item->estado,'cuota' => $item->cuota , 'abonado' => $item->abonado , 'comentario' => $item->comentario]);
                    return true;
                }
                if($accion ==  3){
                    $element = $obj->where('id',$id)->first();
                    if(!is_null($element)){
                        $element->delete();
                        return true;
                    }
                }
                return false;
                # code...
                break;
            default:
                # code...
                break;
        }
    }   


}
