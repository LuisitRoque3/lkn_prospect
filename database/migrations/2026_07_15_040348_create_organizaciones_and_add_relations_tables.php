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
        if (!Schema::hasTable('organizaciones')) {
            Schema::create('organizaciones', function (Blueprint $table) {
                $table->id();
                $table->string('nombre');
                $table->timestamps();
            });
        }

        Schema::table('users', function (Blueprint $table) {
            if (!Schema::hasColumn('users', 'organizacion_id')) {
                $table->unsignedBigInteger('organizacion_id')->nullable()->after('is_admin');
                $table->foreign('organizacion_id')->references('id')->on('organizaciones')->onDelete('set null');
            }
        });

        Schema::table('prospectos_scrapping', function (Blueprint $table) {
            if (!Schema::hasColumn('prospectos_scrapping', 'organizacion_id')) {
                $table->unsignedBigInteger('organizacion_id')->nullable()->after('user_id');
                $table->foreign('organizacion_id')->references('id')->on('organizaciones')->onDelete('set null');
            }
        });

        Schema::table('configuraciones_extraccion', function (Blueprint $table) {
            if (!Schema::hasColumn('configuraciones_extraccion', 'organizacion_id')) {
                $table->unsignedBigInteger('organizacion_id')->nullable()->after('user_id');
                $table->foreign('organizacion_id')->references('id')->on('organizaciones')->onDelete('cascade');
            }
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('configuraciones_extraccion', function (Blueprint $table) {
            if (Schema::hasColumn('configuraciones_extraccion', 'organizacion_id')) {
                $table->dropForeign(['organizacion_id']);
                $table->dropColumn('organizacion_id');
            }
        });

        Schema::table('prospectos_scrapping', function (Blueprint $table) {
            if (Schema::hasColumn('prospectos_scrapping', 'organizacion_id')) {
                $table->dropForeign(['organizacion_id']);
                $table->dropColumn('organizacion_id');
            }
        });

        Schema::table('users', function (Blueprint $table) {
            if (Schema::hasColumn('users', 'organizacion_id')) {
                $table->dropForeign(['organizacion_id']);
                $table->dropColumn('organizacion_id');
            }
        });

        Schema::dropIfExists('organizaciones');
    }
};
