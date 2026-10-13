<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class AddShippingFieldsToOrdersTable extends Migration
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

            $table->string('carrier')->nullable()->after('tracking_number');
            $table->string('shipping_service')->nullable()->after('carrier');

            // Shipment status
            $table->string('shipment_status')->nullable()->after('shipping_service');

            // Label
            $table->text('fedex_label_url')->nullable()->after('shipment_status');

            // Dates
            $table->timestamp('shipped_at')->nullable()->after('fedex_label_url');
            $table->timestamp('delivered_at')->nullable()->after('shipped_at');

            // Store full FedEx response (important for debugging)
            $table->json('shipping_response')->nullable()->after('delivered_at');
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
                'carrier',
                'shipping_service',
                'shipment_status',
                'fedex_label_url',
                'shipped_at',
                'delivered_at',
                'shipping_response'
            ]);
        });
    }
}
