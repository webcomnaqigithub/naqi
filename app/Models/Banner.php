<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Banner extends Model
{
    protected $table = 'banners';
    protected $fillable = ['name','picture','status'];
    protected $appends=['image_url'];
    public function getImageUrlAttribute(){
        return asset($this->picture);
    }
}
