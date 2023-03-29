<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Yamamah extends Model
{
    protected $table = 'yamamah';
    protected $fillable = ['InvalidMSISDN','MessageID','Status','StatusDescription','Msisdn'];
}
