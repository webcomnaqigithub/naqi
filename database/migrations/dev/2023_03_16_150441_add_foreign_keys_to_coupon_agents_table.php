<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class AddForeignKeysToCouponAgentsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('coupon_agents', function (Blueprint $table) {
            $table->foreign(['agent_id'])->references(['id'])->on('agents')->onUpdate('NO ACTION')->onDelete('NO ACTION');
            $table->foreign(['coupon_id'])->references(['id'])->on('coupons')->onUpdate('NO ACTION')->onDelete('NO ACTION');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::table('coupon_agents', function (Blueprint $table) {
            $table->dropForeign('coupon_agents_agent_id_foreign');
            $table->dropForeign('coupon_agents_coupon_id_foreign');
        });
    }
}
