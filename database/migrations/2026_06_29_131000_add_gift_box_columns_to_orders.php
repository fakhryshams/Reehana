<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;

class AddGiftBoxColumnsToOrders extends Migration
{
    public function up()
    {
        Schema::table('orders', function (Blueprint $table) {
            $table->boolean('is_box')->default(false)->after('is_gift');
            $table->decimal('gift_box', 15, 3)->nullable()->after('gift_wrap');
            $table->string('gift_box_title')->nullable()->after('gift_message');
            $table->string('gift_box_message')->nullable()->after('gift_box_title');
        });
    }

    public function down()
    {
        Schema::table('orders', function (Blueprint $table) {
            $table->dropColumn(['is_box', 'gift_box', 'gift_box_title', 'gift_box_message']);
        });
    }
}
