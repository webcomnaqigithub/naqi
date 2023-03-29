<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class WithholdingAgentPoint extends Model
{
    protected $guarded=[];

    public function agent(){
        return $this->belongsTo(Agent::class,'agent_id');
    }
}
