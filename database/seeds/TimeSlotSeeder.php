<?php

use Illuminate\Database\Seeder;

class TimeSlotSeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
        \App\Models\TimeSlot::insert([
            [
            'title_ar'=>'اي وقت',
            'title_en'=>'Any time',
            'is_active'=>true,
            'start_at'=>\Carbon\Carbon::parse('08:00 am')->format('H:i:s'),
            'end_at'=>\Carbon\Carbon::parse('08:00 pm')->format('H:i:s'),
            ],

            [
                'title_ar'=>'صباحي',
                'title_en'=>'At morning',
                'is_active'=>true,
                'start_at'=>\Carbon\Carbon::parse('08:00 am')->format('H:i:s'),
                'end_at'=>\Carbon\Carbon::parse('02:00 pm')->format('H:i:s'),
            ],
            [
                'title_ar'=>'مسائي ',
                'title_en'=>'At evening',
                'is_active'=>true,
                'start_at'=>\Carbon\Carbon::parse('02:00 pm')->format('H:i:s'),
                'end_at'=>\Carbon\Carbon::parse('08:00 pm')->format('H:i:s'),
            ],

        ]);
    }
}
