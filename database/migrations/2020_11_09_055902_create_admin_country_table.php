<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateAdminCountryTable extends Migration
{

    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('admin_country', function (Blueprint $table)
        {
            $table->increments('id');
            $table->integer('sorting')->nullable();
            $table->text('shipping_charge')->nullable();
            $table->string('country_code')->nullable();
            $table->string('currency_code')->nullable();
            $table->double('currency_rate')->nullable();
            $table->string('is_default')->default('0');
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
        Schema::dropIfExists('admin_country');
    }

}
