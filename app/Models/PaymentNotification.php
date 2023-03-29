<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class PaymentNotification extends Model
{
    //
    protected $table = 'paymentNotification';
    protected $fillable = ['id','hyperpayRequest'];
}
