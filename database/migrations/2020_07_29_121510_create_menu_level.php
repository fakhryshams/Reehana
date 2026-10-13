<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateMenuLevel extends Migration
{

    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('menu_level_1', function (Blueprint $table)
        {
            $table->increments('id');
            $table->string('name');
            $table->timestamps();
        });
        Schema::create('menu_level_2', function (Blueprint $table)
        {
            $table->increments('id');
            $table->unsignedInteger('menu_level_1_id');
            $table->string('name');
            $table->timestamps();

            $table->foreign('menu_level_1_id')
                    ->references('id')
                    ->on('menu_level_1')
                    ->onDelete('cascade');
        });
        Schema::create('menu_level_3', function (Blueprint $table)
        {
            $table->increments('id');
            $table->unsignedInteger('menu_level_2_id');
            $table->string('name');
            $table->timestamps();

            $table->foreign('menu_level_2_id')
                    ->references('id')
                    ->on('menu_level_2')
                    ->onDelete('cascade');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('menu_level_3');
        Schema::dropIfExists('menu_level_2');
        Schema::dropIfExists('menu_level_1');
    }

}
