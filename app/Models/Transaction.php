<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Transaction extends Model
{
    //
    protected $table = 'transactions';
    protected $fillable = ['id','user_id','checkout_id','status','amount','currency','data','trackable_data','brand'];
}
