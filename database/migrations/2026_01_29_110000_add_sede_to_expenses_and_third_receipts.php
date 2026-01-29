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
        Schema::table('egreso_receipts', function (Blueprint $table) {
            if (!Schema::hasColumn('egreso_receipts', 'sede')) {
                $table->string('sede')->default('BARRANCABERMEJA')->after('valor');
            }
        });

        Schema::table('third_receipts', function (Blueprint $table) {
            if (!Schema::hasColumn('third_receipts', 'sede')) {
                $table->string('sede')->default('BARRANCABERMEJA')->after('valor');
            }
        });

        Schema::table('entries', function (Blueprint $table) {
            if (!Schema::hasColumn('entries', 'sede')) {
                $table->string('sede')->default('BARRANCABERMEJA')->after('valor');
            }
        });

        Schema::table('other_entries', function (Blueprint $table) {
            if (!Schema::hasColumn('other_entries', 'sede')) {
                $table->string('sede')->default('BARRANCABERMEJA')->after('valor');
            }
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('egreso_receipts', function (Blueprint $table) {
            if (Schema::hasColumn('egreso_receipts', 'sede')) {
                $table->dropColumn('sede');
            }
        });

        Schema::table('third_receipts', function (Blueprint $table) {
            if (Schema::hasColumn('third_receipts', 'sede')) {
                $table->dropColumn('sede');
            }
        });

        Schema::table('entries', function (Blueprint $table) {
            if (Schema::hasColumn('entries', 'sede')) {
                $table->dropColumn('sede');
            }
        });

        Schema::table('other_entries', function (Blueprint $table) {
            if (Schema::hasColumn('other_entries', 'sede')) {
                $table->dropColumn('sede');
            }
        });
    }
};
