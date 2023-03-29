<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class ModifyOrderProductTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('orderProducts', function (Blueprint $table) {
            $table->double('price',8,2)->nullable();
            $table->double('total',8,2)->nullable();
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
            $table->dropColumn('price');
            $table->dropColumn('total');

        });
    }
}
