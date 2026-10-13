<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class AddFedexFieldsToAdminProductsAttributesTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('admin_products_attributes', function (Blueprint $table) {
            //
            $table->float('product_length', 10, 2)->nullable()->after('weight');
            $table->float('product_width', 10, 2)->nullable()->after('product_length');
            $table->float('product_height', 10, 2)->nullable()->after('product_width');
            $table->string('dimension_unit')->default('cm')->after('product_height'); // e.g., cm, in
            $table->string('weight_unit')->default('kg')->after('dimension_unit'); // e.g., kg, lb
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::table('admin_products_attributes', function (Blueprint $table) {
            //
            $table->dropColumn(['product_length', 'product_width', 'product_height', 'dimension_unit', 'weight_unit']);
        });
    }
}
