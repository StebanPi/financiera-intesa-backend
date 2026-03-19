<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    /**
     * Mueve todos los estudiantes (y sus datos financieros) registrados como
     * AGUACHICA antes de la separación multisede a BARRANCABERMEJA.
     *
     * Aguachica inicia desde cero: solo los nuevos registros creados después
     * de este migrate pertenecerán a esa sede.
     *
     * Tablas afectadas: matriculas, entries, other_entries.
     * (costs no tiene sede; la sede se determina por la matrícula del alumno)
     */
    public function up(): void
    {
        // 1. Mover matrículas
        DB::statement("
            UPDATE matriculas
            SET sede = 'BARRANCABERMEJA'
            WHERE UPPER(sede) = 'AGUACHICA'
        ");

        // 2. Mover entries (abonos de semestre)
        DB::statement("
            UPDATE entries
            SET sede = 'BARRANCABERMEJA'
            WHERE UPPER(sede) = 'AGUACHICA'
        ");

        // 3. Mover other_entries (otros ingresos)
        DB::statement("
            UPDATE other_entries
            SET sede = 'BARRANCABERMEJA'
            WHERE UPPER(sede) = 'AGUACHICA'
        ");

        // 4. Reiniciar consecutivos de Aguachica al num_start
        //    (no hay entries reales aún, se parte desde 0)
        DB::statement("
            UPDATE consecutives
            SET num_current = num_start
            WHERE UPPER(sede) = 'AGUACHICA'
        ");
    }

    public function down(): void
    {
        // No hay forma segura de revertir sin saber cuáles eran originalmente
        // de Aguachica. Este down() es intencional no-op.
    }
};
