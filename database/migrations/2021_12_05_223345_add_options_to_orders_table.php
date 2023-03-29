<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class AddOptionsToOrdersTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('orders', function (Blueprint $table) {
//            $table->date('deliveryDate')->nullable();
            $table->time('deliveryTime')->nullable();
             $table->longText('address')->nullable();
            $table->tinyInteger('preorder')->default('0')->nullable();
            $table->enum('deliveryLocation',['ground', 'upstairs'])->default('ground');
            $table->enum('deliveryTimePeriod',['morning', 'evening','any'])->default('any');

        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::table('orders', function (Blueprint $table) {
            //
            $table->dropColumn('deliveryDate');
            // $table->dropColumn('address');
            $table->dropColumn('preorder');
            $table->dropColumn('deliveryLocation');
            $table->dropColumn('deliveryTime');
            $table->dropColumn('deliveryTimePeriod');

        });
    }
}
