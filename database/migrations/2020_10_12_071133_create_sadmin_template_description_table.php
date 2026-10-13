<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateSadminTemplateDescriptionTable extends Migration
{

    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('sadmin_template_description', function (Blueprint $table)
        {
            $table->increments('id');
            $table->integer('template_id')->default(0)->comment("FK = sadmin_template");
            $table->integer('language_id')->comment("FK = admin_languages");
            $table->string('template_name')->index('idx_template_name');
            $table->longText('template_data')->nullable();
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
        Schema::dropIfExists('sadmin_template_description');
    }

}
