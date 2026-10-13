<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateTemporaryOrderDetailsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('temporary_order_details', function (Blueprint $table) {
            $table->bigIncrements('id');
            $table->unsignedBigInteger('temporary_order_id')->index();
            $table->unsignedBigInteger('product_id')->nullable()->index();
            $table->string('product_name')->nullable();
            $table->string('item_code')->nullable();
            $table->text('description')->nullable();
            $table->string('image')->nullable();
            $table->string('color')->nullable();
            $table->string('size')->nullable();
            $table->decimal('price', 10, 3)->default(0);
            $table->integer('quantity')->default(1);
            $table->decimal('total_price', 10, 3)->default(0);
            $table->text('custom_attributes')->nullable()->comment('JSON for any extra customized properties');
            $table->integer('sort_order')->default(0);
            
            $table->unsignedBigInteger('created_by')->nullable();
            $table->unsignedBigInteger('updated_by')->nullable();
            $table->unsignedBigInteger('deleted_by')->nullable();
            $table->timestamps();
            $table->softDeletes();
            
            $table->foreign('temporary_order_id')->references('id')->on('temporary_orders')->onDelete('cascade');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('temporary_order_details');
    }
}
