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
        // 1. Añadir user_id a prospectos_scrapping
        Schema::table('prospectos_scrapping', function (Blueprint $table) {
            if (!Schema::hasColumn('prospectos_scrapping', 'user_id')) {
                $table->foreignId('user_id')->nullable()->after('id')->constrained('users')->onDelete('cascade');
            }
        });

        // 2. Crear tabla configuraciones_extraccion
        if (!Schema::hasTable('configuraciones_extraccion')) {
            Schema::create('configuraciones_extraccion', function (Blueprint $table) {
                $table->id();
                $table->foreignId('user_id')->constrained('users')->onDelete('cascade');
                $table->string('giro');
                $table->string('ciudad');
                $table->boolean('estado')->default(true); // Activa o desactivada
                $table->timestamps();
            });
        }
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('configuraciones_extraccion');
        
        Schema::table('prospectos_scrapping', function (Blueprint $table) {
            if (Schema::hasColumn('prospectos_scrapping', 'user_id')) {
                $table->dropForeign(['user_id']);
                $table->dropColumn('user_id');
            }
        });
    }
};
