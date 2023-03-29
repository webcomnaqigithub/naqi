<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class AddForeignKeysToDeliveryFlatAgentsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('delivery_flat_agents', function (Blueprint $table) {
            $table->foreign(['agent_id'])->references(['id'])->on('agents')->onUpdate('NO ACTION')->onDelete('NO ACTION');
            $table->foreign(['delivery_flat_location_id'])->references(['id'])->on('delivery_flat_locations')->onUpdate('NO ACTION')->onDelete('NO ACTION');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::table('delivery_flat_agents', function (Blueprint $table) {
            $table->dropForeign('delivery_flat_agents_agent_id_foreign');
            $table->dropForeign('delivery_flat_agents_delivery_flat_location_id_foreign');
        });
    }
}
