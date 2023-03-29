<?php

namespace App\Http\Resources;

use Illuminate\Http\Resources\Json\JsonResource;

class OfferResource extends JsonResource
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
            'desc'=>$this->desc,
            'name_ar'=>$this->name_ar,
            'name_en'=>$this->name_en,
            'desc_ar'=>$this->desc_ar,
            'desc_en'=>$this->desc_en,
            'image_url'=>$this->image_url,
            'old_price'=>$this->old_price,
            'price'=>$this->price,
            'start_date'=>$this->start_date,
            'expire_date'=>$this->expire_date,
            'is_active'=>$this->is_active,
            'is_banner'=>$this->is_banner,
            'product'=>ProductResource::make($this->product),
            'product_qty'=>$this->product_qty,
            'gift_product'=>ProductResource::make($this->gift_product),
            'gift_product_qty'=>$this->gift_product_qty,
            'product_id'=>$this->product_id,
            'gift_product_id'=>@$this->gift_product_id,
            'agent_id'=>@$this->agent_id,
            'offer_type'=>$this->offer_type,
        ];
//        return parent::toArray($request);
    }
}
