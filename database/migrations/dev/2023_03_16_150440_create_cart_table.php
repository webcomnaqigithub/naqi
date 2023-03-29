<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateCartTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('cart', function (Blueprint $table) {
            $table->bigInteger('id', true);
            $table->string('userId')->nullable();
            $table->bigInteger('agentId');
            $table->timestamps();
            $table->enum('addressType', ['mosque', 'company', 'home']);
            $table->unsignedBigInteger('address_id')->nullable()->index();
            $table->text('checkout_id')->nullable();
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('cart');
    }
}
