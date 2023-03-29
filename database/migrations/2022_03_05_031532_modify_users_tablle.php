<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class ModifyUsersTablle extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('users', function (Blueprint $table) {
            \DB::statement('ALTER TABLE `users` MODIFY `lng` Double NULL;');
            \DB::statement('ALTER TABLE `users` MODIFY `lat` Double NULL;');
            \DB::statement('ALTER TABLE `users` MODIFY `fcmToken` TEXT NULL;');
        });

    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::table('users', function (Blueprint $table) {
            //
        });
    }
}
