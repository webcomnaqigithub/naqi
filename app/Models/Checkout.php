<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Checkout extends Model
{
    //
    protected $table = 'checkout';
    protected $fillable = ['id','userId','amount','hyperpayResponse','uuid'];
}
