<?php

use Illuminate\Database\Seeder;

class OfferSeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
        \App\Models\Offer::insert([[
            'name_ar'=>'عرض خاص',
            'name_en'=>'special offers',
            'desc_ar'=>'اشتري 20 كرتون عبوة 500 مل × 40 حبة بـ 200 ريال ',
            'desc_en'=>'اشتري 20 كرتون عبوة 500 مل × 40 حبة بـ 200 ريال',
            'picture'=>'/products/1587000776.jpeg',
            'old_price'=>'350',
            'price'=>'200',
            'start_date'=>\Carbon\Carbon::now()->format('Y-m-d'),
            'expire_date'=>\Carbon\Carbon::now()->addMonths(1)->format('Y-m-d'),
            'is_active'=>true,
            'is_banner'=>true,
            'product_id'=>3,
            'product_qty'=>20,
            'gift_product_id'=>7,
            'gift_product_qty'=>2,
            'agent_id'=>208,

        ],
           [
                'name_ar'=>'عرض خاص',
                'name_en'=>'special offers',
                'desc_ar'=>'اشتري 20 كرتون عبوة 500 مل × 40 حبة بـ 250 ريال ',
                'desc_en'=>'اشتري 20 كرتون عبوة 500 مل × 40 حبة بـ 250 ريال',
                'picture'=>'/products/1587000776.jpeg',
                'old_price'=>'400',
                'price'=>'250',
                'start_date'=>\Carbon\Carbon::now()->format('Y-m-d'),
                'expire_date'=>\Carbon\Carbon::now()->addMonths(1)->format('Y-m-d'),
                'is_active'=>true,
                'is_banner'=>true,
                'product_id'=>7,
                'product_qty'=>20,
                'gift_product_id'=>7,
                'gift_product_qty'=>1,
                'agent_id'=>208,

            ],

        ]);
    }
}
