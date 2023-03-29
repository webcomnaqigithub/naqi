<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateAddressTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('address', function (Blueprint $table) {
            $table->bigIncrements('id');
            $table->bigInteger('userId')->index('userId');
            $table->string('name');
            $table->string('type');
            $table->double('lat', 11, 7);
            $table->double('lng', 11, 8);
            $table->boolean('default');
            $table->integer('status')->default(2);
            $table->timestamps();
            $table->softDeletes();
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
        Schema::dropIfExists('address');
    }
}
