<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreatePaymentLinksTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('payment_links', function (Blueprint $table) {
            $table->id();

            $table->unsignedBigInteger('order_id');
            $table->string('payment_link');
            $table->decimal('amount', 10, 3);

            // Who generated it
            $table->enum('generated_from', ['admin', 'system'])->default('admin');
            $table->unsignedBigInteger('generated_by')->nullable(); // admin user id

            // Payment tracking
            $table->enum('status', ['pending', 'paid', 'failed', 'expired'])->default('pending');
            $table->decimal('paid_amount', 10, 3)->default(0);
            $table->timestamp('paid_at')->nullable();

            // Gateway response
            $table->json('gateway_response')->nullable();

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
        Schema::dropIfExists('payment_links');
    }
}
