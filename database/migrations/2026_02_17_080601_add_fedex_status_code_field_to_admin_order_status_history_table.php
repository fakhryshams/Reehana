<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class AddFedexStatusCodeFieldToAdminOrderStatusHistoryTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('admin_order_status_history', function (Blueprint $table) {
            //
            $table->string('fedex_status_code')->nullable()->after('type');
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
                'fedex_status_code'
            ]);
        });
    }
}
