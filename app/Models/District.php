<?php

namespace App\Models;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Notifications\Notifiable;

class District extends Model
{
    use Notifiable;
    protected $table = 'districts_lite';
//    protected $guarded = ['id'];
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
