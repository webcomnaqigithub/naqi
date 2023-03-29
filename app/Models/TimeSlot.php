<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class TimeSlot extends Model
{
        use SoftDeletes;
        protected $fillable=['title_ar','title_en','start_at','end_at','is_active'];
        protected $appends=['title'];

        public function getTitleAttribute(){
            if(app()->getLocale() == 'en'){
                return $this->title_en;
            }
            return $this->title_ar;
        }

        public function agents()
        {
            return $this->belongsToMany(Agent::class, 'time_slot_agents','time_slot_id','agent_id');
        }
}
