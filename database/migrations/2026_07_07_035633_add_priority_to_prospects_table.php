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
            $table->enum('priority', ['alfa', 'bravo', 'charlie'])->default('charlie')->after('estado_contacto');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('prospectos_scrapping', function (Blueprint $table) {
            $table->dropColumn('priority');
        });
    }
};
