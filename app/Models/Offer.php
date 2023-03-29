<?php

namespace App\Models;
use Illuminate\Database\Eloquent\SoftDeletes;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Builder;

class Offer extends Model
{
    use SoftDeletes;

    /**
     * The attributes that should be hidden for arrays.
     *
     * @var array
     */
    protected $hidden = [
        'deleted_at',
   ];

    protected $table = 'offers';
    protected $fillable = [
        'name_ar',
        'name_en',
        'desc_ar',
        'desc_en',
        'picture',
        'old_price',
        'price',
        'start_date',
        'expire_date',
        'is_active',
        'is_banner',
        'product_id',
        'product_qty',
        'gift_product_id',
        'gift_product_qty',
        'agent_id',
        'offer_type',
    ];

    protected $appends=['image_url','name','desc'];

    /**
     * The "booted" method of the model.
     *
     * @return void
     */

    public function getImageUrlAttribute(){
        return asset($this->picture);
    }

//    protected static function boot()
//    {
//        parent::boot();
//
//        static::addGlobalScope('type', function (Builder $builder) {
//            $builder->where('products.type', 2);
//        });
//    }

    public function setTypeAttribute($value)
    {
        $this->attributes['type']=2;
    }
    public function getNameAttribute(){
        if(app()->getLocale()=='ar'){
            return $this->name_ar;
        }
        return $this->name_en;
    }
    public function getDescAttribute(){
        if(app()->getLocale()=='ar'){
            return $this->desc_ar;
        }
        return $this->desc_en;
    }
    public function product(){
        return $this->belongsTo(Product::class,'product_id');
    }
    public function gift_product(){
        return $this->belongsTo(Product::class,'gift_product_id');
    }


}
