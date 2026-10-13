<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateAdminMonogramImageTable extends Migration
{

    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('admin_monogram_image', function (Blueprint $table)
        {
            $table->increments('id');
            $table->unsignedInteger('font_style_id')->comment("FK=admin_monogram_font_style");
            $table->string('letter')->comment("A-Z");
            $table->text('left_image');
            $table->text('center_image')->nullable();
            $table->text('right_image')->nullable();
            $table->text('single_image')->nullable();
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
        Schema::dropIfExists('admin_monogram_image');
    }

}
