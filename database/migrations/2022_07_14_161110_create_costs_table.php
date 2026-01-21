<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateCostsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('costs', function (Blueprint $table) {
            $table->id();
            $table->bigInteger('cod_alumno')->unique();
            $table->bigInteger('valor_semestre');
            $table->integer('numero_semestre');
            $table->bigInteger('valor_total_semestre');
            $table->bigInteger('descuento');
            $table->bigInteger('valor_neto');
            $table->bigInteger('saldo_financiar');
            $table->string('periodo');
            $table->integer('numero_cuotas');
            $table->bigInteger('valor_cuotas');
            $table->date('fecha_pago');
            $table->text('detalles')->nullable();
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
        Schema::dropIfExists('costs');
    }
}
