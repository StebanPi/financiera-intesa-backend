<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        $db = DB::connection()->getDatabaseName();

        // ── debes ──────────────────────────────────────────────
        $old = DB::selectOne("
            SELECT COUNT(*) AS cnt FROM information_schema.statistics
            WHERE table_schema = ? AND table_name = 'debes'
            AND index_name = 'debes_cuenta_nombre_unique'
        ", [$db]);

        if ($old->cnt > 0) {
            Schema::table('debes', fn (Blueprint $t) =>
                $t->dropUnique('debes_cuenta_nombre_unique')
            );
        }

        $new = DB::selectOne("
            SELECT COUNT(*) AS cnt FROM information_schema.statistics
            WHERE table_schema = ? AND table_name = 'debes'
            AND index_name = 'debes_cuenta_sede_unique'
        ", [$db]);

        if ($new->cnt === 0) {
            Schema::table('debes', fn (Blueprint $t) =>
                $t->unique(['cuenta', 'sede'], 'debes_cuenta_sede_unique')
            );
        }

        // ── habers ─────────────────────────────────────────────
        $old = DB::selectOne("
            SELECT COUNT(*) AS cnt FROM information_schema.statistics
            WHERE table_schema = ? AND table_name = 'habers'
            AND index_name = 'habers_cuenta_nombre_unique'
        ", [$db]);

        if ($old->cnt > 0) {
            Schema::table('habers', fn (Blueprint $t) =>
                $t->dropUnique('habers_cuenta_nombre_unique')
            );
        }

        $new = DB::selectOne("
            SELECT COUNT(*) AS cnt FROM information_schema.statistics
            WHERE table_schema = ? AND table_name = 'habers'
            AND index_name = 'habers_cuenta_sede_unique'
        ", [$db]);

        if ($new->cnt === 0) {
            Schema::table('habers', fn (Blueprint $t) =>
                $t->unique(['cuenta', 'sede'], 'habers_cuenta_sede_unique')
            );
        }
    }

    public function down(): void
    {
        $db = DB::connection()->getDatabaseName();

        // ── debes ──────────────────────────────────────────────
        $new = DB::selectOne("
            SELECT COUNT(*) AS cnt FROM information_schema.statistics
            WHERE table_schema = ? AND table_name = 'debes'
            AND index_name = 'debes_cuenta_sede_unique'
        ", [$db]);

        if ($new->cnt > 0) {
            Schema::table('debes', fn (Blueprint $t) =>
                $t->dropUnique('debes_cuenta_sede_unique')
            );
        }

        $old = DB::selectOne("
            SELECT COUNT(*) AS cnt FROM information_schema.statistics
            WHERE table_schema = ? AND table_name = 'debes'
            AND index_name = 'debes_cuenta_nombre_unique'
        ", [$db]);

        if ($old->cnt === 0) {
            Schema::table('debes', fn (Blueprint $t) =>
                $t->unique(['cuenta', 'nombre'], 'debes_cuenta_nombre_unique')
            );
        }

        // ── habers ─────────────────────────────────────────────
        $new = DB::selectOne("
            SELECT COUNT(*) AS cnt FROM information_schema.statistics
            WHERE table_schema = ? AND table_name = 'habers'
            AND index_name = 'habers_cuenta_sede_unique'
        ", [$db]);

        if ($new->cnt > 0) {
            Schema::table('habers', fn (Blueprint $t) =>
                $t->dropUnique('habers_cuenta_sede_unique')
            );
        }

        $old = DB::selectOne("
            SELECT COUNT(*) AS cnt FROM information_schema.statistics
            WHERE table_schema = ? AND table_name = 'habers'
            AND index_name = 'habers_cuenta_nombre_unique'
        ", [$db]);

        if ($old->cnt === 0) {
            Schema::table('habers', fn (Blueprint $t) =>
                $t->unique(['cuenta', 'nombre'], 'habers_cuenta_nombre_unique')
            );
        }
    }
};
