<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class TimeSlotAgents extends Model
{
    protected $table='time_slot_agents';
    protected $fillable=[
        'time_slot_id',
        'agent_id'
    ];

    public function agent()
    {
        return $this->belongsTo(Agent::class);
    }

    public function TimeSlot()
    {
        return $this->belongsTo(TimeSlot::class);
    }
}
