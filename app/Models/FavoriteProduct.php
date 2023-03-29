<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class FavoriteProduct extends Model
{
    protected $table = 'favoriteProducts';
    protected $fillable = ['productId','userId'];

}
