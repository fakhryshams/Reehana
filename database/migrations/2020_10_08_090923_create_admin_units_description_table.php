<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateAdminUnitsDescriptionTable extends Migration
{

    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('admin_units_description', function (Blueprint $table)
        {
            $table->increments('id');
            $table->integer('units_id')->default(0)->comment("FK = admin_units");
            $table->integer('language_id')->comment("FK = admin_languages");
            $table->string('unit_name', 32)->index('idx_unit_name');
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
        Schema::dropIfExists('admin_units_description');
    }

}
