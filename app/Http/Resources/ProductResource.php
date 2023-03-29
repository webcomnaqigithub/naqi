<?php

namespace App\Http\Resources;

use Illuminate\Http\Resources\Json\JsonResource;

class ProductResource extends JsonResource
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
            'name'=>$this->name,
            'image_url'=>$this->image_url,
            'price'=>$this->price,
            'min_order_qty'=>@$this->min_order_qty,
            'is_favorite'=>$this->is_favorite,
        ];
//        return parent::toArray($request);
    }
}
