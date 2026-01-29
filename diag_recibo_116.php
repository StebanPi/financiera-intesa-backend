<?php

use App\Models\Entry;
use App\Models\Cost;
use App\Services\StudentResolverService;
use Illuminate\Support\Facades\DB;

$noRecibo = '116';
$entry = DB::table('entries')->where('no_recibo', $noRecibo)->first();

if (!$entry) {
    echo "No se encontró el recibo $noRecibo\n";
    exit;
}

echo "Recibo: " . json_encode($entry) . "\n";

$cost = DB::table('costs')->where('id', $entry->id_cost)->first();
if ($cost) {
    echo "Costo asociado: " . json_encode($cost) . "\n";
    echo "cod_alumno en tabla costs: '" . $cost->cod_alumno . "'\n";
    
    $student = StudentResolverService::getStudentData($cost->cod_alumno);
    echo "Resultado de StudentResolverService: " . json_encode($student) . "\n";
} else {
    echo "No se encontró el costo asociado (id: " . $entry->id_cost . ")\n";
}
