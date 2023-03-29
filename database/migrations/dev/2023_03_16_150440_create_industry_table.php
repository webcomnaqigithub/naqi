<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateIndustryTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('industry', function (Blueprint $table) {
            $table->bigIncrements('id');
            $table->string('name');
            $table->string('mobile')->unique();
            $table->text('fcmToken');
            $table->integer('status')->default(2);
            $table->timestamps();
            $table->string('password');
            $table->string('api_token', 80)->nullable();
            $table->string('language', 2)->default('ar');
            $table->string('otp', 6)->nullable();
            $table->integer('isAdmin')->default(1);
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
        Schema::dropIfExists('industry');
    }
}
