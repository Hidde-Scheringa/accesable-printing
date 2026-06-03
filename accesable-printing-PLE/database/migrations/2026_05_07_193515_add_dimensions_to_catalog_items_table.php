<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up()
    {
        Schema::table('catalog_items', function (Blueprint $table) {
            // We voegen één JSON kolom toe waarin we {x, y, z, volume} opslaan
            $table->json('dimensions')->nullable()->after('stl_files');
        });
    }

    public function down()
    {
        Schema::table('catalog_items', function (Blueprint $table) {
            $table->dropColumn('dimensions');
        });
    }
};
