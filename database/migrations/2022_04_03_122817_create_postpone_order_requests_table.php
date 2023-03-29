<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreatePostponeOrderRequestsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::dropIfExists('postpone_order_requests');
        Schema::create('postpone_order_requests', function (Blueprint $table) {
            $table->bigIncrements('id');
            $table->unsignedBigInteger('order_id');
//            $table->foreign('order_id')->on('orders')->references('id');
            $table->unsignedBigInteger('delegator_id');
            $table->unsignedBigInteger('reason_id');
//            $table->foreign('delegator_id')->on('delegators')->references('id');
            $table->enum('status',['opened','closed'])->nullable();
            $table->text('note')->nullable();
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
        Schema::dropIfExists('postpone_order_requests');
    }
}
