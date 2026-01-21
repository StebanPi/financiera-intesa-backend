<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;
use App\Models\debe;

class DebeSeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
        debe::create([
            'cuenta' => 1105,
            'nombre' => 'Caja',
        ]);
        debe::create([
            'cuenta' => 1110,
            'nombre' => 'Bancos',
        ]);
        debe::create([
            'cuenta' => 2105,
            'nombre' => 'Bancos Nacionales',
        ]);
        debe::create([
            'cuenta' => 2205,
            'nombre' => 'Nacionales',
        ]);
        debe::create([
            'cuenta' => 5110,
            'nombre' => 'Honorarios',
        ]);
        debe::create([
            'cuenta' => 5115,
            'nombre' => 'Impuestos',
        ]);
        debe::create([
            'cuenta' => 5120,
            'nombre' => 'Arrendamientos',
        ]);
        debe::create([
            'cuenta' => 5105,
            'nombre' => 'Gastos de personal',
        ]);
        debe::create([
            'cuenta' => 5135,
            'nombre' => 'Servicios',
        ]);
        debe::create([
            'cuenta' => 5145,
            'nombre' => 'Mantenimiento y reparaciones',
        ]);
        debe::create([
            'cuenta' => 5195,
            'nombre' => 'Diversos',
        ]);
        debe::create([
            'cuenta' => 5130,
            'nombre' => 'Seguros',
        ]);
        debe::create([
            'cuenta' => 6160,
            'nombre' => 'Enseñanza',
        ]);
        debe::create([
            'cuenta' => 5155,
            'nombre' => 'Gastos de viaje',
        ]);
    }
}
