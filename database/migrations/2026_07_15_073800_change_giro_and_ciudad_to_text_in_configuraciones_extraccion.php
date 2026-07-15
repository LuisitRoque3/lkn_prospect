<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::table('configuraciones_extraccion', function (Blueprint $table) {
            // Usamos DB::statement para asegurar compatibilidad directa sin dependencias de doctrine/dbal
            DB::statement('ALTER TABLE configuraciones_extraccion MODIFY giro TEXT');
            DB::statement('ALTER TABLE configuraciones_extraccion MODIFY ciudad TEXT');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('configuraciones_extraccion', function (Blueprint $table) {
            DB::statement('ALTER TABLE configuraciones_extraccion MODIFY giro VARCHAR(255)');
            DB::statement('ALTER TABLE configuraciones_extraccion MODIFY ciudad VARCHAR(255)');
        });
    }
};
