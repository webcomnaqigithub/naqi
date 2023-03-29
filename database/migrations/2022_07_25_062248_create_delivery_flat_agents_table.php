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
            $table->foreign('delivery_flat_location_id')->references('id')->on('delivery_flat_locations');

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
        Schema::dropIfExists('delivery_flat_agents');
    }
}
