<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateAdminProductsTable extends Migration
{

    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('admin_products', function (Blueprint $table)
        {
            $table->increments('id');
            $table->integer('sorting')->nullable();
            $table->string('products_slug')->nullable();
            $table->string('product_sku')->nullable();
            $table->float('product_regular_price', 10, 3);
            $table->float('product_sale_price', 10, 3)->nullable();
            $table->boolean('is_product_sale_price_null')->default(0);

            $table->float('product_weight', 10, 2)->nullable();
            $table->unsignedInteger('brand_id')->comment("admin_brand")->nullable();
            $table->longText('category_type')->nullable();
            $table->unsignedInteger('unit_id')->comment("admin_unit")->nullable();
            $table->unsignedInteger('color_id')->comment("admin_color")->nullable();
            $table->integer('product_quantity')->comment("")->nullable();
            $table->boolean('product_size')->default(false);
            $table->boolean('is_new_arrival')->default(false);
            $table->boolean('is_latest_collection')->default(false);

//            $table->unsignedInteger('manufacturer_id')->nullable()->comment('Brand Id, FK = admin_manufacturers');
//            $table->boolean('is_feature');
//            $table->boolean('flash_sale');
//            $table->float('product_flash_sale_price', 10, 2)->nullable();
//            $table->date('product_flash_sale_start_date')->nullable();
//            $table->time('product_flash_sale_start_time')->nullable();
//            $table->date('product_flash_sale_end_date')->nullable();
//            $table->time('product_flash_sale_end_time')->nullable();
//            $table->boolean('product_flash_sale_status')->nullable();
//            $table->boolean('is_special');
//            $table->float('is_special_price', 10, 2)->nullable();
//            $table->date('is_special_start_date')->nullable();
//            $table->time('is_special_start_time')->nullable();
//            $table->date('is_special_end_date')->nullable();
//            $table->time('is_special_end_time')->nullable();
//            $table->boolean('is_special_status')->nullable();

            $table->boolean('product_seo')->default(false);
            $table->string('product_seo_title')->nullable();
            $table->longText('product_seo_description')->nullable();
            $table->string('product_seo_url')->nullable();
            $table->longText('product_seo_keyword')->nullable();
            $table->boolean('intl_shipping')->default(false)->comment("International Shipping");

            /*
             * Default
             */
            $table->boolean('is_active')->default('1');
            $table->boolean('is_active_category')->default('1');
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
        Schema::dropIfExists('admin_products');
    }

}
