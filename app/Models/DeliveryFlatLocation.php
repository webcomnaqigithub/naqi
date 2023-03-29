<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class DeliveryFlatLocation extends Model
{
    use SoftDeletes;
    protected $fillable=['title_ar','title_en','delivery_cost','is_active','default_cost'];
    protected $appends=['title'];
    protected $casts=[
        'default_cost'=>'float',
    ];
    public function getTitleAttribute(){
        if(app()->getLocale()=='en'){
            return $this->title_en;
        }
        return $this->title_ar;
    }

    public function agents()
    {
        return $this->belongsToMany(Agent::class, 'delivery_flat_agents','delivery_flat_location_id','agent_id');
    }

}
