<?php

namespace App\Http\Resources\Order;

use App\Http\Resources\AgentResource;
use Illuminate\Http\Resources\Json\JsonResource;

class PurchasedCarton extends JsonResource
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
            'purchased_carton'=>$this['most_purchased_carton'],
            'userId'=>$this->userId,
            'customer'=>CustomerResource::make($this->customer),
            'agent'=>AgentResource::make($this->customer->agent),

        ];
    }
}
