<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('concept_discharge_receipts', function (Blueprint $table) {
            $table->string('sede')->default('BARRANCABERMEJA')->after('id');
        });
    }

    public function down(): void
    {
        Schema::table('concept_discharge_receipts', function (Blueprint $table) {
            $table->dropColumn('sede');
        });
    }
};
