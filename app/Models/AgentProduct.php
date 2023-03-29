<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class AgentProduct extends Model
{
    protected $table = 'agentProducts';
    protected $fillable = ['agentId','productId','homePrice','otherPrice','mosquePrice','officialPrice','status','type'];
}
