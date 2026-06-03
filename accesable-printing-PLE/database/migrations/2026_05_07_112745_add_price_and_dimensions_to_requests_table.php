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
            // We voegen de totaalprijs toe (decimal is beter voor geld dan float)
            // We plaatsen hem na de 'scale' kolom voor de overzichtelijkheid
            $table->decimal('total_price', 10, 2)->after('scale')->default(0.00);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('requests', function (Blueprint $table) {
            $table->dropColumn('total_price');
        });
    }
};
