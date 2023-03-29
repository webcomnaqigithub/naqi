<?php

use Illuminate\Database\Seeder;

class ProvincesSeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
        $Provinces=array(

                array("id"=>"1","nameAr"=>" الحدود الشمالية","nameEn"=>"Northern Frontier"),
                array("id"=>"2","nameAr"=>" الجوف","nameEn"=>"Al Jawf"),
                array("id"=>"3","nameAr"=>" تبوك","nameEn"=>"Tabuk"),
                array("id"=>"4","nameAr"=>" حائل","nameEn"=>"Hail"),
                array("id"=>"5","nameAr"=>" القصيم","nameEn"=>"Al Qassim"),
                array("id"=>"6","nameAr"=>" الرياض","nameEn"=>"Ar Riyadh"),
                array("id"=>"7","nameAr"=>" المدينة المنورة","nameEn"=>"Al Madinah Al Munawwarah"),
                array("id"=>"8","nameAr"=>" عسير","nameEn"=>"Asir"),
                array("id"=>"9","nameAr"=>" الباحة","nameEn"=>"Al Baha"),
                array("id"=>"10","nameAr"=>" جازان","nameEn"=>"Jazan"),
                array("id"=>"11","nameAr"=>" مكة المكرمة","nameEn"=>"Makkah Al Mukarramah"),
                array("id"=>"12","nameAr"=>" نجران","nameEn"=>"Najran"),
                array("id"=>"13","nameAr"=>"الشرقية","nameEn"=>"Eastern "),
);
        \App\Models\Province::insert($Provinces);
    }
}
