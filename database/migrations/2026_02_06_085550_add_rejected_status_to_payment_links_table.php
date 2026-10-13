<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

use Illuminate\Support\Facades\DB;

class AddRejectedStatusToPaymentLinksTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('payment_links', function (Blueprint $table) {
            DB::statement("ALTER TABLE payment_links MODIFY COLUMN status ENUM('pending', 'paid', 'failed', 'expired', 'rejected') DEFAULT 'pending'");
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
            DB::statement("ALTER TABLE payment_links MODIFY COLUMN status ENUM('pending', 'paid', 'failed', 'expired') DEFAULT 'pending'");
        });
    }
}
