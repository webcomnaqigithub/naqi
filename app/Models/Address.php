<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use Laravel\Scout\Searchable;

class Address extends Model
{
    use SoftDeletes;
    // use Searchable;
    protected $table = 'address';

    protected $fillable = ['name','userId','type','lat','lng','default','region_id','city_id','district_id','agent_id'];

    protected $appends=['type_label'];
     /**
     * Get the value used to index the model.
     *
     * @return mixed
     */
//    public function getScoutKey()
//    {
//        return $this->id;
//    }

    /**
     * Get the key name used to index the model.
     *
     * @return mixed
     */
//    public function getScoutKeyName()
//    {
//        return 'id';
//    }
//    public function searchableAs()
//    {
//        return 'address';
//    }


    public function getTypeLabelAttribute(){
        return [
            'mosque'=>__('api.mosque'),
            'home'=>__('api.home'),
            'company'=>__('api.company'),
        ][$this->type];
    }

    public function region()
    {
        return $this->belongsTo(Region::class,'region_id');
    }


    public function city()
    {
        return $this->belongsTo(City::class,'city_id');
    }




    public function district()
    {
        return $this->belongsTo(\App\Models\District::class,'district_id');
    }
    public function customer(){
        return $this->belongsTo(Customer::class,'userId');
    }
}
