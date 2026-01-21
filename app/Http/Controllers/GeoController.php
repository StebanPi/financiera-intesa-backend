<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Http;

class GeoController extends Controller
{

    public static function data() {
        try {
            // Obtener la IP pública
            $response = Http::get("https://api.ipify.org/?format=json");
            $res = $response->json();
            
            if (!isset($res['ip'])) {
                return json_encode(['error' => 'No se pudo obtener la IP']);
            }

            // Obtener información geográfica de la IP
            $geoResponse = Http::get("http://ip-api.com/json/".$res['ip']);
            $json = $geoResponse->body();
            
            return $json;
        } catch (\Exception $e) {
            return json_encode(['error' => 'Error al obtener información geográfica: ' . $e->getMessage()]);
        }
    }
}
