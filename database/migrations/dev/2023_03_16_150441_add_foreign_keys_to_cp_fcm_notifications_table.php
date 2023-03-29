<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class AddForeignKeysToCpFcmNotificationsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('cp_fcm_notifications', function (Blueprint $table) {
            $table->foreign(['industry_id'])->references(['id'])->on('industry')->onUpdate('CASCADE')->onDelete('SET NULL');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::table('cp_fcm_notifications', function (Blueprint $table) {
            $table->dropForeign('cp_fcm_notifications_industry_id_foreign');
        });
    }
}
