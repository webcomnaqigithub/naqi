<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class CouponAgents extends Model
{
    protected $table='coupon_agents';
    public function agent(){
        return $this->belongsTo(Agent::class);
    }

    public function coupon(){
        return $this->belongsTo(Coupon::class,'coupon_id');
    }

}
