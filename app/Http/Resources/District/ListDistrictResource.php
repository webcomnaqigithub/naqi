<?php

namespace App\Http\Resources\District;

use Illuminate\Http\Resources\Json\JsonResource;

class ListDistrictResource extends JsonResource
{

    /**
     * Transform the resource into an array.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return array
     */
    public function toArray($request)
    {
       return parent::toArray($request);
    }
    /**
     * Transform the resource into an array.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return array
     */
    public function serializeForList($request)
    {
        return [
            "id"=> $this->id,
            "arabicName"=> $this->arabicName,
            "englishName"=> $this->englishName,
        ];
        
    }

}
