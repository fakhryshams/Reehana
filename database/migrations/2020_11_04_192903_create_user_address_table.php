<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateUserAddressTable extends Migration
{

    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('user_address', function (Blueprint $table)
        {
            $table->increments('id');
            $table->unsignedInteger('user_id')->comment('FK = users');
            $table->string('address_title')->nullable();
            $table->string('first_name');
            $table->string('last_name');
            $table->string('email');
            $table->string('country_id');
            $table->string('city_id')->nullable();
            $table->string('city')->nullable();
            $table->string('area')->nullable();
            $table->string('block')->nullable();
            $table->string('street')->nullable();
            $table->string('house_number')->nullable();
            $table->string('floor')->nullable();
            $table->string('appartment')->nullable();
            $table->string('address_detail')->nullable();
            $table->string('phone');
            $table->string('pin_code')->nullable();
            $table->boolean('is_billing_address')->default('0');
            $table->string('billing_first_name')->nullable();
            $table->string('billing_last_name')->nullable();
            $table->string('billing_country_id')->nullable();
            $table->string('billing_city_id')->nullable();
            $table->string('billing_city')->nullable();
            $table->string('billing_area')->nullable();
            $table->string('billing_block')->nullable();
            $table->string('billing_street')->nullable();
            $table->string('billing_house_number')->nullable();
            $table->string('billing_floor')->nullable();
            $table->string('billing_appartment')->nullable();
            $table->string('billing_phone')->nullable();
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
        Schema::dropIfExists('user_address');
    }

}
