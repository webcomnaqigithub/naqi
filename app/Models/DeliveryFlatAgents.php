<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class DeliveryFlatAgents extends Model
{
    protected $table='delivery_flat_agents';
    public function agent(){
        return $this->belongsTo(Agent::class);
    }
    public function flat(){
        return $this->belongsTo(DeliveryFlatLocation::class,'delivery_flat_location_id');
    }
}
