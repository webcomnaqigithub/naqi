<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class AddColumnsOrderProductsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('orderProducts', function (Blueprint $table) {
            $table->bigInteger('orderId');
            // $table->foreign('orderId')->references('id')->on('orders')->onDelete('cascade');
            $table->bigInteger('productId');
            // $table->foreign('productId')->references('id')->on('products')->onDelete('cascade');
            $table->unsignedInteger('amount');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::table('orderProducts', function (Blueprint $table) {
            //
            $table->dropColumn('orderId');
            $table->dropColumn('productId');
            $table->dropColumn('amount');
        });
    }
}
