<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        // Eliminar duplicados: por cada (type, sede) conservar solo el registro
        // con mayor num_current (el que ha sido usado activamente).
        DB::statement('
            DELETE c1 FROM consecutives c1
            INNER JOIN consecutives c2
                ON  c1.type = c2.type
                AND c1.sede = c2.sede
                AND c1.num_current < c2.num_current
        ');

        // Si dos registros tienen el mismo num_current (caso extremo), conservar
        // el de menor id y eliminar el resto.
        DB::statement('
            DELETE c1 FROM consecutives c1
            INNER JOIN consecutives c2
                ON  c1.type = c2.type
                AND c1.sede = c2.sede
                AND c1.id > c2.id
        ');

        // Añadir índice único (type, sede) si no existe.
        $db = DB::connection()->getDatabaseName();

        $exists = DB::selectOne("
            SELECT COUNT(*) AS cnt
            FROM information_schema.statistics
            WHERE table_schema = ?
              AND table_name   = 'consecutives'
              AND index_name   = 'consecutives_type_sede_unique'
        ", [$db]);

        if ($exists->cnt === 0) {
            Schema::table('consecutives', function (Blueprint $table) {
                $table->unique(['type', 'sede'], 'consecutives_type_sede_unique');
            });
        }
    }

    public function down(): void
    {
        $db = DB::connection()->getDatabaseName();

        $exists = DB::selectOne("
            SELECT COUNT(*) AS cnt
            FROM information_schema.statistics
            WHERE table_schema = ?
              AND table_name   = 'consecutives'
              AND index_name   = 'consecutives_type_sede_unique'
        ", [$db]);

        if ($exists->cnt > 0) {
            Schema::table('consecutives', function (Blueprint $table) {
                $table->dropUnique('consecutives_type_sede_unique');
            });
        }
    }
};
