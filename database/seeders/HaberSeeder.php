<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\haber;

class HaberSeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
        haber::create([
            'cuenta' => 1105,
            'nombre' => 'Caja',
        ]);
        haber::create([
            'cuenta' => 2105,
            'nombre' => 'Bancos nacionales',
        ]);
        haber::create([
            'cuenta' => 4160,
            'nombre' => 'Enseñanza',
        ]);
        haber::create([
            'cuenta' => 4205,
            'nombre' => 'Otras ventas',
        ]);
    }
}
