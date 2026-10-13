<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateAdminSettingsTable extends Migration
{

    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('admin_settings', function (Blueprint $table)
        {
            $table->increments('id');
            $table->integer('sorting');
            $table->longText('setting_key');
            $table->longText('setting_val')->nullable();
            $table->longText('description');
            $table->boolean('is_multi_lang')->default(false);
            //Default
            $table->boolean('is_require')->default('0');
            $table->boolean('can_edit')->default('1');
            $table->string('type');
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
        Schema::dropIfExists('admin_settings');
    }

}
