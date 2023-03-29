<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class UpdateAddressTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('address', function(Blueprint $table) {
            $table->unsignedInteger('region_id')->index();
            $table->unsignedInteger('city_id')->index();
            $table->unsignedInteger('district_id')->nullable()->index();
            $table->unsignedInteger('agent_id')->nullable()->index('address_agent_id_IDX');
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
