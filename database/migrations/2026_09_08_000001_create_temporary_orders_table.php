<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateTemporaryOrdersTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('temporary_orders', function (Blueprint $table) {
            $table->bigIncrements('id');
            $table->unsignedBigInteger('user_id')->index();
            $table->string('type', 50)->default('purchase_order')->index()->comment('purchase_order or quotation');
            $table->string('custom_order_number', 100)->nullable()->index();
            $table->string('status', 50)->default('pending')->index()->comment('pending, completed, cancelled, rejected, expired');
            
            // Customer Details
            $table->string('customer_name')->nullable();
            $table->string('customer_phone')->nullable();
            $table->string('customer_email')->nullable();
            
            // Address Details
            $table->unsignedBigInteger('country_id')->nullable();
            $table->unsignedBigInteger('city_id')->nullable();
            $table->string('city')->nullable();
            $table->string('block')->nullable();
            $table->string('street')->nullable();
            $table->string('house_number')->nullable();
            $table->string('floor')->nullable();
            $table->text('address_detail')->nullable();
            $table->string('address_url')->nullable();
            $table->string('address_title_text')->nullable();
            $table->unsignedBigInteger('customer_address_id')->nullable();
            $table->text('note')->nullable();
            
            // Financial Details
            $table->decimal('subtotal', 10, 3)->default(0);
            $table->decimal('shipping_price', 10, 3)->default(0);
            $table->string('discount_type', 20)->nullable()->comment('flat or percentage');
            $table->decimal('discount_val', 10, 3)->default(0);
            $table->decimal('discount_amount', 10, 3)->default(0);
            $table->decimal('grand_total', 10, 3)->default(0);
            $table->decimal('advance_price', 10, 3)->default(0);
            $table->decimal('pending_amount', 10, 3)->default(0);
            
            // Payment & Snapshot Meta
            $table->string('payment_type', 50)->nullable();
            $table->longText('custom_order_details')->nullable();
            
            $table->unsignedBigInteger('created_by')->nullable();
            $table->unsignedBigInteger('updated_by')->nullable();
            $table->unsignedBigInteger('deleted_by')->nullable();
            $table->timestamps();
            $table->softDeletes();
            
            $table->foreign('user_id')->references('id')->on('users')->onDelete('cascade');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('temporary_orders');
    }
}
