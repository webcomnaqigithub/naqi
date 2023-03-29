<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class AddForeignKeysToTimeSlotAgentsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('time_slot_agents', function (Blueprint $table) {
            $table->foreign(['agent_id'])->references(['id'])->on('agents')->onUpdate('NO ACTION')->onDelete('NO ACTION');
            $table->foreign(['time_slot_id'])->references(['id'])->on('time_slots')->onUpdate('NO ACTION')->onDelete('NO ACTION');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::table('time_slot_agents', function (Blueprint $table) {
            $table->dropForeign('time_slot_agents_agent_id_foreign');
            $table->dropForeign('time_slot_agents_time_slot_id_foreign');
        });
    }
}
