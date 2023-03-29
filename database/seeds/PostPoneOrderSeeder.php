<?php

use Illuminate\Database\Seeder;

class PostPoneOrderSeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
        \App\Models\PostponeReason::insert([
            [
                'title_ar'=>'مكان توصيل العميل بعيد جداً عني',
                'title_en'=>'The customer\'s delivery location is too far from me',
                'is_active'=>true,
            ],

            [
                'title_ar'=>'لدي الكثير من الطلبات هذا اليوم',
                'title_en'=>'I have a lot of orders today',
                'is_active'=>true,
            ],


        ]);
    }
}
