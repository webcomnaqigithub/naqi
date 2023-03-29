<?php

namespace App\Http\Resources\Coupon;

use App\Http\Resources\Customer\CustomerResource;
use App\Http\Resources\PaginatedCollection;
use Illuminate\Http\Resources\Json\JsonResource;

class WebCouponResource extends PaginatedCollection
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
                return new ListCouponResource($path);
            }),
            'pagination' => $this->pagination,
        ];
    }
}
