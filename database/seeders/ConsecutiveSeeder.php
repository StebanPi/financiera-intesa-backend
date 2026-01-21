<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;
use App\Models\consecutive;

class ConsecutiveSeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
        consecutive::create([
            'type' => 'entry',
            'num_start' => 200,
            'num_current' => 204,
        ]);
        consecutive::create([
            'type' => 'discharge',
            'num_start' => 5000,
            'num_current' => 5002,
        ]);
        consecutive::create([
            'type' => 'entry',
            'num_start' => 10000,
            'num_current' => 10000,
        ]);
        consecutive::create([
            'type' => 'discharge',
            'num_start' => 5000,
            'num_current' => 5000,
        ]);
    }
}
