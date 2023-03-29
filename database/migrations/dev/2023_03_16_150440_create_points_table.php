<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreatePointsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('points', function (Blueprint $table) {
            $table->integer('id', true);
            $table->integer('clientId');
            $table->bigInteger('orderId')->nullable();
            $table->integer('points');
            $table->integer('agentId')->nullable();
            $table->string('type', 15)->comment('bonus, discount');
            $table->timestamp('updated_at');
            $table->timestamp('created_at')->nullable()->useCurrent();
            $table->integer('delegatorId')->nullable();
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('points');
    }
}
