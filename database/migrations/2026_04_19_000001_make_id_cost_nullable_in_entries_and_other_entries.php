<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class MakeIdCostNullableInEntriesAndOtherEntries extends Migration
{
    public function up(): void
    {
        Schema::table('entries', function (Blueprint $table) {
            $table->bigInteger('id_cost')->unsigned()->nullable()->change();
        });

        Schema::table('other_entries', function (Blueprint $table) {
            $table->bigInteger('id_cost')->unsigned()->nullable()->change();
        });
    }

    public function down(): void
    {
        Schema::table('entries', function (Blueprint $table) {
            $table->bigInteger('id_cost')->unsigned()->nullable(false)->change();
        });

        Schema::table('other_entries', function (Blueprint $table) {
            $table->bigInteger('id_cost')->unsigned()->nullable(false)->change();
        });
    }
}
