<?php

namespace App\Http\Resources\City;

use Illuminate\Http\Resources\Json\ResourceCollection;

class WebCityCollection extends PaginatedCollection 
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

            // Here we transform any item in paginated items to a resource
            "status"=> true,
            "message"=> "success",
            'data' => $this->collection->transform(function ($path) {
                return new ListCityResource($path);
            }),

            'pagination' => $this->pagination,
        ];
    }
}
