<?php

namespace App\Http\Controllers;
use App\Http\Controllers\TableChangeController;
use Illuminate\Http\Request;

class DateController extends Controller
{
    //
    public static $Meses = array('Enero', 'Febrero', 'Marzo', 'Abril', 'Mayo', 'Junio',
       'Julio', 'Agosto', 'Septiembre', 'Octubre', 'Noviembre', 'Diciembre');

    public static function getMes($id){
        $id = intval($id) - 1;
        return self::$Meses[$id];
    }
    public static function nextMes($id,$return_num){
        if($id < 12){
            $id = intval($id) + 1;
        }else{
            if($return_num){
                $id = 1;
            }else{
                $id = 0;
            }
        }
        if(!$return_num){
            return self::$Meses[$id];
        }
        return $id;

    }

    public static function Is_nextYear($year,$mes){
        if($mes > 1){
            $mesAnterior = $mes - 1;
        }else{
            $mesAnterior = 12;
        }
        if($mes == 1 && $mesAnterior == 12 ){
            return intval($year)+1;
        }
        return $year;
    }

    public static function getMesSubtr($fecha){
        if(empty($fecha) || !is_string($fecha)){
            return '';
        }
        $fecha_array = explode('-',$fecha);
        if(count($fecha_array) < 3){
            return $fecha;
        }
        $id = intval($fecha_array[1]) - 1;
        if($id < 0 || $id >= count(self::$Meses)){
            return $fecha;
        }
        $name = substr(self::$Meses[$id],0,3);
        return $fecha_array[2]."-".$name."-".$fecha_array[0];
    }

    public static function transformMonth($Arrayd,$Campos){
        for ($i=0; $i < count($Campos); $i++) { 
            $value = $Campos[$i];
            $Arrayd->$value = self::getMesSubtr($Arrayd->$value);
        }
        return $Arrayd;
    }

}
