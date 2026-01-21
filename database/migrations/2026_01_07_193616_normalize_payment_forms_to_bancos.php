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
        // Normalizar datos existentes: reemplazar 'Consignación' por 'Bancos'
        DB::table('entries')->where('forma', 'Consignación')->update(['forma' => 'Bancos']);
        DB::table('other_entries')->where('forma', 'Consignación')->update(['forma' => 'Bancos']);
        DB::table('third_receipts')->where('forma', 'Consignación')->update(['forma' => 'Bancos']);
        // egreso_receipts ya se crea con 'Bancos', así que no necesita actualización
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        // Revertir: cambiar 'Bancos' a 'Consignación' donde aplique
        DB::table('entries')->where('forma', 'Bancos')->update(['forma' => 'Consignación']);
        DB::table('other_entries')->where('forma', 'Bancos')->update(['forma' => 'Consignación']);
        DB::table('third_receipts')->where('forma', 'Bancos')->update(['forma' => 'Consignación']);
    }
};
