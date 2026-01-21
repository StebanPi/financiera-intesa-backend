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
        // Para conceptos: mantener solo el primer registro por nombre
        // Primero verificar si hay entries usando los conceptos duplicados
        // Si hay entries usando conceptos duplicados, migrar a los conceptos que se mantendrán
        DB::statement('
            UPDATE entries e1
            INNER JOIN (
                SELECT nombre, MIN(id) as min_id
                FROM conceptos
                GROUP BY nombre
            ) c_min ON c_min.nombre = (SELECT nombre FROM conceptos WHERE id = e1.concepto)
            SET e1.concepto = c_min.min_id
            WHERE e1.concepto NOT IN (
                SELECT id FROM (
                    SELECT MIN(id) as id
                    FROM conceptos
                    GROUP BY nombre
                ) as conceptos_unicos
            )
        ');
        
        // Ahora eliminar duplicados de conceptos (mantener solo el primer registro por nombre)
        DB::statement('DELETE c1 FROM conceptos c1 
            INNER JOIN conceptos c2 
            WHERE c1.id > c2.id AND c1.nombre = c2.nombre');
        
        // Agregar índice único
        $connection = DB::connection();
        $databaseName = $connection->getDatabaseName();
        
        // Verificar y agregar índice único en conceptos
        $conceptosIndexExists = DB::select("
            SELECT COUNT(*) as count 
            FROM information_schema.statistics 
            WHERE table_schema = ? 
            AND table_name = 'conceptos' 
            AND index_name = 'conceptos_nombre_unique'
        ", [$databaseName]);
        
        if ($conceptosIndexExists[0]->count == 0) {
        Schema::table('conceptos', function (Blueprint $table) {
                $table->unique('nombre', 'conceptos_nombre_unique');
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
        
        $conceptosIndexExists = DB::select("
            SELECT COUNT(*) as count 
            FROM information_schema.statistics 
            WHERE table_schema = ? 
            AND table_name = 'conceptos' 
            AND index_name = 'conceptos_nombre_unique'
        ", [$databaseName]);
        
        if ($conceptosIndexExists[0]->count > 0) {
        Schema::table('conceptos', function (Blueprint $table) {
                $table->dropUnique('conceptos_nombre_unique');
        });
        }
    }
};
