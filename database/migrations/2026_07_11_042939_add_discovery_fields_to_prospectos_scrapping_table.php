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
        Schema::table('prospectos_scrapping', function (Blueprint $table) {
            $table->string('fuente_descubrimiento')->nullable()->default('maps')->after('telefono_whatsapp');
            $table->boolean('vacantes_activas')->default(false)->after('fuente_descubrimiento');
            $table->text('puestos_buscados')->nullable()->after('vacantes_activas');
            $table->string('tamano_empresa')->nullable()->after('puestos_buscados');
            $table->text('origen_detalles')->nullable()->after('tamano_empresa');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('prospectos_scrapping', function (Blueprint $table) {
            $table->dropColumn([
                'fuente_descubrimiento',
                'vacantes_activas',
                'puestos_buscados',
                'tamano_empresa',
                'origen_detalles'
            ]);
        });
    }
};
