<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        // Limpiar duplicados antes de agregar índice único
        // Para otros_conceptos: mantener solo el primer registro por nombre
        // Primero verificar si hay other_entries usando los conceptos duplicados
        // Si hay other_entries usando conceptos duplicados, migrar a los conceptos que se mantendrán
        DB::statement('
            UPDATE other_entries oe1
            INNER JOIN (
                SELECT nombre, MIN(id) as min_id
                FROM otros_conceptos
                GROUP BY nombre
            ) oc_min ON oc_min.nombre = (SELECT nombre FROM otros_conceptos WHERE id = oe1.concepto)
            SET oe1.concepto = oc_min.min_id
            WHERE oe1.concepto NOT IN (
                SELECT id FROM (
                    SELECT MIN(id) as id
                    FROM otros_conceptos
                    GROUP BY nombre
                ) as otros_conceptos_unicos
            )
        ');
        
        // Ahora eliminar duplicados de otros_conceptos (mantener solo el primer registro por nombre)
        DB::statement('DELETE oc1 FROM otros_conceptos oc1 
            INNER JOIN otros_conceptos oc2 
            WHERE oc1.id > oc2.id AND oc1.nombre = oc2.nombre');
        
        // Agregar índice único
        $connection = DB::connection();
        $databaseName = $connection->getDatabaseName();
        
        // Verificar y agregar índice único en otros_conceptos
        $otrosConceptosIndexExists = DB::select("
            SELECT COUNT(*) as count 
            FROM information_schema.statistics 
            WHERE table_schema = ? 
            AND table_name = 'otros_conceptos' 
            AND index_name = 'otros_conceptos_nombre_unique'
        ", [$databaseName]);
        
        if ($otrosConceptosIndexExists[0]->count == 0) {
        Schema::table('otros_conceptos', function (Blueprint $table) {
                $table->unique('nombre', 'otros_conceptos_nombre_unique');
        });
        }
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        // Eliminar índice único si existe
        $connection = DB::connection();
        $databaseName = $connection->getDatabaseName();
        
        $otrosConceptosIndexExists = DB::select("
            SELECT COUNT(*) as count 
            FROM information_schema.statistics 
            WHERE table_schema = ? 
            AND table_name = 'otros_conceptos' 
            AND index_name = 'otros_conceptos_nombre_unique'
        ", [$databaseName]);
        
        if ($otrosConceptosIndexExists[0]->count > 0) {
        Schema::table('otros_conceptos', function (Blueprint $table) {
                $table->dropUnique('otros_conceptos_nombre_unique');
        });
        }
    }
};
