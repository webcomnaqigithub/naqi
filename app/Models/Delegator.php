<?php

namespace App\Models;

use Tymon\JWTAuth\Contracts\JWTSubject;

use Illuminate\Database\Eloquent\Model;
use Grimzy\LaravelMysqlSpatial\Eloquent\SpatialTrait;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;


use Grimzy\LaravelMysqlSpatial\Types\Polygon;
use Grimzy\LaravelMysqlSpatial\Types\LineString;
use Grimzy\LaravelMysqlSpatial\Types\Point;
use Illuminate\Database\Eloquent\SoftDeletes;


class Delegator extends Authenticatable implements JWTSubject
{
    use Notifiable, SoftDeletes;

    protected $guard = 'delegator';

    protected $fillable = [
        'name','mobile','region','city','fcmToken','status','password','api_token','otp','language','city_id','district_id','region_id','agentId'
    ];

    protected $hidden = [
        'password','fcmToken','otp'
    ];

    public function getJWTIdentifier()
    {
        return $this->getKey();
    }

    public function getJWTCustomClaims()
    {
        return [];
    }
    public function setPasswordAttribute($password)
    {
        if ( !empty($password) ) {
            $this->attributes['password'] = bcrypt($password);
        }
    }



    public function agent()
    {
        return $this->belongsTo(Agent::class,'agentId');
    }

    public function city()
    {
        return $this->belongsTo(City::class,'city');
    }

    public function region()
    {
        return $this->belongsTo(Region::class,'region');
    }


    public function district()
    {
        return $this->belongsTo(\App\Models\District::class,'district_id');
    }


    public function orders(){
        return $this->hasMany(Order::class,'delegatorId');
    }
}
