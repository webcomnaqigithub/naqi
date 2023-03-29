<?php

namespace App\Http\Resources\Region;

use Illuminate\Http\Resources\Json\JsonResource;

class ListRegionResource extends JsonResource
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
            "arabicName"=> $this->arabicName,
            "englishName"=> $this->englishName,
        ];
        
    }

    public function serializeForMobile($request)
    {
        // $data =  RegionResource::collection($data)->map->serializeForMobile($request);
        return parent::toArray($request);

         return [
            "status"=> true,
        ];
    }
}
