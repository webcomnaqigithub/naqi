<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateYamamahTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('yamamah', function (Blueprint $table) {
            $table->bigIncrements('id');
            $table->string('InvalidMSISDN')->nullable();
            $table->string('MessageID')->nullable();
            $table->string('Status')->nullable();
            $table->string('StatusDescription')->nullable();
            $table->string('Msisdn')->nullable();
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
        Schema::dropIfExists('yamamah');
    }
}
