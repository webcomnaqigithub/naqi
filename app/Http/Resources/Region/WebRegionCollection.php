<?php

namespace App\Http\Resources\Region;

use Illuminate\Http\Resources\Json\ResourceCollection;

class WebRegionCollection extends PaginatedCollection 
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
                return new ListRegionResource($path);
            }),

            'pagination' => $this->pagination,
        ];
    }
}
