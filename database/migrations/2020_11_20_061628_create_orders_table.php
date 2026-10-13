<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateOrdersTable extends Migration
{

    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('orders', function (Blueprint $table)
        {
            $table->increments('id');
            $table->integer('user_id')->nullable()->comment("FK = users");
            $table->integer('shipping_address_id')->nullable()->comment("FK = user_address");
            $table->integer('billing_address_id')->nullable()->comment("FK = user_address");
            $table->string('payment_method_id')->comment("FK = payment_method");
            $table->string('payment_method');
            $table->string('transaction_id')->nullable();
            $table->string('transaction_status');
            $table->longText('tracking_number')->nullable();
            $table->string('order_status');
            $table->string('order_price');
            $table->float('delivery_price', 10, 3)->nullable();
            $table->float('discount_per', 10, 3)->nullable();
            $table->float('discount', 10, 3)->nullable();
            $table->text('coupon_code')->nullable();
            $table->enum('coupon_discount_in', ['P', 'F'])->comment("P=Percentage,F=Flat")->nullable();
            $table->float('coupon_discount', 10, 3)->nullable();
            $table->float('coupon_discount_amount', 10, 3)->nullable();
            $table->float('tax', 10, 3)->nullable();
            $table->string('currency_code')->nullable();
            $table->double('currency_rate')->nullalbe();
            $table->float('order_total', 10, 3);
            $table->integer('courier_id')->nullable();
            $table->boolean('cancel_return_flag')->default(false);
            $table->longText('payment_response')->nullable();
            $table->longText('user_agent_name')->nullable();
            /*
             * Default
             */
            $table->boolean('is_active')->default('1');
            $table->timestamps();
            $table->softDeletes('deleted_at', 0);
            $table->integer('created_by')->nullable();
            $table->integer('updated_by')->nullable();
            $table->integer('deleted_by')->nullable();
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('orders');
    }

}
