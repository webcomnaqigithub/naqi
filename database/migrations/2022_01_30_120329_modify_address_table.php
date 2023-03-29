<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class ModifyAddressTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('address', function (Blueprint $table) {
            $table->unsignedInteger('region_id')->index();
//            $table->foreign('region_id')->on('regions_lite')->references('id');

            $table->unsignedInteger('city_id')->index();
//            $table->foreign('city_id')->on('cities_lite')->references('id');

            $table->unsignedInteger('district_id')->index()->nullable();
//            $table->foreign('district_id')->on('districts_lite')->references('id');



        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::table('address', function (Blueprint $table) {
//            $table->dropForeign(['region_id']);
            $table->dropColumn('region_id');

//            $table->dropForeign(['city_id']);
            $table->dropColumn('city_id');

//            $table->dropForeign(['district_id']);
            $table->dropColumn('district_id');
        });
    }
}
