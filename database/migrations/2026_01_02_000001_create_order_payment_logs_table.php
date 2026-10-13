<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateOrderPaymentLogsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('order_payment_logs', function (Blueprint $table) {
            $table->bigIncrements('id');
            $table->unsignedBigInteger('order_id');
            $table->unsignedBigInteger('coupon_code_id')->nullable();
            $table->decimal('coupon_discount_amount', 12, 3)->default(0);
            $table->decimal('total_amount', 14, 3)->default(0);
            $table->decimal('due_amount', 14, 3)->default(0);
            $table->decimal('final_amount', 14, 3)->default(0);
            $table->unsignedBigInteger('user_id')->nullable();
            $table->text('meta')->nullable();
            $table->timestamps();

            $table->index('order_id');
            $table->index('user_id');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('order_payment_logs');
    }
}
