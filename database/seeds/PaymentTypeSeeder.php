<?php

use Illuminate\Database\Seeder;

class PaymentTypeSeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
            \App\Models\PaymentType::insert([
                [
                'name_ar'=>'الدفع عن الاستلام',
                'name_en'=>'Cash on delivery',
                'icon'=>'cash.png',
                'is_active'=>true,
                ],
                [
                    'name_ar'=>'بطاقة فيزا',
                    'name_en'=>'Visa card',
                    'icon'=>'visa.png',
                    'is_active'=>true,
                ],
                [
                    'name_ar'=>'بطاقة مدى',
                    'name_en'=>'Mada card',
                    'icon'=>'mada.png',
                    'is_active'=>true,
                ],
            ]);
//        \DB::transaction(function() use ($rid) {
//            for($i = 1; $i <= 5; $i++) {
//                \App\Models\PaymentType::updateOrCreate(
//                    ['r_id' => $rid, 'meta_key' => "q$i"],
//                    ['meta_value' => Input::get("q$i")]);
//            }
//        });
    }
}
