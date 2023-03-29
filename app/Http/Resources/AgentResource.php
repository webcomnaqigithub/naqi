<?php

namespace App\Http\Resources;

use App\Http\Resources\City\ListCityResource;
use App\Http\Resources\Region\RegionResource;
use Illuminate\Http\Resources\Json\JsonResource;

class AgentResource extends JsonResource
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
            "id"=>$this->id,
            "name"=>$this->name,
            "mobile"=>$this->mobile,
            "city"=>$this->city,
            "region"=>$this->region,
//            "region"=>ListCityResource::make($this->region()->get()),
            'delivery_flat_location'=>DFL_WithoutAgents::collection($this->DeliveryFlatLocation),

        ];
    }
}
