<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class ModifyProductssTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('products', function (Blueprint $table) {
            $table->dropColumn('mosquePrice');
            $table->dropColumn('homePrice');
            $table->dropColumn('officialPrice');


        });
        Schema::table('products', function (Blueprint $table) {
            $table->double('mosquePrice')->default(0);
            $table->double('homePrice')->default(0);
            $table->double('officialPrice')->default(0);
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::table('products', function (Blueprint $table) {
            //
        });
    }
}
