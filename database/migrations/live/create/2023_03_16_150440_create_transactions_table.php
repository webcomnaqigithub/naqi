<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateTransactionsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('transactions', function (Blueprint $table) {
            $table->string('id')->primary();
            $table->string('user_id');
            $table->string('checkout_id')->unique();
            $table->string('status');
            $table->double('amount', 8, 2);
            $table->string('currency');
            $table->longText('data')->nullable();
            $table->longText('trackable_data');
            $table->string('brand');
            $table->timestamps();
            $table->integer('customer_id')->nullable();
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('transactions');
    }
}
