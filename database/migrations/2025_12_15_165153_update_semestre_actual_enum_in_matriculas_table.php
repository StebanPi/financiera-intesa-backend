<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\DB;

class UpdateSemestreActualEnumInMatriculasTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        // Actualizar el enum de semestre_actual para incluir "Ninguno (curso)"
        DB::statement("ALTER TABLE matriculas MODIFY COLUMN semestre_actual ENUM('I', 'II', 'Ninguno (curso)') NULL");
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        // Revertir a solo I y II
        DB::statement("ALTER TABLE matriculas MODIFY COLUMN semestre_actual ENUM('I', 'II') NULL");
    }
}
