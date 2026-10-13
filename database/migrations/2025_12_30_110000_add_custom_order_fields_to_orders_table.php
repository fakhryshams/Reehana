<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class AddCustomOrderFieldsToOrdersTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('orders', function (Blueprint $table) {
            $table->unsignedBigInteger('parent_id')->nullable()->after('id');
            $table->string('order_type')->default('regular')->after('order_status'); // regular, custom
            $table->decimal('paid_amount', 10, 3)->default(0)->after('order_total');
            $table->decimal('pending_amount', 10, 3)->default(0)->after('paid_amount');
            $table->text('custom_order_details')->nullable()->after('pending_amount');
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
            $table->dropColumn(['parent_id', 'order_type', 'paid_amount', 'pending_amount', 'custom_order_details']);
        });
    }
}
