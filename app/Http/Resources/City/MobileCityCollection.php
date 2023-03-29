<?php

namespace App\Http\Resources\City;

use Illuminate\Http\Resources\Json\ResourceCollection;

class MobileCityCollection extends ResourceCollection 
{
    /**
     * Transform the resource collection into an array.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return array
     */
    public function toArray($request)
    {
        return [
            "status"=> true,
            "message"=> "success",
            'data' => $this->collection->transform(function ($path) {
                return new ListCityResource($path);
            }),
        ];
    }

}
