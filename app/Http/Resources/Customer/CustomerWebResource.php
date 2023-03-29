<?php

namespace App\Http\Resources\Customer;

use App\Http\Resources\City\ListCityResource;
use App\Http\Resources\PaginatedCollection;
use Illuminate\Http\Resources\Json\JsonResource;

class CustomerWebResource extends PaginatedCollection
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

            // Here we transform any item in paginated items to a resource
            'data' => $this->collection->transform(function ($path) {
                return new CustomerResource($path);
            }),
            'pagination' => $this->pagination,
        ];
    }
}
