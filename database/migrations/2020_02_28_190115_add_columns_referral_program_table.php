<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class AddColumnsReferralProgramTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('referralProgram', function (Blueprint $table) {
            $table->bigInteger('fromUser');
            $table->bigInteger('toUser');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::table('referralProgram', function (Blueprint $table) {
            $table->dropColumn('fromUser');
            $table->dropColumn('toUser');
        });
    }
}
