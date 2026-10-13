<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateFilterTable extends Migration
{

    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('filter', function (Blueprint $table)
        {
            $table->increments('id');
            $table->unsignedInteger('product_id')->comment("FK=admin_products");
            $table->unsignedInteger('category_id')->comment("FK=admin_categories")->nullable();
            $table->unsignedInteger('sub_category_id')->comment("FK=admin_categories")->nullable();
            $table->unsignedInteger('color_id')->comment("FK=admin_color")->nullable();
            $table->unsignedInteger('size_id')->comment("FK=admin_size")->nullable();
            $table->unsignedInteger('brand_id')->comment("FK=admin_brand")->nullable();
            /*
             * Defaults
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
        Schema::dropIfExists('filter');
    }

}
