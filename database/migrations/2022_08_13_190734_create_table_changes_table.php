<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateTableChangesTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('table_changes', function (Blueprint $table) {
            $table->id();
            $table->string('table');
            $table->string('id_change');
            $table->boolean('add');
            $table->boolean('edit');
            $table->boolean('delete');
            $table->boolean('is_synchronized');
            $table->string('latitude');
            $table->string('longitude');
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('table_changes');
    }
}
