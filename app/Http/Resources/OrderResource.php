<?php

namespace App\Http\Resources;

use Illuminate\Http\Resources\Json\JsonResource;

class OrderResource extends JsonResource
{
    /**
     * Transform the resource into an array.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return array
     */
    public function toArray($request)
    {
        $product_qty=0;
        if($this->type=="offer"){
            $product_qty=($this->offer && $this->offer->product_qty)?$this->offer->product_qty:0;
        }else{
            $product_qty=  $this->totalqty();
        }
        return [
            'id'=>$this->id,
            'type'=>$this->type,
            'total_qty'=>$product_qty,
            'total_amount'=>$this->amount,
            'status'=>$this->status,
            'status_label'=>$this->status_label,
            'created_at'=>$this->created_at->format('Y-m-d h:i A'),
            'payment_type'=>@PaymentTypeResource::make($this->paymentType),
            'products'=>@OrderProductResource::collection($this->orderproducts),
            'offer'=>@($this->offer)?OfferResource::make($this->offer):null,
        ];

    }
}
