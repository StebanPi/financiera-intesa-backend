<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\otrosConcepto;

class OtherConceptosSeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
        otrosConcepto::create([
            'nombre' => 'Uniforme',
            'estado' => 1,
            'debe' => 1,
            'haber' => 3,
        ]);
    }
}
