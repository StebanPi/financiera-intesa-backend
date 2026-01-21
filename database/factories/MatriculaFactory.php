<?php

namespace Database\Factories;

use App\Models\Matricula;
use Illuminate\Database\Eloquent\Factories\Factory;

class MatriculaFactory extends Factory
{
    protected $model = Matricula::class;

    /**
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        $doc = (string) fake()->unique()->numerify('########');
        return [
            'cod_alumno' => $doc,
            'nombre_completo' => fake()->name(),
            'numero_documento' => $doc,
            'tipo_documento' => fake()->randomElement(['CC', 'TI', 'PPT']),
            'programa' => 'Auxiliar de Primera Infancia',
            'horario' => 'Diurno',
            'sede' => 'Barrancabermeja',
            'estado_estudiante' => 'Activo',
            'semestre_actual' => 'I',
            'anio' => (string) fake()->year(),
            'numero_grupo' => '1A',
        ];
    }
}
