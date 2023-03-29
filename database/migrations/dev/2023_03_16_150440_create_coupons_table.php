<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateCouponsTable extends Migration
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
            $table->string('type');
            $table->decimal('value');
            $table->unsignedInteger('used')->default(0);
            $table->decimal('minAmount', 10);
            $table->dateTime('notBefore')->nullable();
            $table->dateTime('notAfter')->nullable();
            $table->integer('status')->default(2);
            $table->timestamps();
            $table->enum('target_agent', ['all', 'custom'])->default('all');
            $table->softDeletes();
            $table->boolean('is_used_one_time')->default(false);
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
