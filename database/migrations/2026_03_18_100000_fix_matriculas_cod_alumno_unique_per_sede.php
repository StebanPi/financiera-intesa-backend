<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('matriculas', function (Blueprint $table) {
            // Eliminar el unique global de cod_alumno
            $table->dropUnique(['cod_alumno']);

            // Agregar unique compuesto (cod_alumno, sede)
            $table->unique(['cod_alumno', 'sede'], 'matriculas_cod_alumno_sede_unique');
        });
    }

    public function down(): void
    {
        Schema::table('matriculas', function (Blueprint $table) {
            $table->dropUnique('matriculas_cod_alumno_sede_unique');
            $table->unique('cod_alumno');
        });
    }
};
