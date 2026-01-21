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
        Schema::table('third_receipts', function (Blueprint $table) {
            $table->date('fecha_recibo')->nullable()->after('no_recibo');
        });
        
        // Migrar datos existentes: usar created_at como fecha_recibo si existe
        DB::statement("UPDATE third_receipts SET fecha_recibo = DATE(created_at) WHERE fecha_recibo IS NULL");
        
        // Hacer el campo obligatorio después de migrar
        Schema::table('third_receipts', function (Blueprint $table) {
            $table->date('fecha_recibo')->nullable(false)->change();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('third_receipts', function (Blueprint $table) {
            $table->dropColumn('fecha_recibo');
        });
    }
};
