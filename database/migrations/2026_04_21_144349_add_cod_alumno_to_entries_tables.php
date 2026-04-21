<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        // Hacer id_cost nullable (usando DB::statement por problemas de dependencias dbal)
        DB::statement('ALTER TABLE entries MODIFY id_cost BIGINT UNSIGNED NULL');
        DB::statement('ALTER TABLE other_entries MODIFY id_cost BIGINT UNSIGNED NULL');

        // Backfill de cod_alumno desde la tabla costs
        DB::statement('UPDATE entries e INNER JOIN costs c ON c.id = e.id_cost SET e.cod_alumno = c.cod_alumno WHERE e.id_cost IS NOT NULL AND e.cod_alumno IS NULL');
        DB::statement('UPDATE other_entries o INNER JOIN costs c ON c.id = o.id_cost SET o.cod_alumno = c.cod_alumno WHERE o.id_cost IS NOT NULL AND o.cod_alumno IS NULL');
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        // Revertir a NOT NULL (solo si sabemos que todos tienen id_cost válido)
        DB::statement('ALTER TABLE entries MODIFY id_cost BIGINT UNSIGNED NOT NULL');
        DB::statement('ALTER TABLE other_entries MODIFY id_cost BIGINT UNSIGNED NOT NULL');
    }
};