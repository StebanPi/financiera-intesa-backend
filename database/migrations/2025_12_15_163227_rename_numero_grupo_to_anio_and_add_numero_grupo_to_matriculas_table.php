<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\DB;

class RenameNumeroGrupoToAnioAndAddNumeroGrupoToMatriculasTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        // Renombrar numero_grupo a anio usando SQL directo
        DB::statement("ALTER TABLE matriculas CHANGE COLUMN numero_grupo anio VARCHAR(255) NULL");
        
        // Agregar nuevo campo numero_grupo con las opciones especificadas
        Schema::table('matriculas', function (Blueprint $table) {
            $table->enum('numero_grupo', ['1A', '1B', '2A', '2B', '3A', '3B'])->nullable()->after('anio');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::table('matriculas', function (Blueprint $table) {
            $table->dropColumn('numero_grupo');
        });
        
        // Revertir anio a numero_grupo
        DB::statement("ALTER TABLE matriculas CHANGE COLUMN anio numero_grupo VARCHAR(255) NULL");
    }
}
