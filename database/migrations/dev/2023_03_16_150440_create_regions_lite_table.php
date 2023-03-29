<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateRegionsLiteTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('regions_lite', function (Blueprint $table) {
            $table->increments('id');
            $table->integer('capital_city_id');
            $table->string('code', 2)->default('');
            $table->string('arabicName', 64)->default('');
            $table->string('englishName', 64)->default('');
            $table->integer('population')->nullable();
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
        Schema::dropIfExists('regions_lite');
    }
}
