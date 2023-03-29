<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateUsersTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('users', function (Blueprint $table) {
            $table->bigIncrements('id');
            $table->string('avatar')->nullable();
            $table->string('name');
            $table->string('mobile')->unique('users_email_unique');
            $table->dateTime('mobile_verified_at')->nullable();
            $table->string('password');
            $table->rememberToken();
            $table->timestamp('created_at')->nullable()->useCurrent();
            $table->timestamp('updated_at')->nullable();
            $table->double('lng')->nullable();
            $table->double('lat')->nullable();
            $table->text('fcmToken')->nullable();
            $table->integer('status')->default(1)->comment('1= enabled. 2= diabled, 3 = not verified');
            $table->bigInteger('points')->nullable()->default(0);
            $table->string('language', 2)->default('ar');
            $table->integer('district_id')->nullable();
            $table->integer('city_id')->nullable();
            $table->integer('region_id')->nullable();
            $table->softDeletes();
            $table->unsignedBigInteger('agent_id')->nullable()->index('users_agent_id_foreign');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('users');
    }
}
