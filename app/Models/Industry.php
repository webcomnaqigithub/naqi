<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use Laratrust\Traits\LaratrustUserTrait;
use Tymon\JWTAuth\Contracts\JWTSubject;
use Grimzy\LaravelMysqlSpatial\Eloquent\SpatialTrait;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;

class Industry extends Authenticatable implements JWTSubject
{
    use LaratrustUserTrait;
    use Notifiable,SoftDeletes;
    protected $table = 'industry';

    protected $guard = 'industry';

    protected $fillable = [
        'name','mobile','fcmToken','status','password','api_token','isAdmin','language','otp'
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
}
