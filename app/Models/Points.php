<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Points extends Model
{
    protected $table = 'points';
    protected $primaryKey = 'id';

    protected $fillable = ['clientId','agentId','points','type','delegatorId','orderId'];



    public function user()
    {
        return $this->belongsTo(User::class,'clientId');
    }

}
