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
            // Voeg de kolom toe waarin we de annuleringsinformatie opslaan
            $table->text('cancellation_details')->nullable();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('requests', function (Blueprint $table) {
            // Verwijder de kolom als we de migratie terugdraaien
            $table->dropColumn('cancellation_details');
        });
    }
};
