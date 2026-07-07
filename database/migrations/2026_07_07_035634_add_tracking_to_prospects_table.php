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
        Schema::table('prospects', function (Blueprint $table) {
            $table->string('tracking_uuid')->nullable()->unique()->after('id');
            $table->timestamp('opened_at')->nullable()->after('estado_contacto');
            $table->integer('open_count')->default(0)->after('opened_at');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('prospects', function (Blueprint $table) {
            $table->dropColumn(['tracking_uuid', 'opened_at', 'open_count']);
        });
    }
};
