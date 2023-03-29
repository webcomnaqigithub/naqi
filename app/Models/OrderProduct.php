<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class OrderProduct extends Model
{
    protected $table = 'orderProducts';
    protected $fillable = ['orderId','productId','amount','price','total'];

    public function product(){
        return $this->belongsTo(Product::class,'productId');
    }
}
