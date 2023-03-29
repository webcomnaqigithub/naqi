<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class PostponeOrderRequest extends Model
{
    protected $guarded=[];
    protected $appends=['status_text'];
    public function order(){
        return $this->belongsTo(Order::class,'order_id');
    }

    public function reason(){
        return $this->belongsTo(PostponeReason::class,'reason_id');
    }
    public function delegator(){
        return $this->belongsTo(Delegator::class,'delegator_id');
    }
    public function getStatusTextAttribute(){
        return [
            'opened'=>__('api.opened'),
            'closed'=>__('api.closed'),

        ][$this->status];
    }
}
