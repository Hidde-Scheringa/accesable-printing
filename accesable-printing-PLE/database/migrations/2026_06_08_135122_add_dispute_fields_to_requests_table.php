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
        Schema::table('requests', function (Blueprint $table) {
            // We voegen de velden toe na de 'status' kolom om de tabel netjes te houden
            $table->text('defect_reason')->nullable()->after('status');
            $table->string('defect_image_path')->nullable()->after('defect_reason');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('requests', function (Blueprint $table) {
            // Maak de wijzigingen ongedaan als we de migratie terugdraaien
            $table->dropColumn(['defect_reason', 'defect_image_path']);
        });
    }
};
