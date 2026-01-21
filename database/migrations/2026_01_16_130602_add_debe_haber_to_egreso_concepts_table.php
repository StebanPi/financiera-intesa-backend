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
            if (!Schema::hasColumn('egreso_concepts', 'debe')) {
                $table->unsignedBigInteger('debe')->nullable()->after('state');
            }
            if (!Schema::hasColumn('egreso_concepts', 'haber')) {
                $table->unsignedBigInteger('haber')->nullable()->after('debe');
            }
            
            // Agregar foreign keys
            if (!Schema::hasColumn('egreso_concepts', 'debe') || !Schema::hasIndex('egreso_concepts', 'egreso_concepts_debe_foreign')) {
                $table->foreign('debe')->references('id')->on('debes')->onDelete('set null');
            }
            if (!Schema::hasColumn('egreso_concepts', 'haber') || !Schema::hasIndex('egreso_concepts', 'egreso_concepts_haber_foreign')) {
                $table->foreign('haber')->references('id')->on('habers')->onDelete('set null');
            }
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('egreso_concepts', function (Blueprint $table) {
            if (Schema::hasColumn('egreso_concepts', 'haber')) {
                $table->dropForeign(['haber']);
                $table->dropColumn('haber');
            }
            if (Schema::hasColumn('egreso_concepts', 'debe')) {
                $table->dropForeign(['debe']);
                $table->dropColumn('debe');
            }
        });
    }
};
