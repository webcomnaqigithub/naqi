<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Sms extends Model
{
    protected $table = 'sms';
    protected $fillable = ['otp','userId','status'];


    public function customer(){
        return $this->belongsTo(Customer::class,'userId');
    }
}
