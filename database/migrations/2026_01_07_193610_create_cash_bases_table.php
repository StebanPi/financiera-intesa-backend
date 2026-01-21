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
        Schema::create('cash_bases', function (Blueprint $table) {
            $table->id();
            $table->date('fecha')->unique();
            $table->decimal('base_efectivo', 15, 2)->default(0);
            $table->decimal('base_banco', 15, 2)->default(0);
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('cash_bases');
    }
};
