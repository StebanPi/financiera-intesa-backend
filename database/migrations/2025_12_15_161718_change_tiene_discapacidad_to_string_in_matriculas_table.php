<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\DB;

class ChangeTieneDiscapacidadToStringInMatriculasTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        // Cambiar tiene_discapacidad de boolean a string usando DB raw
        DB::statement("ALTER TABLE matriculas MODIFY COLUMN tiene_discapacidad VARCHAR(50) NULL");
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        // Revertir a boolean
        DB::statement("ALTER TABLE matriculas MODIFY COLUMN tiene_discapacidad BOOLEAN DEFAULT 0");
    }
}
