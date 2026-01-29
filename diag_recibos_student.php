<?php

$studentId = '1067603652';

$costs = DB::table('costs')->where('cod_alumno', $studentId)->get();
echo "Costos para el estudiante $studentId: " . count($costs) . "\n";

foreach ($costs as $cost) {
    echo "  Costo ID: {$cost->id}, Sede: {$cost->sede}\n";
    
    $entries = DB::table('entries')->where('id_cost', $cost->id)->get();
    foreach ($entries as $e) {
        echo "    Entry: No. {$e->no_recibo}, Fecha: {$e->fecha_recibo}, Sede: '{$e->sede}', Valor: {$e->valor}\n";
    }
    
    $otherEntries = DB::table('other_entries')->where('id_cost', $cost->id)->get();
    foreach ($oe as $e) {
        echo "    OtherEntry: No. {$e->no_recibo}, Fecha: {$e->fecha_recibo}, Sede: '{$e->sede}', Valor: {$e->valor}\n";
    }
}

// También buscar por no_recibo 116 directamente sin id_cost
$anyEntry = DB::table('entries')->where('no_recibo', 'like', '%116%')->get();
foreach ($anyEntry as $e) {
    echo "Entry encontrado por no_recibo like 116: ID: {$e->id}, No: {$e->no_recibo}, Cost: {$e->id_cost}\n";
}
