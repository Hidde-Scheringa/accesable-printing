<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {

    public function up()
    {
        Schema::table('requests', function (Blueprint $table) {
            // We gebruiken 'after' zodat ze netjes in je tabel staan
            $table->decimal('suggested_refund', 8, 2)->default(0.00)->after('payment_status');
        });
    }

    public function down()
    {
        Schema::table('requests', function (Blueprint $table) {
            $table->dropColumn(['suggested_refund']);
        });
    }
};
