<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\consecutive;

class ConsecutiveSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $sedes = [
            'BARRANCABERMEJA' => [
                ['type' => 'entry',     'num_start' => 200,  'num_current' => 200],
                ['type' => 'discharge', 'num_start' => 5000, 'num_current' => 5000],
            ],
            'AGUACHICA' => [
                ['type' => 'entry',     'num_start' => 200,  'num_current' => 200],
                ['type' => 'discharge', 'num_start' => 5000, 'num_current' => 5000],
            ],
        ];

        foreach ($sedes as $sede => $records) {
            foreach ($records as $data) {
                consecutive::updateOrCreate(
                    ['type' => $data['type'], 'sede' => $sede],
                    ['num_start' => $data['num_start'], 'num_current' => $data['num_current']],
                );
            }
        }
    }
}
