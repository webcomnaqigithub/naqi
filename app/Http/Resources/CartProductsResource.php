<?php

namespace App\Http\Resources;

use Illuminate\Http\Resources\Json\JsonResource;

class CartProductsResource extends JsonResource
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
            'cart_product_id'=>$this->id,
            'product_id'=>@$this->product->id,
            'name'=>@$this->product->name,
            'image_url'=>@$this->product->image_url,
            'is_favorite'=>@$this->product->is_favorite,
            'price'=>@$this->price,
            'amount'=>@$this->amount,
            'total'=>@$this->total,
        ];
//        return parent::toArray($request);
    }
}
