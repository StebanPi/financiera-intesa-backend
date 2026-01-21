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
        Schema::table('institution_settings', function (Blueprint $table) {
            if (!Schema::hasColumn('institution_settings', 'logo_path')) {
                $table->string('logo_path')->nullable()->after('name')->comment('Ruta del logo de la institución');
            }
            if (!Schema::hasColumn('institution_settings', 'institucion_subtitulo')) {
                $table->string('institucion_subtitulo')->nullable()->after('logo_path')->comment('Nombre comercial o subtítulo (ej: INSTITUTO TÉCNICO DEL SABER o INTESA)');
            }
            if (!Schema::hasColumn('institution_settings', 'sede')) {
                $table->string('sede')->nullable()->after('institucion_subtitulo')->comment('Sede de la institución (ej: Sede Barrancabermeja)');
            }
            if (!Schema::hasColumn('institution_settings', 'telefono2')) {
                $table->string('telefono2')->nullable()->after('phone')->comment('Segundo teléfono de contacto');
            }
            if (!Schema::hasColumn('institution_settings', 'telefono3')) {
                $table->string('telefono3')->nullable()->after('telefono2')->comment('Tercer teléfono de contacto');
            }
            if (!Schema::hasColumn('institution_settings', 'footer_firma')) {
                $table->string('footer_firma')->nullable()->after('footer_mostrar_ubicacion_fecha')->comment('Firma del footer (ej: by IngELopez)');
            }
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('institution_settings', function (Blueprint $table) {
            $table->dropColumn([
                'logo_path',
                'institucion_subtitulo',
                'sede',
                'telefono2',
                'telefono3',
                'footer_firma'
            ]);
        });
    }
};
