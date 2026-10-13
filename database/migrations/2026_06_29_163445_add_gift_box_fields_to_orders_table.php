<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class AddGiftBoxFieldsToOrdersTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('orders', function (Blueprint $table) {
            $table->string('gift_box')->nullable()->after('gift_message');
            $table->tinyInteger('is_gift_box')->default(0)->after('gift_box');
            $table->text('gift_box_title')->nullable()->after('is_gift_box');
            $table->text('gift_box_message')->nullable()->after('gift_box_title');
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
            $table->dropColumn(['gift_box', 'is_gift_box', 'gift_box_title', 'gift_box_message']);
        });
    }
}
