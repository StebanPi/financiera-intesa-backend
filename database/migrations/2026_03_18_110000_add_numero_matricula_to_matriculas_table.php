<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('matriculas', function (Blueprint $table) {
            $table->unsignedInteger('numero_matricula')->nullable()->after('id');
        });

        // Poblar numero_matricula para registros existentes, secuencial por sede
        $sedes = DB::table('matriculas')->select('sede')->distinct()->pluck('sede');

        foreach ($sedes as $sede) {
            $registros = DB::table('matriculas')
                ->where('sede', $sede)
                ->orderBy('id')
                ->pluck('id');

            foreach ($registros as $index => $id) {
                DB::table('matriculas')
                    ->where('id', $id)
                    ->update(['numero_matricula' => $index + 1]);
            }
        }
    }

    public function down(): void
    {
        Schema::table('matriculas', function (Blueprint $table) {
            $table->dropColumn('numero_matricula');
        });
    }
};
