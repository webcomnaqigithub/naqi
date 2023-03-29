<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateDeliveryFlatAgentsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('delivery_flat_agents', function (Blueprint $table) {
            $table->unsignedBigInteger('delivery_flat_location_id')->index();
            $table->unsignedBigInteger('agent_id')->index('delivery_flat_agents_agent_id_foreign');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('delivery_flat_agents');
    }
}
