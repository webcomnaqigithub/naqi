<?php

namespace App\Models;
use Tymon\JWTAuth\Contracts\JWTSubject;

use Illuminate\Database\Eloquent\Model;
use Grimzy\LaravelMysqlSpatial\Eloquent\SpatialTrait;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use Illuminate\Database\Eloquent\SoftDeletes;

use Grimzy\LaravelMysqlSpatial\Types\Polygon;
use Grimzy\LaravelMysqlSpatial\Types\LineString;
use Grimzy\LaravelMysqlSpatial\Types\Point;

class Agent extends Authenticatable implements JWTSubject
{

    use SpatialTrait,Notifiable,SoftDeletes;
    
    protected $guard = 'agent';
    protected $table = 'agents';

    protected $fillable = [
        'name','mobile','region','city','fcmToken','area','status','password','language','otp','minimum_cartons','englishSuccessMsg','arabicSuccessMsg','city_id','district_id','region_id'
    ];
    protected $spatialFields = [
        'area'
    ];
    protected $hidden = [
        'password','fcmToken','otp'
    ];

    public function search($lat, $lng)
    {
        $point =  new Point($lat, $lng);
        $agent = Agent::contains('area',$point)->where('status',1)
        ->orderBy('id','asc')->first();
        if($agent == null)
            return null;
            // return Agent::where('id',46)->first(); // this is used for demo testing with apple
        return $agent;
    }

    public function getDefault()
    {
        return Agent::where('id',203)->first(); // this is used for demo testing with apple
    }

    public function getJWTIdentifier()
    {
        return $this->getKey();
    }

    public function getJWTCustomClaims()
    {
        return ['id'];
    }
    public function setPasswordAttribute($password)
    {
        if ( !empty($password) ) {
            $this->attributes['password'] = bcrypt($password);
        }
    }

    public function city()
    {
        return $this->belongsTo(City::class,'city_id');
    }

    public function region()
    {
        return $this->belongsTo(Region::class,'region_id');
    }


    public function district()
    {
        return $this->belongsTo(\App\Models\District::class,'district_id');
    }

    public function products(){
        return $this->hasMany(AgentProduct::class,'agentId')->where('status',1);
    }

    public function orders(){
        return $this->hasMany(Order::class,'agentId');
    }
    public function areas(){
        return $this->hasMany(AgentArea::class,'agent_id');
    }

    public function DeliveryFlatLocation()
    {
        return $this->belongsToMany(DeliveryFlatLocation::class, 'delivery_flat_agents','agent_id','delivery_flat_location_id');
    }
}
