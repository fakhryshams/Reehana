<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateAdminCouponCodeTable extends Migration
{

    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('admin_coupon_code', function (Blueprint $table)
        {
            $table->increments('id');
            $table->unsignedInteger('user_id');
            $table->string('email');
            $table->unsignedInteger('coupon_id')->nullable();
            $table->text('coupon_code');
            $table->enum('coupon_discount_in', ['P', 'F'])->comment("P=Percentage,F=Flat")->default("P");
            $table->float('coupon_discount', 10, 3);
            $table->longText('coupon_description')->nullable();
            $table->date('from_date');
            $table->date('to_date');
            $table->boolean('allow_multiple')->default('0');
            $table->boolean('allow_all')->default('0');
            $table->boolean('is_applied')->default('0');
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
        Schema::dropIfExists('admin_coupon_code');
    }

}
