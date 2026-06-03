<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */

    public function up()
    {
        Schema::table('requests', function (Blueprint $table) {
            // ID van de Stripe sessie om de betaling te verifiëren
            $table->string('stripe_checkout_id')->nullable()->after('status');
            // De betaalstatus: unpaid, pending, paid
            $table->string('payment_status')->default('unpaid')->after('stripe_checkout_id');
        });
    }

    /**
     * Reverse the migrations.
     */

    public function down()
    {
        Schema::table('requests', function (Blueprint $table) {
            $table->dropColumn(['stripe_checkout_id', 'payment_status']);
        });
    }
};
