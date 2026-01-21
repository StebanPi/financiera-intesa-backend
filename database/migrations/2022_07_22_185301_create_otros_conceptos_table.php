<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateOtrosConceptosTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('otros_conceptos', function (Blueprint $table) {
            $table->id();
            $table->string('nombre');
            $table->boolean('estado');
            $table->bigInteger('debe')->unsigned();
            $table->foreign('debe')->references('id')->on('debes');
            $table->bigInteger('haber')->unsigned();
            $table->foreign('haber')->references('id')->on('habers');
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
        Schema::dropIfExists('otros_conceptos');
    }
}
