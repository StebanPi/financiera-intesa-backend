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
        // Limpiar duplicados antes de agregar índices únicos
        // Para elaborados: mantener solo el primer registro por nombre
        DB::statement('DELETE e1 FROM elaborados e1 
            INNER JOIN elaborados e2 
            WHERE e1.id > e2.id AND e1.nombre = e2.nombre');
        
        // Para debes: mantener solo el primer registro por cuenta y nombre
        DB::statement('DELETE d1 FROM debes d1 
            INNER JOIN debes d2 
            WHERE d1.id > d2.id AND d1.cuenta = d2.cuenta AND d1.nombre = d2.nombre');
        
        // Para habers: mantener solo el primer registro por cuenta y nombre
        DB::statement('DELETE h1 FROM habers h1 
            INNER JOIN habers h2 
            WHERE h1.id > h2.id AND h1.cuenta = h2.cuenta AND h1.nombre = h2.nombre');
        
        // Agregar índices únicos usando SQL directo para verificar si existen
        $connection = DB::connection();
        $databaseName = $connection->getDatabaseName();
        
        // Verificar y agregar índice único en elaborados
        $elaboradosIndexExists = DB::select("
            SELECT COUNT(*) as count 
            FROM information_schema.statistics 
            WHERE table_schema = ? 
            AND table_name = 'elaborados' 
            AND index_name = 'elaborados_nombre_unique'
        ", [$databaseName]);
        
        if ($elaboradosIndexExists[0]->count == 0) {
            Schema::table('elaborados', function (Blueprint $table) {
                $table->unique('nombre', 'elaborados_nombre_unique');
            });
        }
        
        // Verificar y agregar índice único en debes
        $debesIndexExists = DB::select("
            SELECT COUNT(*) as count 
            FROM information_schema.statistics 
            WHERE table_schema = ? 
            AND table_name = 'debes' 
            AND index_name = 'debes_cuenta_nombre_unique'
        ", [$databaseName]);
        
        if ($debesIndexExists[0]->count == 0) {
            Schema::table('debes', function (Blueprint $table) {
                $table->unique(['cuenta', 'nombre'], 'debes_cuenta_nombre_unique');
            });
        }
        
        // Verificar y agregar índice único en habers
        $habersIndexExists = DB::select("
            SELECT COUNT(*) as count 
            FROM information_schema.statistics 
            WHERE table_schema = ? 
            AND table_name = 'habers' 
            AND index_name = 'habers_cuenta_nombre_unique'
        ", [$databaseName]);
        
        if ($habersIndexExists[0]->count == 0) {
            Schema::table('habers', function (Blueprint $table) {
                $table->unique(['cuenta', 'nombre'], 'habers_cuenta_nombre_unique');
            });
        }
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        // Eliminar índices únicos si existen
        $connection = DB::connection();
        $databaseName = $connection->getDatabaseName();
        
        // Eliminar índice de elaborados
        $elaboradosIndexExists = DB::select("
            SELECT COUNT(*) as count 
            FROM information_schema.statistics 
            WHERE table_schema = ? 
            AND table_name = 'elaborados' 
            AND index_name = 'elaborados_nombre_unique'
        ", [$databaseName]);
        
        if ($elaboradosIndexExists[0]->count > 0) {
            Schema::table('elaborados', function (Blueprint $table) {
                $table->dropUnique('elaborados_nombre_unique');
            });
        }
        
        // Eliminar índice de debes
        $debesIndexExists = DB::select("
            SELECT COUNT(*) as count 
            FROM information_schema.statistics 
            WHERE table_schema = ? 
            AND table_name = 'debes' 
            AND index_name = 'debes_cuenta_nombre_unique'
        ", [$databaseName]);
        
        if ($debesIndexExists[0]->count > 0) {
            Schema::table('debes', function (Blueprint $table) {
                $table->dropUnique('debes_cuenta_nombre_unique');
            });
        }
        
        // Eliminar índice de habers
        $habersIndexExists = DB::select("
            SELECT COUNT(*) as count 
            FROM information_schema.statistics 
            WHERE table_schema = ? 
            AND table_name = 'habers' 
            AND index_name = 'habers_cuenta_nombre_unique'
        ", [$databaseName]);
        
        if ($habersIndexExists[0]->count > 0) {
            Schema::table('habers', function (Blueprint $table) {
                $table->dropUnique('habers_cuenta_nombre_unique');
            });
        }
    }
};
