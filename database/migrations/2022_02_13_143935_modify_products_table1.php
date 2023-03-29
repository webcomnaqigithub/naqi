<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class ModifyProductsTable1 extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('products', function (Blueprint $table) {
            $table->double('old_price',10,2)->default(0);
            $table->double('offer_price',10,2)->default(0);
            $table->integer('offer_qty')->default(0);
            $table->date('offer_expire_date')->nullable();
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
            $table->dropColumn('old_price');
            $table->dropColumn('offer_price');
            $table->dropColumn('offer_qty');
            $table->dropColumn('offer_expire_date');

        });
    }
}
