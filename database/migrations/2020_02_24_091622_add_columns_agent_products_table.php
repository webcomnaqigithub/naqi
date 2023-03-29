<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class AddColumnsAgentProductsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('agentProducts', function (Blueprint $table) {
            $table->bigInteger('agentId');
            $table->bigInteger('productId');
            $table->unsignedInteger('mosquePrice');
            $table->unsignedInteger('homePrice');
            $table->unsignedInteger('officialPrice');
            $table->unsignedInteger('otherPrice');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::table('agentProducts', function (Blueprint $table) {
            $table->dropColumn('agentId');
            $table->dropColumn('productId');
            $table->dropColumn('mosquePrice');
            $table->dropColumn('homePrice');
            $table->dropColumn('officialPrice');
            $table->dropColumn('otherPrice');
        });
    }
}
