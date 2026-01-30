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
        Schema::table('permissions', function (Blueprint $table) {
            \DB::table('permissions')->insertOrIgnore([
                'name' => 'Eliminar Registros Financieros',
                'slug' => 'records.delete',
                'description' => 'Permiso para eliminar abonos, otros ingresos, egresos y costos',
                'created_at' => now(),
                'updated_at' => now(),
            ]);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('permissions', function (Blueprint $table) {
            \DB::table('permissions')->where('slug', 'records.delete')->delete();
        });
    }
};
