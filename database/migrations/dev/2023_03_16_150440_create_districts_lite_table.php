<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateDistrictsLiteTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('districts_lite', function (Blueprint $table) {
            $table->integer('id', true);
            $table->string('district_id', 12)->nullable();
            $table->integer('city_id');
            $table->integer('region_id');
            $table->string('arabicName', 64)->default('');
            $table->string('englishName', 64)->default('');
            $table->integer('status')->nullable()->default(2);
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('districts_lite');
    }
}
