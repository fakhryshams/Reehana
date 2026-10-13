<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreatePhysicalStoreUsersTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('physical_store_users', function (Blueprint $table) {
            $table->id();
            $table->string('title')->nullable();
            $table->string('physical_store_id');
            $table->string('user_id');
            $table->string('first_name');
            $table->string('last_name');
            $table->string('username');
            $table->string('social_id')->nullable();
            $table->enum('login_type', array('website', 'google', 'facebook', 'guest'))->default('website');
            $table->string('email');
            $table->string('mobile');
            $table->date('birth_date')->nullable();
            $table->integer('country')->nullable();
            $table->longText('profile_picture')->nullable();
            $table->boolean('is_active')->default('1');
            $table->integer('locale')->nullable()->comment('User selected language ID');
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
        Schema::dropIfExists('physical_store_users');
    }
}
