<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateAdminMonogramLetterCountTable extends Migration
{

    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('admin_monogram_letter_count', function (Blueprint $table)
        {
            $table->increments('id');
            $table->string('letter_count_name');
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
        Schema::dropIfExists('admin_monogram_letter_count');
    }

}
