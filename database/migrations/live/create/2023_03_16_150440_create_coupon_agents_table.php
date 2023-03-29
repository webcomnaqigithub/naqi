<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateCouponAgentsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('coupon_agents', function (Blueprint $table) {
            $table->unsignedInteger('coupon_id')->index('coupon_agents_coupon_id_foreign');
            $table->unsignedBigInteger('agent_id')->index('coupon_agents_agent_id_foreign');
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
    }
}
