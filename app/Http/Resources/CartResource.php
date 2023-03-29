<?php

namespace App\Http\Resources;

use Illuminate\Http\Resources\Json\JsonResource;

class CartResource extends JsonResource
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
            'id'=>$this->id,
            'userId'=>$this->userId,
            'agentId'=>$this->agentId,
            'address_id'=>$this->address_id,
            'addressType'=>$this->addressType,
            'sub_total'=>@$this->total,
            'total_qty'=>@$this->total_qty,
            'products'=>@CartProductsResource::collection($this->cartProducts),
        ];
//    "id": 251151,
//    "userId": "189474",
//    "agentId": 208,
//    "created_at": "2022-03-01 14:51:13",
//    "updated_at": "2022-03-01 14:51:13",
//    "addressType": "home",
//    "address_id": 92356
//        return parent::toArray($request);
    }
}
