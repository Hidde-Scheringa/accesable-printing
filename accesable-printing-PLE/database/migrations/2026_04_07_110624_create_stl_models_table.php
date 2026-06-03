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
        Schema::create('catalog_items', function (Blueprint $table) {
            $table->id();

            // Basis informatie
            $table->string('title');
            $table->string('slug')->unique(); // Voor SEO vriendelijke URL's: /catalogus/dragon-miniature
            $table->text('description')->nullable();
            $table->string('category')->nullable(); // Bijv: Monsters, Terrain, Heroes

            // Eigenschappen van het model
            $table->decimal('price', 8, 2)->nullable(); // Optioneel: prijs als je direct verkoopt

            // De bestanden (zelfde structuur als je requests)
            // Hierin sla je de JSON op met paden naar de STL previews
            $table->json('stl_files')->nullable();

            // Status & Zichtbaarheid
            $table->boolean('is_active')->default(true);
            $table->boolean('is_featured')->default(false); // Om items bovenaan te zetten

            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('catalog_items');
    }
};
