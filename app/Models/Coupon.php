<?php

namespace App\Models;


use App\Models\Agent;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class Coupon extends Model
{
    use SoftDeletes;
    protected $table = 'coupons';
    protected $fillable = ['name','code','type','value','used','minAmount','notBefore','notAfter','status','target_agent','is_used_one_time'];

    public function agents()
    {
        return $this->belongsToMany(Agent::class, 'coupon_agents','coupon_id','agent_id');

//        return $this->hasMany(ProductVariation::class, 'product_id')->with('variationItems.attribute');
    }
    public function agentsList()
    {
        return $this->hasMany(CouponAgents::class,'coupon_id');

    }




}
