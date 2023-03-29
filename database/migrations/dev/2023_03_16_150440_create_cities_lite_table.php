<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateCitiesLiteTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('cities_lite', function (Blueprint $table) {
            $table->increments('id');
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
        Schema::dropIfExists('cities_lite');
    }
}
