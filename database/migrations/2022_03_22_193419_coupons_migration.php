<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CouponsMigration extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        if(!Schema::hasColumn('coupons','deleted_at')){
            Schema::table('coupons', function (Blueprint $table) {
                    $table->softDeletes();
            });
        }

    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        if(Schema::hasColumn('coupons','deleted_at')){
            Schema::table('coupons', function (Blueprint $table) {
                $table->dropSoftDeletes();
            });
        }
    }
}
