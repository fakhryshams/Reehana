<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateAdminHomeLatestCollectionTable extends Migration
{

    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('admin_home_latest_collection', function (Blueprint $table)
        {
            $table->increments('id');
            $table->integer('category_id')->nullable()->comment('admin_category');
            $table->integer('sub_category_id')->nullable()->comment('admin_sub_category');
            $table->integer('product_id')->nullable()->comment('admin_product');
            $table->string('image');
            $table->boolean('is_center')->default('0');
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
        Schema::dropIfExists('admin_home_latest_collection');
    }

}
