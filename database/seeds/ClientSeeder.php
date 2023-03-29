<?php

use Illuminate\Database\Seeder;
use App\models\User;
use App\models\Customer;
class ClientSeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
//        User::create([
//            'name'=>'asdfbd',
//            'password'=>Hash::make('123456'),
//            'mobile'=>'0597773989'
//        ]);
        Customer::updateOrCreate(['mobile'=>'0597773989'],[
            'name'=>'test',
            'password'=>Hash::make('123456'),
            'mobile'=>'0597773989'
        ]);
    }
}
