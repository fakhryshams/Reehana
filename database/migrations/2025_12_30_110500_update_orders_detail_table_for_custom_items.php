<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class UpdateOrdersDetailTableForCustomItems extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('orders_detail', function (Blueprint $table) {
            $table->string('product_name')->nullable()->after('product_id')->comment('Stores name for custom items or snapshot of product name');
            $table->text('custom_attributes')->nullable()->after('note')->comment('JSON string for custom attributes like color, size, description');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::table('orders_detail', function (Blueprint $table) {
            $table->dropColumn(['product_name', 'custom_attributes']);
        });
    }
}
