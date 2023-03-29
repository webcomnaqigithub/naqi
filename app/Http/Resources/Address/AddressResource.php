<?php

namespace App\Http\Resources\Address;

use App\Http\Resources\AreaRegionCityDistrictResource;
use Illuminate\Http\Resources\Json\JsonResource;

class AddressResource extends JsonResource
{
    // public static $wrap = 'user';

    /**
     * Transform the resource into an array.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return array
     */
    public function toArray($request)
    {
        return [
            "id"=> $this->id,
            "customer_id"=> $this->userId,
            "name"=> $this->name,
            "varchar"=> $this->varchar,
            "lat"=> $this->lat,
            "lng"=> $this->lng,
            "default"=> $this->default,
            "status"=> $this->status,
            'region_id'=>$this->region_id,
            'city_id'=>$this->city_id,
            'district_id'=>$this->district_id,
            'type'=>$this->type,
            'type_label'=>$this->type_label,
            'region'=>@AreaRegionCityDistrictResource::make($this->region),
            'city'=>@AreaRegionCityDistrictResource::make($this->city),
            'district'=>@AreaRegionCityDistrictResource::make($this->district),
        ];

    }

}
