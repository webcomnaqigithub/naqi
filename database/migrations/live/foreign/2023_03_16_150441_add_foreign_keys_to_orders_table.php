<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class AddForeignKeysToOrdersTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('orders', function (Blueprint $table) {
            $table->foreign(['coupon_id'])->references(['id'])->on('coupons')->onUpdate('NO ACTION')->onDelete('NO ACTION');
            $table->foreign(['flat_location_id'])->references(['id'])->on('delivery_flat_locations')->onUpdate('NO ACTION')->onDelete('NO ACTION');
            $table->foreign(['offer_id'])->references(['id'])->on('offers')->onUpdate('NO ACTION')->onDelete('NO ACTION');
            $table->foreign(['payment_type_id'])->references(['id'])->on('payment_types')->onUpdate('NO ACTION')->onDelete('NO ACTION');
            $table->foreign(['schedule_slot_id'])->references(['id'])->on('order_schedule_slots')->onUpdate('NO ACTION')->onDelete('NO ACTION');
            $table->foreign(['time_slot_id'])->references(['id'])->on('time_slots')->onUpdate('NO ACTION')->onDelete('NO ACTION');
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
            $table->dropForeign('orders_coupon_id_foreign');
            $table->dropForeign('orders_flat_location_id_foreign');
            $table->dropForeign('orders_offer_id_foreign');
            $table->dropForeign('orders_payment_type_id_foreign');
            $table->dropForeign('orders_schedule_slot_id_foreign');
            $table->dropForeign('orders_time_slot_id_foreign');
        });
    }
}
