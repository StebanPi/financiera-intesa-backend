<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateThirdEntriesTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('third_entries', function (Blueprint $table) {
            $table->id();
            $table->bigInteger('cedula')->unique();
            $table->string('nombre');
            $table->string('direccion')->nullable();
            $table->bigInteger('telefono')->nullable();
            $table->bigInteger('actividad')->unsigned();
            $table->foreign('actividad')->references('id')->on('third_activities');
            $table->text('mas')->nullable();
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
        Schema::dropIfExists('third_entries');
    }
}
