<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::create('egreso_receipts', function (Blueprint $table) {
            $table->id();
            $table->bigInteger('no_recibo');
            $table->date('fecha_recibo');
            $table->bigInteger('proveedor_id')->unsigned();
            $table->foreign('proveedor_id')->references('id')->on('egreso_providers');
            $table->enum('forma', ['Efectivo','Bancos']);
            $table->string('concepto');
            $table->text('descripcion')->nullable();
            $table->bigInteger('valor');
            $table->bigInteger('elaborado_por')->unsigned();
            $table->foreign('elaborado_por')->references('id')->on('elaborados');
            $table->bigInteger('debe')->unsigned();
            $table->foreign('debe')->references('id')->on('debes');
            $table->bigInteger('haber')->unsigned();
            $table->foreign('haber')->references('id')->on('habers');
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('egreso_receipts');
    }
};
