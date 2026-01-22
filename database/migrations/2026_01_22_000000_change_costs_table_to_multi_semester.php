<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class ChangeCostsTableToMultiSemester extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('costs', function (Blueprint $table) {
            // Eliminar el índice único anterior en cod_alumno
            $table->dropUnique(['cod_alumno']);

            // Añadir un índice único compuesto para evitar duplicados del mismo semestre para un estudiante
            $table->unique(['cod_alumno', 'numero_semestre']);
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::table('costs', function (Blueprint $table) {
            $table->dropUnique(['cod_alumno', 'numero_semestre']);
            $table->unique('cod_alumno');
        });
    }
}
