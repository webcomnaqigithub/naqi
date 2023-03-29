<?php

namespace App\Http\Resources\District;

use Illuminate\Http\Resources\Json\ResourceCollection;

class MobileDistrictCollection extends ResourceCollection 
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
            'data' => $this->collection->transform(function ($path) use ($request) {
                return new ListDistrictResource($path);
            }),
        ];
    }   /**
     * Transform the resource collection into an array.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return array
     */
    public function serializeForList($request)
    {
        return [
            "status"=> true,
            "message"=> "success",
            'data' => $this->collection->transform(function ($path) use ($request) {
                return (new ListDistrictResource($path))->serializeForList($request);
            }),
        ];
    }

}
