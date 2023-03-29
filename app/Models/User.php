<?php

namespace App\Models;

use Illuminate\Contracts\Auth\MustVerifyEmail;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use Laravel\Sanctum\HasApiTokens;
use Tymon\JWTAuth\Contracts\JWTSubject;
use Laravel\Scout\Searchable;
use Laratrust\Traits\LaratrustUserTrait;


class User extends Authenticatable
{

    use LaratrustUserTrait;
    use Notifiable,HasApiTokens;
    protected $guard = 'customer';
    /**
     * The attributes that are mass assignable.
     *
     * @var array
     */
    protected $fillable = [
        'name','mobile','fcmToken','status','password','lat','lng','points','language','city_id','district_id','region_id'
    ];

    /**
     * The attributes that should be hidden for arrays.
     *
     * @var array
     */
    protected $hidden = [
        'password', 'remember_token',
    ];

    /**
     * The attributes that should be cast to native types.
     *
     * @var array
     */
    protected $casts = [
        'mobile_verified_at' => 'datetime',
    ];

//    public function getJWTIdentifier()
//    {
//        return $this->getKey();
//    }

//    public function getJWTCustomClaims()
//    {
//        return [];
//    }
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

    public function addresses()
    {
        return $this->hasMany(\App\Models\Address::class, 'userId');
    }
}
