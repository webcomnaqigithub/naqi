<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateAgentsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('agents', function (Blueprint $table) {
            $table->bigIncrements('id');
            $table->string('name');
            $table->string('mobile');
            $table->integer('region');
            $table->integer('region_id')->nullable();
            $table->integer('city');
            $table->integer('district_id')->nullable();
            $table->integer('city_id')->nullable();
            $table->text('fcmToken')->nullable();
            $table->polygon('area')->nullable();
            $table->integer('status')->default(3)->comment('1= enabled. 2 =disabled. 3 = reset password');
            $table->integer('minimum_cartons')->default(15);
            $table->timestamps();
            $table->string('password');
            $table->string('api_token', 80)->nullable()->unique();
            $table->string('language', 2)->default('ar');
            $table->string('otp', 4)->nullable();
            $table->text('englishSuccessMsg')->nullable();
            $table->text('arabicSuccessMsg')->nullable();
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
        Schema::dropIfExists('agents');
    }
}
