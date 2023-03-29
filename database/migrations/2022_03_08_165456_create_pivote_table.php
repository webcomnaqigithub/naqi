<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreatePivoteTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('coupon_agents', function (Blueprint $table) {
            $table->unsignedInteger('coupon_id');
            $table->foreign('coupon_id')->references('id')->on('coupons');

            $table->unsignedBigInteger('agent_id');
            $table->foreign('agent_id')->references('id')->on('agents');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('coupon_agents');
//        Schema::table('coupon_agents', function (Blueprint $table) {
//            $table->dropForeign(['coupon_id']);
//            $table->dropForeign(['agent_id']);
//            $table->dropColumn('coupon_id');
//            $table->dropColumn('agent_id');
//        });
    }
}
