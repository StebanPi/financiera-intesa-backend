<?php

namespace App\Http\Controllers;
use App\Models\table_change;
use App\Http\Controllers\GeoController;
use Illuminate\Http\Request;

class TableChangeController extends Controller
{   
    public static function construct()
    {
        $json = GeoController::data();
        $data = json_decode($json);
        
        // Validar que el decode fue exitoso y que tiene las propiedades necesarias
        if ($data === null || isset($data->error) || !isset($data->lat) || !isset($data->lon)) {
            // Retornar valores por defecto si hay error o faltan propiedades
            return (object)['lat' => 0, 'lon' => 0];
        }
        
        return $data;
    }
  
    public static function StoreAdd($table,$id){
        $data = self::construct();        
        table_change::create(['table' => $table, 'id_change' =>  $id, 'add' => 1, 'edit' => 0, 'delete' => 0, 'is_synchronized' => 0, 'latitude' => $data->lat, 'longitude' => $data->lon]);
    }

    public static function StoreEdit($table,$id){
        $data = self::construct();    
        table_change::create(['table' => $table, 'id_change' =>  $id, 'add' => 0, 'edit' => 1, 'delete' => 0, 'is_synchronized' => 0, 'latitude' => $data->lat, 'longitude' => $data->lon]);
    }

    public static function StoreDelete($table,$id){
        $data = self::construct();
        table_change::create(['table' => $table, 'id_change' =>  $id, 'add' => 0, 'edit' => 0, 'delete' => 1, 'is_synchronized' => 0, 'latitude' => $data->lat, 'longitude' => $data->lon]);
    }
}
