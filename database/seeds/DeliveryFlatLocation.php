<?php

use Illuminate\Database\Seeder;

class DeliveryFlatLocation extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
        \App\Models\DeliveryFlatLocation::insert([
            [
                'title_en'=>"Ground floor",
                'title_ar'=>"الطابق الارضي",
                'delivery_cost'=>0,
            ],
            [
                'title_en'=>"Upstairs",
                'title_ar'=>"الطابق العلوي",
                'delivery_cost'=>0.5,
            ],
        ]);
    }
}
