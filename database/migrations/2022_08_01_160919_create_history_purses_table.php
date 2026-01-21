<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateHistoryPursesTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('history_purses', function (Blueprint $table) {
            $table->id();
            $table->bigInteger('id_purse')->unsigned();
            $table->foreign('id_purse')->references('id')->on('purses');
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
        Schema::dropIfExists('history_purses');
    }
}
