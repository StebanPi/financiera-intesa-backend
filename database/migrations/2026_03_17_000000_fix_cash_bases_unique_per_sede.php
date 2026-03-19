<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('cash_bases', function (Blueprint $table) {
            // Eliminar el unique constraint original (solo sobre fecha)
            $table->dropUnique(['fecha']);

            // Nuevo unique compuesto: una base por fecha+sede
            $table->unique(['fecha', 'sede']);
        });
    }

    public function down(): void
    {
        Schema::table('cash_bases', function (Blueprint $table) {
            $table->dropUnique(['fecha', 'sede']);
            $table->unique(['fecha']);
        });
    }
};
