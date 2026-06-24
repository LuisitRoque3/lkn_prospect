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
        Schema::create('prospectos_scrapping', function (Blueprint $table) {
            $table->id();
            $table->string('empresa')->nullable();
            $table->string('director_nombre')->nullable();
            $table->string('correo_corporativo')->nullable();
            $table->string('telefono_whatsapp')->nullable();
            $table->string('estado_contacto')->nullable()->default('Nuevo');
            $table->timestamp('creado_at')->nullable();
            $table->timestamp('actualizado_at')->nullable();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('prospectos_scrapping');
    }
};
