<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreatePursesTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('purses', function (Blueprint $table) {
            $table->id();
            $table->bigInteger('id_cost')->unsigned();
            $table->foreign('id_cost')->references('id')->on('costs');
            $table->date('fecha_pago');
            $table->enum('estado', ["Al dia", "Pendiente", "En mora","Pago Extraordinario"]);
            $table->bigInteger('cuota');
            $table->bigInteger('abonado');
            $table->text('comentario');
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
        Schema::dropIfExists('purses');
    }
}
