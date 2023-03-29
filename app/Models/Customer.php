<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use Laravel\Sanctum\HasApiTokens;
use Illuminate\Support\Facades\Hash;


class Customer extends Authenticatable
{
    use Notifiable,HasApiTokens,SoftDeletes;

    protected $table='users';
    protected $fillable = [
        'name','mobile','password','status','mobile_verified_at','fcmToken',
        'lat','lng','points','language','city_id','district_id','region_id','avatar',
        'agent_id'
    ];

    protected $hidden = [
       // 'password', 'remember_token',
    ];

    protected $casts = [
        'mobile_verified_at' => 'datetime',
    ];
    protected $appends=['is_verified','has_default_address','avatar_url'];
    public function setPasswordAttribute($password)
    {
        if ( !empty($password) ) {
            $this->attributes['password'] = Hash::make($password);
        }
    }
    public function favorites(){
        return $this->hasMany(Favorite::class,'user_id');
    }


    public function addresses(){
        return $this->hasMany(Address::class,'userId');
    }

    public function defaultAddress(){
        return $this->hasOne(Address::class,'userId')->where('default',1);
    }

    public function getIsVerifiedAttribute(){
        if($this->status==1){
            return true;
        }
        return false;
    }
    public function getHasDefaultAddressAttribute(){
        if($this->defaultAddress()->exists()){
            return true;
        }
        return false;
    }

    public function orders(){
        return $this->hasMany(Order::class,'userId');
    }
    public function complaints(){
        return $this->hasMany(Complain::class,'userId');
    }
    public function points(){
        return $this->hasMany(Points::class,'clientId');
    }

    public function finalPoints(){
            return (($this->points()->where('type','bonus')->sum('points'))-($this->points()->where('type','discount')->sum('points')));
    }
    public function getAvatarUrlAttribute(){
        if($this->avatar){
        return asset('uploads/'.$this->avatar);
        }
        return asset('uploads/avatar.jpg');
    }

    public function agent(){
        return $this->belongsTo(Agent::class,'agent_id')->withTrashed();
    }
    public function completedOrdersCount(){
        return $this->orders()->where('status','completed')->count();
    }
    public function lastOrder(){
        return $this->orders()->orderByDesc('created_at')->first();
    }

    public function city(){
        return $this->belongsTo(City::class);
    }
}
