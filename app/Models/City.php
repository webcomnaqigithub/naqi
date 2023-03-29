<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class City extends Model
{
    protected $table = 'cities_lite';
//    protected $guarded = ['id'];

    // protected $table = 'cities';
    // protected $fillable = ['id','englishName','arabicName','regionId','status','created_at','updated_at'];
    protected $appends=['name'];
    public function getNameAttribute(){
        if(app()->getLocale()=='ar'){
            return $this->arabicName;
        }
        return $this->englishName;
    }
    public function scopeSearch($q,$request)
    {
        if ($request->filled('q')) {
            $q->where(function($qq) use ($request){
                $qq->where('arabicName','LIKE', '%' . $request->q. '%')->orWhere('englishName','LIKE', '%' . $request->q . '%');
            });
        }

    }
}
