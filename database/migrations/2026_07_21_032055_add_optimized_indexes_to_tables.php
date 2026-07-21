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
            // Índices de filtrado simple (B-Tree)
            if (Schema::hasColumn('prospectos_scrapping', 'estado_contacto')) {
                $table->index('estado_contacto', 'prospectos_estado_contacto_idx');
            }
            if (Schema::hasColumn('prospectos_scrapping', 'priority')) {
                $table->index('priority', 'prospectos_priority_idx');
            }
            if (Schema::hasColumn('prospectos_scrapping', 'giro_negocio')) {
                $table->index('giro_negocio', 'prospectos_giro_negocio_idx');
            }
            if (Schema::hasColumn('prospectos_scrapping', 'fuente_descubrimiento')) {
                $table->index('fuente_descubrimiento', 'prospectos_fuente_descubrimiento_idx');
            }
            if (Schema::hasColumn('prospectos_scrapping', 'vacantes_activas')) {
                $table->index('vacantes_activas', 'prospectos_vacantes_activas_idx');
            }

            // Índice FULLTEXT para búsquedas de texto optimizadas
            $table->fullText(['empresa', 'director_nombre'], 'prospectos_search_fulltext');
        });

        Schema::table('configuraciones_extraccion', function (Blueprint $table) {
            if (Schema::hasColumn('configuraciones_extraccion', 'estado')) {
                $table->index('estado', 'configs_estado_idx');
            }
        });

        Schema::table('organizacion_user', function (Blueprint $table) {
            if (Schema::hasColumn('organizacion_user', 'organizacion_id')) {
                $table->index('organizacion_id', 'org_user_org_idx');
            }
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('prospectos_scrapping', function (Blueprint $table) {
            $table->dropIndex('prospectos_estado_contacto_idx');
            $table->dropIndex('prospectos_priority_idx');
            $table->dropIndex('prospectos_giro_negocio_idx');
            $table->dropIndex('prospectos_fuente_descubrimiento_idx');
            $table->dropIndex('prospectos_vacantes_activas_idx');
            $table->dropFullText('prospectos_search_fulltext');
        });

        Schema::table('configuraciones_extraccion', function (Blueprint $table) {
            $table->dropIndex('configs_estado_idx');
        });

        Schema::table('organizacion_user', function (Blueprint $table) {
            $table->dropIndex('org_user_org_idx');
        });
    }
};
