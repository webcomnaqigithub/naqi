<?php

namespace App\Http\Resources;

use Illuminate\Http\Resources\Json\JsonResource;

class OrderProductResource extends JsonResource
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
            'productId'=>$this->productId,
            'amount'=>$this->amount,
            'price'=>$this->price,
            'total'=>$this->total,
            'product_name'=>@$this->product->name,
            'product_image_url'=>@$this->product->image_url,

        ];

    }
}
