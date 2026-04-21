<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\DB;
return new class extends Migration
{
    public function up()
    {
        DB::statement('ALTER TABLE third_entries MODIFY cedula VARCHAR(255)');
    }

    public function down()
    {
        DB::statement('ALTER TABLE third_entries MODIFY cedula BIGINT');
    }
};
