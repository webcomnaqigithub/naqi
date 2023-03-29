<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class AddColumnOrders extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('orders', function (Blueprint $table) {
            $table->bigInteger('userId')->nullable();
            $table->bigInteger('addressId')->nullable();
            $table->bigInteger('agentId');

            $table->bigInteger('delegatorId')->nullable();
            $table->dateTime('assignDate')->nullable();

            $table->string('coupon')->nullable();
            $table->double('amount', 8, 1)->nullable();	
            $table->string('points')->nullable();
            $table->string('paymentReference')->nullable();

            $table->dateTime('rejectionDate')->nullable();
            $table->text('rejectionReason')->nullable();
            $table->dateTime('cancelDate')->nullable();

            $table->text('reviewText')->nullable();
            $table->double('productsReview')->nullable();
            $table->double('delegatorReview')->nullable();
            $table->double('serviceReview')->nullable();
            
            $table->enum('status',['created', 'cancelledByClient','cancelledByApp','completed'])->default('created');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::table('orders', function (Blueprint $table) {
            $table->dropColumn('userId');
            $table->dropColumn('addressId');
            $table->dropColumn('agentId');

            $table->dropColumn('delegatorId');
            $table->dropColumn('assignDate');

            $table->dropColumn('rejectionReason');
            $table->dropColumn('rejectionDate');
            $table->dropColumn('cancelDate');

            $table->dropColumn('amount');
            $table->dropColumn('coupon');
            $table->dropColumn('points');
            $table->dropColumn('paymentReference');


            $table->dropColumn('reviewText');
            $table->dropColumn('productsReview');
            $table->dropColumn('delegatorReview');
            $table->dropColumn('serviceReview');

            $table->dropColumn('status');
        });
    }
}
