<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class UpdateYamamahTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        //
        Schema::table('yamamah', function(Blueprint $table) {
            $table->unsignedBigInteger('id',true)->autoIncrement()->change();
            $table->string('Msisdn',255)->nullable()->charset('utf8mb4')->collation('utf8mb4_unicode_ci')->change();
            $table->string('StatusDescription',255)->nullable()->charset('utf8mb4')->collation('utf8mb4_unicode_ci')->change();
            $table->string('Status',255)->charset('utf8mb4')->nullable()->collation('utf8mb4_unicode_ci')->change();
            $table->string('MessageID',255)->nullable()->charset('utf8mb4')->collation('utf8mb4_unicode_ci')->change();
            $table->string('InvalidMSISDN',255)->nullable()->charset('utf8mb4')->collation('utf8mb4_unicode_ci')->change();
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        //
    }
}
