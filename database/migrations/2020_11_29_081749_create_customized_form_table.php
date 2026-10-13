<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateCustomizedFormTable extends Migration
{

    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::dropIfExists('customized_form');
        Schema::create('customized_form', function (Blueprint $table)
        {
            $table->increments('id');
            $table->enum('gift_type', ['gift', 'giveaways']);
            $table->string('sender_name');
            $table->string('receiver_name');
            $table->string('email');
            $table->string('mobile');
            $table->string('gift_occasion');
            $table->string('number_of_guests')->nullable();
            $table->longText('gift_options')->nullable();
            $table->string('name_on_card')->nullable();
            $table->string('gift_budget')->nullable();
            $table->string('wrapping_budget')->nullable();
            $table->date('preferred_delivery_date');
            $table->longText('message')->nullable();
            $table->longText('photo_reference')->nullable();
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
        Schema::dropIfExists('customized_form');
    }

}
