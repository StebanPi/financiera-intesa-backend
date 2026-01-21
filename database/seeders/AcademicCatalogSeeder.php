<?php

namespace Database\Seeders;

use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use App\Models\Program;
use App\Models\Schedule;
use App\Models\Group;
use App\Models\Teacher;
use App\Models\Module;
use App\Models\InstitutionSetting;

class AcademicCatalogSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        // Programas
        Program::firstOrCreate(
            ['name' => 'Auxiliar de Primera Infancia'],
            ['active' => true]
        );
        Program::firstOrCreate(
            ['name' => 'Auxiliar Administrativo'],
            ['active' => true]
        );
        Program::firstOrCreate(
            ['name' => 'Seguridad en el Trabajo'],
            ['active' => true]
        );
        Program::firstOrCreate(
            ['name' => 'Operador de Maquinaria Pesada'],
            ['active' => true]
        );
        Program::firstOrCreate(
            ['name' => 'Mecánica Diesel Automotriz'],
            ['active' => true]
        );

        // Horarios
        Schedule::firstOrCreate(
            ['name' => 'Diurno'],
            ['active' => true]
        );
        Schedule::firstOrCreate(
            ['name' => 'Nocturno'],
            ['active' => true]
        );
        Schedule::firstOrCreate(
            ['name' => 'Mixto'],
            ['active' => true]
        );
        Schedule::firstOrCreate(
            ['name' => 'Fin de Semana'],
            ['active' => true]
        );

        // Grupos
        Group::firstOrCreate(
            ['name' => '1A'],
            ['active' => true]
        );
        Group::firstOrCreate(
            ['name' => '1B'],
            ['active' => true]
        );
        Group::firstOrCreate(
            ['name' => '2A'],
            ['active' => true]
        );
        Group::firstOrCreate(
            ['name' => '2B'],
            ['active' => true]
        );
        Group::firstOrCreate(
            ['name' => '3A'],
            ['active' => true]
        );
        Group::firstOrCreate(
            ['name' => '3B'],
            ['active' => true]
        );

        // Configuración de Institución
        InstitutionSetting::firstOrCreate(
            ['id' => 1],
            [
                'name' => 'INSTITUTO TECNICO DEL SABER',
                'nit' => '',
                'address' => 'Barrio Galan. Calle 51 No.16-66',
                'phone' => '317 4100817',
                'email' => 'intesa.academia@gmail.com',
                'website' => 'www.institutointesa.edu.co',
                'footer_licencia_texto' => 'Licencia de Funcionamiento según Resolución No. 3021 del 15 de diciembre de 2015; NIT 77168558-1',
                'footer_ciudad' => '',
                'footer_mostrar_ubicacion_fecha' => false,
            ]
        );
    }
}
