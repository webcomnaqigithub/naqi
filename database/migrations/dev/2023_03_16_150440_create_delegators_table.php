<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateDelegatorsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('delegators', function (Blueprint $table) {
            $table->bigIncrements('id');
            $table->string('name');
            $table->string('mobile');
            $table->bigInteger('region');
            $table->integer('district_id')->nullable();
            $table->integer('city_id')->nullable();
            $table->integer('region_id')->nullable();
            $table->bigInteger('city');
            $table->text('fcmToken');
            $table->integer('status')->default(3)->comment(' 1= enabled. 2 =disabled. 3 = reset password ');
            $table->timestamps();
            $table->string('password');
            $table->string('api_token', 80)->nullable()->unique();
            $table->string('otp', 4)->nullable();
            $table->string('language', 2)->default('ar');
            $table->integer('agentId');
            $table->softDeletes();
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('delegators');
    }
}
