<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class UpdateUsersTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
	Schema::table('users', function (Blueprint $table) {
            $table->dropColumn(['lat', 'lng']);
        });
        Schema::table('users', function(Blueprint $table) {
            
            $table->double('lat')->nullable();
            $table->double('lng')->nullable();
            $table->text('fcmToken')->nullable()->change();
            $table->string('avatar')->nullable();
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
        //
    }
}
