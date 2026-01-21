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
        // Cambiar enum de 'Consignación' a 'Bancos' en entries
        DB::statement("ALTER TABLE entries MODIFY COLUMN forma ENUM('Efectivo','Bancos') NOT NULL");
        
        // Cambiar enum de 'Consignación' a 'Bancos' en other_entries
        DB::statement("ALTER TABLE other_entries MODIFY COLUMN forma ENUM('Efectivo','Bancos') NOT NULL");
        
        // Cambiar enum de 'Consignación' a 'Bancos' en third_receipts
        DB::statement("ALTER TABLE third_receipts MODIFY COLUMN forma ENUM('Efectivo','Bancos') NOT NULL");
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        // Revertir enum a 'Consignación' en entries
        DB::statement("ALTER TABLE entries MODIFY COLUMN forma ENUM('Efectivo','Consignación') NOT NULL");
        
        // Revertir enum a 'Consignación' en other_entries
        DB::statement("ALTER TABLE other_entries MODIFY COLUMN forma ENUM('Efectivo','Consignación') NOT NULL");
        
        // Revertir enum a 'Consignación' en third_receipts
        DB::statement("ALTER TABLE third_receipts MODIFY COLUMN forma ENUM('Efectivo','Consignación') NOT NULL");
    }
};
