<?php

namespace App\Http\Resources\Order;

use App\Http\Resources\Address\AddressResource;
use App\Http\Resources\Order\CustomerResource;

use App\Http\Resources\OrderProductResource;
use App\Http\Resources\OrderTimeSlotResource;
use App\Http\Resources\PaymentTypeResource;
use Carbon\Carbon;
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
        $delivery_period=0;
        if($this->type=="offer"){
            $product_qty=($this->offer && $this->offer->product_qty)?$this->offer->product_qty:0;
        }else{
            $product_qty=  $this->totalqty();
        }
        if($this->completionDate != null){
            $delivery_period=Carbon::parse($this->assignDate)->diffinhours(Carbon::parse($this->completionDate));
        }

        return [
            'id'=>$this->id,
            'type'=>$this->type,
            'total_qty'=>$product_qty,
            'clientEvaluation'=>$this->clientEvaluation,
            'total_amount'=>$this->amount,
            'delivery_period'=>$delivery_period,
            'status'=>$this->status,
            'coupon'=>$this->coupona,
            'total_discount'=>$this->total_discount,
            'client_delivery_date'=>$this->client_delivery_date,
            'order_created_by'=>$this->creatable_type, //$this->createdBy(),
            'order_time_slot'=>OrderTimeSlotResource::make($this->timeSlot),
            'address'=>AddressResource::make($this->address),
            'customer'=>CustomerResource::make($this->customer),
            'agent'=>UserResource::make($this->agent),
            'delegator'=>UserResource::make($this->delegator),
            'payment_type'=>@PaymentTypeResource::make($this->paymentType),
            'products'=>@OrderProductResource::collection($this->orderproducts),
        ];
    }
}
