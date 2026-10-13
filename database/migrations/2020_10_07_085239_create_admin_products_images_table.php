<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateAdminProductsImagesTable extends Migration
{

    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('admin_products_images', function (Blueprint $table)
        {
            $table->increments('id');
            $table->unsignedInteger('products_id')->comment('FK = admin_products');
            $table->longText('image_url');
            $table->longText('image_thumb_url')->nullable();
            $table->longText('image_cart_url')->nullable();
            $table->boolean('is_video')->default('0');
            $table->boolean('is_default')->default(false);
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
        Schema::dropIfExists('admin_products_images');
    }

}
