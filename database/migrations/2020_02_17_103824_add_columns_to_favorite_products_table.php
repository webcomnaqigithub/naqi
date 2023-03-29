<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class AddColumnsToFavoriteProductsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('favoriteProducts', function (Blueprint $table) {
            //
            $table->bigInteger('userId');
            $table->bigInteger('productId');

        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::table('favoriteProducts', function (Blueprint $table) {
            //
            $table->dropColumn('userId');
            $table->dropColumn('productId');

        });
    }
}
