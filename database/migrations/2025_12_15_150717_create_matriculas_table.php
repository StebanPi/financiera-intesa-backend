<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateMatriculasTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('matriculas', function (Blueprint $table) {
            $table->id();
            $table->bigInteger('cod_alumno')->unique();
            $table->string('nombre_completo');
            $table->string('numero_documento');
            $table->string('lugar_expedicion_documento')->nullable();
            $table->enum('tipo_documento', ['CC', 'TI', 'PPT'])->default('CC');
            $table->date('fecha_nacimiento')->nullable();
            $table->text('direccion_barrio')->nullable();
            $table->string('ciudad_residencia')->nullable();
            $table->string('departamento')->nullable();
            $table->string('correo_gmail')->nullable();
            $table->string('telefono_personal')->nullable();
            $table->string('telefono_emergencia')->nullable();
            $table->string('estado_civil')->nullable();
            $table->integer('estrato')->nullable();
            $table->string('nivel_sisben')->nullable();
            $table->string('eps')->nullable();
            $table->string('grupo_sanguineo')->nullable();
            $table->string('nivel_formacion')->nullable();
            $table->string('ocupacion')->nullable();
            $table->boolean('tiene_discapacidad')->default(false);
            $table->text('discapacidad_descripcion')->nullable();
            $table->string('programa')->nullable();
            $table->string('horario')->nullable();
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('matriculas');
    }
}
