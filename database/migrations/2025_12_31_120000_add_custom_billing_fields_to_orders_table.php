<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class AddCustomBillingFieldsToOrdersTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('orders', function (Blueprint $table) {
            $table->unsignedBigInteger('custom_billing_country_id')->nullable()->after('billing_address_id');
            $table->string('custom_billing_city')->nullable()->after('custom_billing_country_id');
            $table->string('custom_billing_block')->nullable()->after('custom_billing_city');
            $table->string('custom_billing_street')->nullable()->after('custom_billing_block');
            $table->string('custom_billing_house_number')->nullable()->after('custom_billing_street');
            $table->string('custom_billing_floor')->nullable()->after('custom_billing_house_number');
            $table->text('custom_billing_address_detail')->nullable()->after('custom_billing_floor');
            $table->string('custom_billing_address_url')->nullable()->after('custom_billing_address_detail');
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
            $table->dropColumn([
                'custom_billing_country_id',
                'custom_billing_city',
                'custom_billing_block',
                'custom_billing_street',
                'custom_billing_house_number',
                'custom_billing_floor',
                'custom_billing_address_detail',
                'custom_billing_address_url'
            ]);
        });
    }
}
