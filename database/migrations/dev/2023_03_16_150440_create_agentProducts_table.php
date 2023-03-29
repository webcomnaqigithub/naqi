<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateAgentProductsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('agentProducts', function (Blueprint $table) {
            $table->bigIncrements('id');
            $table->timestamps();
            $table->bigInteger('agentId');
            $table->bigInteger('productId');
            $table->double('mosquePrice')->unsigned();
            $table->double('homePrice')->unsigned();
            $table->double('officialPrice')->unsigned();
            $table->unsignedInteger('otherPrice')->default(0);
            $table->integer('status')->default(1);
            $table->tinyInteger('type')->nullable()->default(1);
            $table->integer('min_order_qty')->default(1);
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('agentProducts');
    }
}
