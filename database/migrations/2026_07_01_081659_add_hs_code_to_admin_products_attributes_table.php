<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class AddHsCodeToAdminProductsAttributesTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('admin_products_attributes', function (Blueprint $table) {
            $table->string('hs_code')->nullable()->after('weight_unit');
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
            $table->dropColumn(['hs_code']);
        });
    }
}
