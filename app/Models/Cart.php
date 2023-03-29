<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Cart extends Model
{
    protected $table = 'cart';
    protected $fillable = ['agentId','addressId','userId','addressType','address_id'];
    protected $appends=['total','total_qty'];
    public function cartProducts(){
        return $this->hasMany(CartProduct::class,'cartId');
    }

    public function address(){
        return $this->belongsTo(Address::class,'address_id');
    }
    public function getTotalAttribute()
    {
        return $this->cartProducts()->get()->sum('total');
    }
    public function getTotalQtyAttribute()
    {
        return $this->cartProducts()->get()->sum('amount');
    }
}
