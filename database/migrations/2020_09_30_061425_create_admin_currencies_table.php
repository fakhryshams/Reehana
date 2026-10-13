<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateAdminCurrenciesTable extends Migration
{

    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('admin_currencies', function (Blueprint $table)
        {
            $table->increments('id');
            $table->string('title');
            $table->string('code');
            $table->string('symbol_left')->nullable();
            $table->string('symbol_right')->nullable();
            $table->unsignedInteger('country_id')->comment("FK = sadmin_countries")->nullable();
            $table->string('position', 12)->nullable()->comment("Left/Right/Left with Space/Right with Space");
            $table->char('decimal_point', 1)->nullable();
            $table->char('thousands_seperator', 1)->nullable();
            $table->char('decimal_seperator', 1)->nullable();
            $table->float('value', 13, 8)->nullable();
            $table->boolean('is_default')->default(0);
            $table->boolean('is_current')->default(0);
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
        Schema::dropIfExists('admin_currencies');
    }

}
