<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateOffersTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('offers', function (Blueprint $table) {
            $table->bigIncrements('id');
            $table->string('name_ar')->nullable();
            $table->string('name_en')->nullable();
            $table->text('desc_ar')->nullable();
            $table->text('desc_en')->nullable();
            $table->string('picture')->nullable();
            $table->double('old_price')->nullable();
            $table->double('price')->nullable();
            $table->date('start_date')->nullable();
            $table->date('expire_date')->nullable();
            $table->boolean('is_active')->default(false);
            $table->boolean('is_banner')->default(false);
            $table->unsignedBigInteger('product_id')->index('offers_product_id_foreign');
            $table->integer('product_qty')->nullable();
            $table->unsignedBigInteger('gift_product_id')->nullable();
            $table->integer('gift_product_qty')->nullable();
            $table->unsignedBigInteger('agent_id')->nullable()->index('offers_agent_id_foreign');
            $table->timestamps();
            $table->softDeletes();
            $table->string('offer_type')->nullable();
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('offers');
    }
}
