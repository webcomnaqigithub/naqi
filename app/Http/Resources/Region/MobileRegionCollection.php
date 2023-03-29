<?php

namespace App\Http\Resources\Region;

use Illuminate\Http\Resources\Json\ResourceCollection;

class MobileRegionCollection extends ResourceCollection 
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
                return new ListRegionResource($path);
            }),
        ];
    }

}
