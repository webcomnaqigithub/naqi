<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class UpdateOrdersTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {

	Schema::table('orders', function (Blueprint $table) {
            $table->dropColumn(['status']);
        });
        Schema::table('orders', function (Blueprint $table) {
            $table->enum('status', ['created', 'assigned', 'cancelledByClient', 'cancelledByApp', 'completed', 'on_the_way'])->default('created');
            $table->date('deliveryDate')->nullable();
            $table->time('deliveryTime')->nullable();
            $table->tinyInteger('preorder')->nullable()->default(0);
            $table->enum('deliveryLocation', ['ground', 'upstairs'])->default('ground');
            $table->enum('deliveryTimePeriod', ['morning', 'evening', 'any'])->default('any');
            $table->unsignedInteger('coupon_id')->nullable()->index();
            $table->unsignedBigInteger('time_slot_id')->nullable()->index();
            $table->unsignedBigInteger('schedule_slot_id')->nullable()->index();
            $table->unsignedBigInteger('flat_location_id')->nullable()->index();
            $table->unsignedBigInteger('payment_type_id')->nullable()->index();
            $table->unsignedBigInteger('parent_order_id')->nullable()->index();
            $table->enum('type', ['normal', 'offer'])->default('normal');
            $table->double('sub_total', 8, 2)->nullable();
            $table->double('total_discount', 8, 2)->nullable();
            $table->double('sub_total_2', 8, 2)->nullable();
            $table->double('tax_ratio', 8, 2)->nullable();
            $table->double('tax', 8, 2)->nullable();
            $table->double('delivery_cost', 8, 2)->nullable();
            $table->boolean('use_points')->default(false);
            $table->date('delivery_schedule_date')->nullable();
            $table->boolean('is_paid')->default(false);
            $table->integer('payment_transaction_id')->nullable();
            $table->enum('delivery_date', ['immediately', 'schedule'])->default('immediately');
            $table->unsignedBigInteger('cancel_reason_id')->nullable();
            $table->unsignedBigInteger('offer_id')->nullable()->index('orders_offer_id_foreign');
            $table->string('creatable_type')->nullable();
            $table->unsignedBigInteger('creatable_id')->nullable();

            $table->index(['creatable_type', 'creatable_id']);
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
