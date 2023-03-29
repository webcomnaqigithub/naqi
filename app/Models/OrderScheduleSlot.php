<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class OrderScheduleSlot extends Model
{
    use SoftDeletes;
    protected $fillable=['title_ar','title_en','code','is_active'];
    protected $appends=['title'];

    public function getTitleAttribute(){
        if(app()->getLocale()=='en'){
            return $this->title_en;
        }
        return $this->title_ar;
    }
}
