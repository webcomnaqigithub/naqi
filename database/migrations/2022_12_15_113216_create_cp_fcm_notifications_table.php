<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateCpFcmNotificationsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('cp_fcm_notifications', function (Blueprint $table) {
            $table->bigIncrements('id');
            $table->enum('sender_type', ['admin', 'agent']); // admin or agent

            $table->bigInteger('industry_id')->nullable()->unsigned();
            $table->foreign('industry_id')->references('id')->on('industry')->onUpdate('cascade')->onDelete('set null');

            $table->bigInteger('agent_id')->nullable()->unsigned();
            $table->foreign('agent_id')->references('id')->on('agents')->onUpdate('cascade')->onDelete('set null');

            $table->string('title');
            $table->string('body');
            $table->longText('receiver_ids');
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
        Schema::dropIfExists('cp_fcm_notifications');
    }
}
