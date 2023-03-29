<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class CpFcmNotification extends Model
{
    protected $table = 'cp_fcm_notifications';
    protected $fillable = [
        'sender_type', //admin or agent
        'industry_id',
        'sender_ids',
        'title',
        'body',
        'receiver_ids',
        'users_count',
    ];

    public function industry()
    {
        return $this->belongsTo(Industry::class);
    }

    public function agent()
    {
        return $this->belongsTo(Agent::class);
    }
}
