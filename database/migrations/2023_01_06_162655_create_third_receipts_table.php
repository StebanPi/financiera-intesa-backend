<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateThirdReceiptsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('third_receipts', function (Blueprint $table) {
            $table->id();
            $table->bigInteger('no_recibo');
            $table->enum('type', ['entry','discharge']);
            $table->bigInteger('third')->unsigned();
            $table->foreign('third')->references('id')->on('third_entries');
            $table->bigInteger('concepto');
            $table->text('detalles')->nullable();
            $table->bigInteger('valor');
            $table->bigInteger('debe')->unsigned();
            $table->foreign('debe')->references('id')->on('debes');
            $table->bigInteger('haber')->unsigned();
            $table->foreign('haber')->references('id')->on('habers');
            $table->bigInteger('elaborado_por')->unsigned();
            $table->foreign('elaborado_por')->references('id')->on('elaborados');
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
        Schema::dropIfExists('third_receipts');
    }
}
