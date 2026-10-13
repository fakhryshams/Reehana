<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateAdminAboutUsPageDescriptionTable extends Migration
{

    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('admin_about_us_page_description', function (Blueprint $table)
        {
            $table->increments('id');
            $table->integer('admin_about_us_id')->comment("FK = admin_about_us");
            $table->integer('language_id')->comment("FK = admin_languages");
            $table->longText('note');
            $table->longText('note_2');

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
        Schema::dropIfExists('admin_about_us_page_description');
    }

}
