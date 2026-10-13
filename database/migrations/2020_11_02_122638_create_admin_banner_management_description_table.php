<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateAdminBannerManagementDescriptionTable extends Migration
{

    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('admin_banner_management_description', function (Blueprint $table)
        {
            $table->increments('id');
            $table->integer('banner_management_id')->default(0)->comment("FK = admin_banner_management");
            $table->integer('language_id')->comment("FK = admin_languages");
            $table->string('button_title')->nullable();
            $table->string('banner_header');
            $table->string('banner_header2')->nullable();
            $table->text('banner_description')->nullable();

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
        Schema::dropIfExists('admin_banner_management_description');
    }

}
