<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class ReferralProgram extends Model
{
    protected $table = 'referralProgram';
    protected $fillable = ['fromUser','toUser'];
}
