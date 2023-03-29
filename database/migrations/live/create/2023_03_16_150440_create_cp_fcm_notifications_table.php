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
            $table->enum('sender_type', ['admin', 'agent']);
            $table->unsignedBigInteger('industry_id')->nullable()->index('cp_fcm_notifications_industry_id_foreign');
            $table->longText('sender_ids')->nullable();
            $table->longText('receiver_ids');
            $table->string('title');
            $table->string('body');
            $table->string('users_count', 100)->nullable();
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
