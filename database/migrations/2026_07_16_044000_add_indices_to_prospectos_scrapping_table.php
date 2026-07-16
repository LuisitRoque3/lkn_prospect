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
            // Añadir índice a creado_at para optimizar el ordenamiento por fecha
            $table->index('creado_at', 'prospectos_creado_at_index');
            
            // Añadir índice compuesto (organizacion_id, creado_at) para optimizar la query principal del CRM
            $table->index(['organizacion_id', 'creado_at'], 'prospectos_org_creado_at_composite_index');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('prospectos_scrapping', function (Blueprint $table) {
            $table->dropIndex('prospectos_creado_at_index');
            $table->dropIndex('prospectos_org_creado_at_composite_index');
        });
    }
};
