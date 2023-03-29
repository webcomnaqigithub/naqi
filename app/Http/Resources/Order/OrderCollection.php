<?php

namespace App\Http\Resources\Order;

use App\Http\Resources\Order\OrderResource;
use App\Http\Resources\PaginatedCollection;
use Illuminate\Http\Resources\Json\ResourceCollection;

class OrderCollection extends PaginatedCollection
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

            'data' => $this->collection->transform(function ($path) use ($request) {
                return new OrderResource($path,$request);
            }),

            'pagination' => $this->pagination,
        ];
    }
}
