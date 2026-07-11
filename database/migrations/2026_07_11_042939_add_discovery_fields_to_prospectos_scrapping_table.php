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
            if (!Schema::hasColumn('prospectos_scrapping', 'fuente_descubrimiento')) {
                $table->string('fuente_descubrimiento')->nullable()->default('maps')->after('telefono_whatsapp');
            }
            if (!Schema::hasColumn('prospectos_scrapping', 'vacantes_activas')) {
                $table->boolean('vacantes_activas')->default(false)->after('fuente_descubrimiento');
            }
            if (!Schema::hasColumn('prospectos_scrapping', 'puestos_buscados')) {
                $table->text('puestos_buscados')->nullable()->after('vacantes_activas');
            }
            if (!Schema::hasColumn('prospectos_scrapping', 'tamano_empresa')) {
                $table->string('tamano_empresa')->nullable()->after('puestos_buscados');
            }
            if (!Schema::hasColumn('prospectos_scrapping', 'origen_detalles')) {
                $table->text('origen_detalles')->nullable()->after('tamano_empresa');
            }
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('prospectos_scrapping', function (Blueprint $table) {
            $columns = [];
            if (Schema::hasColumn('prospectos_scrapping', 'fuente_descubrimiento')) $columns[] = 'fuente_descubrimiento';
            if (Schema::hasColumn('prospectos_scrapping', 'vacantes_activas')) $columns[] = 'vacantes_activas';
            if (Schema::hasColumn('prospectos_scrapping', 'puestos_buscados')) $columns[] = 'puestos_buscados';
            if (Schema::hasColumn('prospectos_scrapping', 'tamano_empresa')) $columns[] = 'tamano_empresa';
            if (Schema::hasColumn('prospectos_scrapping', 'origen_detalles')) $columns[] = 'origen_detalles';
            
            if (!empty($columns)) {
                $table->dropColumn($columns);
            }
        });
    }
};
