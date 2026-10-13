<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class AddTypeFieldToAdminOrderStatusHistoryTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('admin_order_status_history', function (Blueprint $table) {
            $table->string('type')->nullable()->after('order_status_id');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::table('admin_order_status_history', function (Blueprint $table) {
            //
            $table->dropColumn([
                'type'
            ]);
        });
    }
}
