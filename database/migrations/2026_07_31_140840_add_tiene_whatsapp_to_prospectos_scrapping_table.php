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
            $table->boolean('tiene_whatsapp')->nullable()->default(null)->after('telefono_whatsapp');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('prospectos_scrapping', function (Blueprint $table) {
            $table->dropColumn('tiene_whatsapp');
        });
    }
};
