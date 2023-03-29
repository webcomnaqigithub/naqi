<?php

namespace App\Models;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Builder;
use App\Models\Favorite;
class Product extends Model
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

    protected $table = 'products';
    protected $fillable = ['arabicName','englishName','picture','homePrice','mosquePrice','otherPrice','officialPrice','status','type'];
    protected $appends=['image_url','name','is_favorite'];

    /**
     * The "booted" method of the model.
     *
     * @return void
     */
//    protected static function boot()
//    {
//        parent::boot();
//
//        static::addGlobalScope('type', function (Builder $builder) {
////            $builder->where('products.type', 1);
//        });
//    }
    public function getImageUrlAttribute(){
        return asset($this->picture);
    }
    public function getNameAttribute(){
        if(app()->getLocale()=='ar'){
            return $this->arabicName;
        }
          return $this->englishName;
    }

    public function favorites()
    {
        return $this->morphMany(\App\Models\Favorite::class, 'content');
    }
    public function getIsFavoriteAttribute()
    {
        $customer= auth('customer')->user();
        if ($customer) {
            $user_id = $customer->id;
            if ($this->favorites()->where('user_id', $user_id)->exists()) {
                return true;
            }
        }
        return false;
    }



}
