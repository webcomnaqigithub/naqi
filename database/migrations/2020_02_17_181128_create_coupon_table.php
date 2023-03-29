<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateCouponTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('coupons', function (Blueprint $table) {
            $table->increments('id');
            $table->string('name');
            $table->string('code')->unique();
            $table->string('type'); // percentage/ flat
            $table->decimal('value');
            $table->unsignedInteger('total');
            $table->unsignedInteger('used')->default(0);
            $table->decimal('minAmount', 10, 2);
            $table->datetime('notBefore')->nullable();
            $table->datetime('notAfter')->nullable();
            $table->integer('status')->default(2); // 2 disabled, 1 enabled
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('coupons');
    }
}
