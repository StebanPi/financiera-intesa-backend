<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\DB;

class AddCodAlumnoToEntriesAndOtherEntries extends Migration
{
    public function up(): void
    {
        // Verificar si la columna ya existe en entries
        $entriesHasColumn = DB::select("SHOW COLUMNS FROM entries WHERE Field = 'cod_alumno'");
        if (empty($entriesHasColumn)) {
            Schema::table('entries', function (Blueprint $table) {
                $table->bigInteger('cod_alumno')->nullable()->after('id_cost');
            });
        }

        // Verificar si la columna ya existe en other_entries
        $otherEntriesHasColumn = DB::select("SHOW COLUMNS FROM other_entries WHERE Field = 'cod_alumno'");
        if (empty($otherEntriesHasColumn)) {
            Schema::table('other_entries', function (Blueprint $table) {
                $table->bigInteger('cod_alumno')->nullable()->after('id_cost');
            });
        }
    }

    public function down(): void
    {
        Schema::table('entries', function (Blueprint $table) {
            if (DB::select("SHOW COLUMNS FROM entries WHERE Field = 'cod_alumno'")) {
                $table->dropColumn('cod_alumno');
            }
        });

        Schema::table('other_entries', function (Blueprint $table) {
            if (DB::select("SHOW COLUMNS FROM other_entries WHERE Field = 'cod_alumno'")) {
                $table->dropColumn('cod_alumno');
            }
        });
    }
}
