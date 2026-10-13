<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class AddPickupFieldsToOrdersTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('orders', function (Blueprint $table) {
            //
            $table->string('pickup_confirmation_code')->nullable()->after('delivered_at');
            $table->string('pickup_location')->nullable()->after('pickup_confirmation_code');
            $table->string('pickup_status')->nullable()->after('pickup_location');
            $table->json('pickup_response')->nullable()->after('pickup_status');
            $table->timestamp('pickup_requested_at')->nullable()->after('pickup_response');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::table('orders', function (Blueprint $table) {
            //
            $table->dropColumn([
                'pickup_confirmation_code',
                'pickup_location',
                'pickup_status',
                'pickup_response',
                'pickup_requested_at'
            ]);
        });
    }
}
