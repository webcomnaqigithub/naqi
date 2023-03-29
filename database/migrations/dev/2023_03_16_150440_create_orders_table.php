<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateOrdersTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('orders', function (Blueprint $table) {
            $table->bigInteger('id', true);
            $table->timestamp('created_at')->nullable()->index('created_at_ind');
            $table->timestamp('updated_at')->nullable();
            $table->bigInteger('userId')->nullable()->index('userId');
            $table->bigInteger('addressId')->nullable();
            $table->bigInteger('agentId')->index('agentId');
            $table->bigInteger('delegatorId')->nullable()->index('delegatorId');
            $table->dateTime('assignDate')->nullable();
            $table->string('coupon')->nullable();
            $table->double('amount', 8, 1)->nullable();
            $table->integer('points')->nullable();
            $table->string('paymentReference')->nullable();
            $table->dateTime('rejectionDate')->nullable();
            $table->text('rejectionReason')->nullable();
            $table->dateTime('cancelDate')->nullable();
            $table->text('reviewText')->nullable();
            $table->double('productsReview')->nullable();
            $table->double('delegatorReview')->nullable();
            $table->double('serviceReview')->nullable();
            $table->enum('status', ['created', 'assigned', 'cancelledByClient', 'cancelledByApp', 'completed', 'on_the_way'])->default('created');
            $table->enum('addressType', ['mosque', 'company', 'home']);
            $table->softDeletes();
            $table->dateTime('deliveryDateX')->nullable();
            $table->text('delegatorReviewText')->nullable();
            $table->string('clientEvaluation', 2)->nullable();
            $table->timestamp('completionDate')->nullable();
            $table->double('pointsDiscount')->nullable()->default(0);
            $table->double('couponDiscount')->nullable()->default(0);
            $table->integer('district_id')->nullable();
            $table->integer('city_id')->nullable();
            $table->integer('region_id')->nullable();
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
        Schema::dropIfExists('orders');
    }
}
