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
        Schema::table('egreso_concepts', function (Blueprint $table) {
            if (!Schema::hasColumn('egreso_concepts', 'state')) {
                $table->boolean('state')->default(true)->after('descripcion');
            }
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('egreso_concepts', function (Blueprint $table) {
            if (Schema::hasColumn('egreso_concepts', 'state')) {
                $table->dropColumn('state');
            }
        });
    }
};
