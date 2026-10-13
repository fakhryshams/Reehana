<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateAdminCategoriesTable extends Migration
{

    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('admin_categories', function (Blueprint $table)
        {
            $table->increments('id');
            $table->text('categories_image', 65535)->nullable();
            $table->text('categories_icon', 65535)->nullable();
            $table->integer('parent_id')->default(0)->index('idx_categories_parent_id');
            $table->string('categories_slug', 191)->nullable();
            $table->enum('show_in', ['accessories', 'clothing'])->nullable()->comment('A=Accessories,C=Clothing');
            $table->boolean('show_on_homepage')->default('0');
            $table->integer('min_order_quantity');
            $table->integer('max_order_quantity');
            $table->integer('sorting')->nullable();
            $table->boolean('show_in_menu')->default('1');
            $table->boolean('show_customized_form')->default('0');

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
        Schema::dropIfExists('admin_categories');
    }

}
