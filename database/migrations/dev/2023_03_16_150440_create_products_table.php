<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateProductsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('products', function (Blueprint $table) {
            $table->bigIncrements('id');
            $table->string('arabicName');
            $table->string('englishName');
            $table->string('picture');
            $table->double('otherPrice')->nullable()->default(0);
            $table->integer('status')->default(2);
            $table->tinyInteger('type')->nullable()->default(1);
            $table->timestamps();
            $table->softDeletes();
            $table->double('old_price', 10, 2)->default(0);
            $table->double('offer_price', 10, 2)->default(0);
            $table->integer('offer_qty')->default(0);
            $table->date('offer_expire_date')->nullable();
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
        Schema::dropIfExists('products');
    }
}
