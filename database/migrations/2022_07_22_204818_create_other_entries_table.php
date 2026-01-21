<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateOtherEntriesTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('other_entries', function (Blueprint $table) {
            $table->id();
            $table->bigInteger('id_cost')->unsigned();
            $table->bigInteger('concepto')->unsigned();
            $table->text('descripcion');
            $table->bigInteger('no_recibo');
            $table->date('fecha_recibo');
            $table->bigInteger('valor');
            $table->bigInteger('elaborado_por')->unsigned();
            $table->bigInteger('debe')->unsigned();
            $table->bigInteger('haber')->unsigned();
            $table->foreign('id_cost')->references('id')->on('costs');
            $table->foreign('concepto')->references('id')->on('otros_conceptos');
            $table->foreign('elaborado_por')->references('id')->on('elaborados');
            $table->foreign('debe')->references('id')->on('debes');
            $table->foreign('haber')->references('id')->on('habers');
            $table->enum('forma', ['Efectivo','Consignación']);
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
        Schema::dropIfExists('other_entries');
    }
}
