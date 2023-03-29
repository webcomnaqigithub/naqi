<?php

namespace App\Http\Resources;

use App\Http\Resources\City\ListCityResource;
use App\Http\Resources\Region\RegionResource;
use Illuminate\Http\Resources\Json\JsonResource;

class DelegateResource extends JsonResource
{
    /**
     * Transform the resource into an array.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return array
     */
    public function toArray($request)
    {
        return [
            'id'=>$this->id,
            'name'=>$this->name,
            'mobile'=>$this->mobile,
            'region'=>RegionResource::make($this->region),
            'city'=>ListCityResource::make($this->city),
            'agent'=>AgentResource::make($this->agent),
        ];
//        return parent::toArray($request);
    }
}
