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
        if (!Schema::hasTable('organizacion_user')) {
            Schema::create('organizacion_user', function (Blueprint $table) {
                $table->id();
                $table->foreignId('user_id')->constrained('users')->onDelete('cascade');
                $table->foreignId('organizacion_id')->constrained('organizaciones')->onDelete('cascade');
                $table->timestamps();

                $table->unique(['user_id', 'organizacion_id']);
            });
        }

        // Copiar las relaciones existentes de la columna `users.organizacion_id`
        try {
            $existingUsers = DB::table('users')->whereNotNull('organizacion_id')->get();
            foreach ($existingUsers as $user) {
                DB::table('organizacion_user')->insert([
                    'user_id' => $user->id,
                    'organizacion_id' => $user->organizacion_id,
                    'created_at' => now(),
                    'updated_at' => now(),
                ]);
            }
        } catch (\Exception $e) {
            // Silencioso por si no existen registros o la tabla no tiene la estructura esperada
        }
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('organizacion_user');
    }
};
