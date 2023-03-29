<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class AddColumnsDelegatorsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('delegators', function (Blueprint $table) {
//            $table->string('password');
//            $table->string('api_token', 80)->after('password')
//            ->unique()
//            ->nullable()
//            ->default(null);
//            $table->string('contactNumber');
//            $table->dropColumn('coverageArea');
//            $table->dropColumn('apiToken');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::table('delegators', function (Blueprint $table) {
            $table->dropColumn('password');
            $table->dropColumn('api_token');
            $table->dropColumn('contactNumber');
            $table->string('coverageArea');
            $table->string('apiToken');
        });
    }
}
