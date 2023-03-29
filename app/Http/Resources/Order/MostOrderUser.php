<?php

namespace App\Http\Resources\Order;

use App\Http\Resources\AgentResource;
use Illuminate\Http\Resources\Json\JsonResource;

class MostOrderUser extends JsonResource
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
            'most_purchased_carton'=>PurchasedCarton::collection($this['most_purchased_carton']),
            'most_purchased_orders'=>PurchasedCarton::collection($this['most_purchased_orders']),
        ];
    }
}
