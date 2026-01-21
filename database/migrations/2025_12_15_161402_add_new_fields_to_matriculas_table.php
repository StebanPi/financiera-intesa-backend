<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class AddNewFieldsToMatriculasTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('matriculas', function (Blueprint $table) {
            // Agregar nuevos campos
            $table->string('sede')->nullable()->after('horario');
            $table->enum('estado_estudiante', ['Activo', 'Inactivo', 'Por Certificar', 'Certificado', 'Retirado', 'Suspendido', 'Todos'])->nullable()->after('sede');
            $table->string('contraseña_plataforma')->nullable()->after('estado_estudiante');
            $table->enum('talla_uniforme', ['XS', 'S', 'M', 'L', 'XL', 'XXL', 'XXXL'])->nullable()->after('contraseña_plataforma');
            $table->enum('semestre_actual', ['I', 'II'])->nullable()->after('talla_uniforme');
            $table->string('numero_grupo')->nullable()->after('semestre_actual');
            $table->string('tipo_discapacidad')->nullable()->after('discapacidad_descripcion');
        });
        
        // Modificar tipo_documento usando DB raw (ya que cambiar enum requiere DBAL)
        DB::statement("ALTER TABLE matriculas MODIFY COLUMN tipo_documento ENUM('CC', 'TI', 'PPT') DEFAULT 'CC'");
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::table('matriculas', function (Blueprint $table) {
            $table->dropColumn([
                'sede',
                'estado_estudiante',
                'contraseña_plataforma',
                'talla_uniforme',
                'semestre_actual',
                'numero_grupo',
                'tipo_discapacidad'
            ]);
        });
        
        // Revertir tipo_documento
        DB::statement("ALTER TABLE matriculas MODIFY COLUMN tipo_documento ENUM('CC', 'TI') DEFAULT 'CC'");
    }
}
