<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;


class ChangeOrderDetails extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        \DB::getDoctrineSchemaManager()
            ->getDatabasePlatform()
            ->registerDoctrineTypeMapping('enum', 'string');
        \Doctrine\DBAL\Types\Type::addType('enum', \Doctrine\DBAL\Types\StringType::class);
        Schema::table('orders', function (Blueprint $table) {

            $table->unsignedInteger('coupon_id')->nullable()->index();
            $table->unsignedBigInteger('time_slot_id')->nullable()->index();
            $table->unsignedBigInteger('schedule_slot_id')->nullable()->index();
            $table->unsignedBigInteger('flat_location_id')->nullable()->index();
            $table->unsignedBigInteger('payment_type_id')->nullable()->index();
            $table->unsignedBigInteger('parent_order_id')->nullable()->index();

            $table->foreign('coupon_id')->on('coupons')->references('id');
            $table->foreign('time_slot_id')->on('time_slots')->references('id');
            $table->foreign('schedule_slot_id')->on('order_schedule_slots')->references('id');
            $table->foreign('flat_location_id')->on('delivery_flat_locations')->references('id');
            $table->foreign('payment_type_id')->on('payment_types')->references('id');

//            $table->foreign('parent_order_id')->on('orders')->references('id');
            $table->enum('type',['normal','offer'])->default('normal');
//            $table->enum('addressType',['mosque', 'company', 'home'])->nullable()->change();
            $table->double('sub_total',8,2)->nullable();
            $table->double('total_discount',8,2)->nullable();
            $table->double('sub_total_2',8,2)->nullable();
            $table->double('tax_ratio',8,2)->nullable();
            $table->double('tax',8,2)->nullable();
//            $table->double('discount',8,2)->nullable();//couponDiscount
//            $table->double('total',8,2)->nullable();//amount
            $table->double('delivery_cost',9,2)->nullable();//couponDiscount
            $table->boolean('use_points')->default(false);
            $table->integer('points')->nullable()->change();

            $table->date('delivery_schedule_date')->nullable();
            $table->boolean('is_paid')->default(false);
            $table->integer('payment_transaction_id')->nullable();
        });




//        Schema::table('orders', function (Blueprint $table) {
//            \DB::statement("ALTER TABLE `orders` ADD `type` ENUM('normal','offer') NULL DEFAULT 'normal' AFTER `deliveryTimePeriod`;");
//        });


    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::table('orders', function (Blueprint $table) {
            $table->dropForeign(['coupon_id']);
            $table->dropForeign(['time_slot_id']);
            $table->dropForeign(['schedule_slot_id']);
            $table->dropForeign(['flat_location_id']);
            $table->dropForeign(['payment_type_id']);
//            $table->dropForeign(['parent_order_id']);
            $table->dropColumn('coupon_id');
            $table->dropColumn('time_slot_id');
            $table->dropColumn('schedule_slot_id');
            $table->dropColumn('flat_location_id');
            $table->dropColumn('payment_type_id');
            $table->dropColumn('parent_order_id');
            $table->dropColumn('type');
            $table->dropColumn('sub_total');
            $table->dropColumn('sub_total_2');
            $table->dropColumn('total_discount');
            $table->dropColumn('tax_ratio');
            $table->dropColumn('tax');
            $table->dropColumn('delivery_cost');
            $table->dropColumn('use_points');
            $table->dropColumn('is_paid');
            $table->dropColumn('delivery_schedule_date');
            $table->dropColumn('payment_transaction_id');
        });
    }
    private function registerEnumWithDoctrine()
    {
        \DB::getDoctrineSchemaManager()
            ->getDatabasePlatform()
            ->registerDoctrineTypeMapping('enum', 'string');
    }
}
