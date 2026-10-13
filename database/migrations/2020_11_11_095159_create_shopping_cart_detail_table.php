<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateShoppingCartDetailTable extends Migration
{

    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('shopping_cart_detail', function (Blueprint $table)
        {
            $table->increments('id');
            $table->integer('shopping_cart_id')->nullable()->comment("FK = shopping_cart");
            $table->integer('user_id');
            $table->integer('product_id');
            $table->integer('size_id')->nullable();
            $table->integer('quantity');
            $table->text('note')->nullable();


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
        Schema::dropIfExists('shopping_cart_detail');
    }

}
