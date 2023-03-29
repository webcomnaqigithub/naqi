<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class PaymentType extends Model
{
    use SoftDeletes;
    protected $fillable=['name_ar','name_en','icon','is_active'];
    protected $appends=['icon_url','name'];
    public function getIconUrlAttribute(){
        return asset('payment_icons/'.$this->icon);
    }

    public function getNameAttribute(){
        if(app()->getLocale()=='en'){
            return $this->name_en;
        }
        return $this->name_ar;
    }

}
