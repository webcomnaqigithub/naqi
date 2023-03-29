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
            $table->string('mobile')->unique();
            $table->string('region');
            $table->string('city');
            $table->text('api_token');
            $table->text('fcmToken');
            // $table->multiPolygon('coverageArea');
            $table->polygon('area')->nullable();

            $table->integer('status')->default(2);// 2 disabled, 1 enabled
            $table->timestamps();
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
