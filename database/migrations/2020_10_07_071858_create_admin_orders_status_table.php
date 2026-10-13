<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateAdminOrdersStatusTable extends Migration
{

    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('admin_orders_status', function (Blueprint $table)
        {
            $table->increments('id');
            $table->integer('sorting')->nullable()->default(0);
            $table->string('order_status')->nullable()->default(0);
            $table->boolean('show_in_list')->default('1');
            $table->boolean('show_on_cancel_return')->default('0');
            $table->boolean('is_default')->default('0');
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
        Schema::dropIfExists('admin_orders_status');
    }

}
