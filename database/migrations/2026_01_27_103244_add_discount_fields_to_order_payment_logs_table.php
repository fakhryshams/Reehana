<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class AddDiscountFieldsToOrderPaymentLogsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('order_payment_logs', function (Blueprint $table) {
            $table->string('discount_type')->nullable()->comment('flat/percentage');
            $table->string('discount_per')->nullable();
            $table->decimal('discount_value', 10, 2)->default(0);
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::table('order_payment_logs', function (Blueprint $table) {
            //
            $table->dropColumn('discount_type');
            $table->dropColumn('discount_per');
            $table->dropColumn('discount_value');
        });
    }
}
