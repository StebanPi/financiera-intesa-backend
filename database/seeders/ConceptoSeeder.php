<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;
use App\Models\concepto;

class ConceptoSeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
        concepto::create([
            'nombre' => 'Abono Cuota',
            'estado' => 1,
            'orderTable' => 0,
            'consecutivo' => 1,
            'debe' => 1,
            'haber' => 3,
        ]);
        concepto::create([
            'nombre' => 'Abono Inicial',
            'estado' => 1,
            'orderTable' => 0,
            'consecutivo' => 1,
            'debe' => 1,
            'haber' => 3,
        ]);
    }
}
