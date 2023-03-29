<?php

use Illuminate\Database\Seeder;

class OrderScheduleSlotSeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
        \App\Models\OrderScheduleSlot::insert([
            [
                'title_ar'=>'مرة واحدة',
                'title_en'=>'One time',
                'is_active'=>true,
                'code'=>'one_time',

            ],
            [
                'title_ar'=>'يومي',
                'title_en'=>'Daily',
                'is_active'=>true,
                'code'=>'daily',

            ],

            [
                'title_ar'=>'اسبوعي',
                'title_en'=>'Weekly',
                'is_active'=>true,
                'code'=>'weekly',

            ],

            [
                'title_ar'=>'كل اسبوعين',
                'title_en'=>'Every two week',
                'is_active'=>false,
                'code'=>'two_weekly',
            ],
            [
                'title_ar'=>'شهري',
                'title_en'=>'Monthly',
                'is_active'=>true,
                'code'=>'monthly',
            ],

        ]);
    }
}
