<?php

namespace App\Http\Resources\City;

use Illuminate\Http\Resources\Json\JsonResource;

class ListCityResource extends JsonResource
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
            "id"=> $this->id,
            "arabicName"=> $this->arabicName,
            "englishName"=> $this->englishName,
        ];
        
    }

}
