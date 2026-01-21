<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\DB;

class ChangeNumeroGrupoToStringInMatriculasTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        // Cambiar numero_grupo de ENUM a VARCHAR para aceptar valores dinámicos desde la tabla groups
        DB::statement("ALTER TABLE matriculas MODIFY COLUMN numero_grupo VARCHAR(255) NULL");
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        // Revertir a ENUM con valores originales
        DB::statement("ALTER TABLE matriculas MODIFY COLUMN numero_grupo ENUM('1A', '1B', '2A', '2B', '3A', '3B') NULL");
    }
}
