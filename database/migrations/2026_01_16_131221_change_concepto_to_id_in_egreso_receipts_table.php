<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        // Primero, actualizar los valores existentes: si concepto es un nombre, encontrar su ID correspondiente
        DB::statement('
            UPDATE egreso_receipts er
            INNER JOIN egreso_concepts ec ON er.concepto = ec.nombre
            SET er.concepto = ec.id
            WHERE er.concepto REGEXP "^[^0-9]" -- Solo si concepto no es un número (es un nombre)
        ');
        
        // Para los que no tienen coincidencia, poner NULL temporalmente (luego se deberán actualizar manualmente)
        DB::statement('
            UPDATE egreso_receipts er
            LEFT JOIN egreso_concepts ec ON er.concepto = ec.nombre OR er.concepto = ec.id
            SET er.concepto = NULL
            WHERE ec.id IS NULL AND er.concepto REGEXP "^[^0-9]"
        ');
        
        // Cambiar concepto de string a bigInteger
        Schema::table('egreso_receipts', function (Blueprint $table) {
            $table->dropColumn('concepto');
        });
        
        Schema::table('egreso_receipts', function (Blueprint $table) {
            $table->bigInteger('concepto')->unsigned()->nullable()->after('forma');
        });
        
        // Ahora convertir los valores string a integer si son números
        DB::statement('
            UPDATE egreso_receipts 
            SET concepto = CAST(concepto AS UNSIGNED) 
            WHERE concepto IS NOT NULL AND concepto REGEXP "^[0-9]+$"
        ');
        
        // Agregar foreign key solo si no hay valores NULL o inválidos
        Schema::table('egreso_receipts', function (Blueprint $table) {
            // Verificar que todos los valores sean válidos antes de agregar la foreign key
            $table->foreign('concepto')->references('id')->on('egreso_concepts')->onDelete('restrict');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('egreso_receipts', function (Blueprint $table) {
            $table->dropForeign(['concepto']);
            $table->dropColumn('concepto');
        });
        
        Schema::table('egreso_receipts', function (Blueprint $table) {
            $table->string('concepto')->after('forma');
        });
    }
};
