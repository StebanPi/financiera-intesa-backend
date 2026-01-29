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
        Schema::table('initial_balances', function (Blueprint $table) {
            $table->string('sede')->default('BARRANCABERMEJA')->after('start_date');
        });

        Schema::table('cash_bases', function (Blueprint $table) {
            $table->string('sede')->default('BARRANCABERMEJA')->after('fecha');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('initial_balances', function (Blueprint $table) {
            $table->dropColumn('sede');
        });

        Schema::table('cash_bases', function (Blueprint $table) {
            $table->dropColumn('sede');
        });
    }
};
