<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateRejectionReasonsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('rejectionReasons', function (Blueprint $table) {
            $table->bigIncrements('id');
            $table->string('arabicReason');
            $table->string('englishReason');
            $table->timestamps();
            $table->integer('status')->nullable()->default(2);
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('rejectionReasons');
    }
}
