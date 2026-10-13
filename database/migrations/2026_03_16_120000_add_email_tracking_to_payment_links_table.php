<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class AddEmailTrackingToPaymentLinksTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('payment_links', function (Blueprint $table) {
            $table->string('customer_email')->nullable();
            $table->unsignedInteger('email_sent_count')->default(0);
            $table->timestamp('last_email_sent_at')->nullable();
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::table('payment_links', function (Blueprint $table) {
            $table->dropColumn(['customer_email', 'email_sent_count', 'last_email_sent_at']);
        });
    }
}
