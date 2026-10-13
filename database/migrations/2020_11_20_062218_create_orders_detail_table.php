<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateOrdersDetailTable extends Migration
{

    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('orders_detail', function (Blueprint $table)
        {
            $table->increments('id');
            $table->integer('order_id')->nullable()->comment("FK = orders");
            $table->integer('product_id')->nullable()->comment("FK = admin_products");
            $table->float('product_price', 10, 2);
            $table->integer('quantity');
            $table->integer('size_id')->nullable();
            $table->float('total_price', 10, 2);
            $table->text('note')->nullable();
            $table->integer('order_status')->nullable();

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
        Schema::dropIfExists('orders_detail');
    }

}
