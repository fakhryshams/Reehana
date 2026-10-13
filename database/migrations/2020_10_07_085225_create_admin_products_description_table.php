<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateAdminProductsDescriptionTable extends Migration
{

    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('admin_products_description', function (Blueprint $table)
        {
            $table->increments('id');
            $table->unsignedInteger('products_id')->comment("FK = admin_products");
            $table->integer('language_id')->comment("FK = admin_languages");
            $table->string('products_name', 255)->index('idx_products_name');
            $table->longText('products_description')->nullable();
//            $table->longText('products_short_description')->nullable();
            $table->longText('product_return_exchange_policy')->nullable();
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
        Schema::dropIfExists('admin_products_description');
    }

}
