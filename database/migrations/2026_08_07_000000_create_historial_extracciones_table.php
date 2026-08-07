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
        Schema::create('historial_extracciones', function (Blueprint $table) {
            $table->id();
            $table->string('tipo')->default('automatica'); // 'automatica' o 'manual'
            $table->text('giro');
            $table->text('ciudad');
            $table->integer('leads_encontrados')->default(0);
            $table->integer('leads_nuevos')->default(0);
            $table->string('estado')->default('ejecutando'); // 'ejecutando', 'completado', 'error'
            $table->text('error_mensaje')->nullable();
            $table->unsignedBigInteger('organizacion_id')->nullable();
            $table->foreign('organizacion_id')->references('id')->on('organizaciones')->onDelete('set null');
            $table->timestamp('created_at')->useCurrent();
            $table->timestamp('updated_at')->useCurrent()->useCurrentOnUpdate();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('historial_extracciones');
    }
};
