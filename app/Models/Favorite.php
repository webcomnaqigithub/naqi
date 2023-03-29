<?php

namespace App\Models;
use Illuminate\Database\Eloquent\Model;

class Favorite extends Model
{
    protected $table="favorites";
    protected $fillable=[
        'content_type',
        'content_id',
        'user_id',
        'created_at',
        'updated_at',
    ];
    public function content(){
        return $this->morphTo();
    }
}
