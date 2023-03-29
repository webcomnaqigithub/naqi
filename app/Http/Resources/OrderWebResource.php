<?php

namespace App\Http\Resources;

use App\Http\Resources\Address\AddressResource;
use App\Http\Resources\AgentResource;
use Carbon\Carbon;
use Illuminate\Http\Resources\DelegatesToResource;
use Illuminate\Http\Resources\Json\JsonResource;

class OrderWebResource extends JsonResource
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
            'sub_total'=>$this->sub_total,
            'pointsDiscount'=>$this->pointsDiscount,
            'couponDiscount'=>$this->pointsDiscount,
            'total_discount'=>$this->total_discount,
            'sub_total_2'=>$this->sub_total_2,
            'tax_ratio'=>$this->tax_ratio,
            'tax'=>@$this->tax,
            'delivery_cost'=>$this->delivery_cost,
            'total_amount'=>$this->amount,
            'use_points'=>$this->use_points,
            'points'=>$this->points,
            'status'=>$this->status,
            'type'=>$this->type,
            'status_label'=>$this->status_label,
            'time_slot'=>@OrderTimeSlotResource::make($this->timeSlot),
            'schedule_slot'=>@OrderScheduleSlotResource::make($this->scheduleSlot),
            'flat_location'=>@DeliveryFlatLocation::make($this->flatLocation ),
            'payment_type'=>@PaymentTypeResource::make($this->paymentType),
            'parent_order_id'=>$this->parent_order_id,
            'created_at'=>@Carbon::parse($this->assignDate)->format('Y-m-d h:m:i A'),
            'delegator'=>@DelegateResource::make($this->delegator_id),
            'agent'=>@AgentResource::make($this->agent),
            'customer'=>@CustomerResource::make($this->customer),
            'delivery_address'=>@AddressResource::make($this->address),
            'coupon'=>@CouponResource::make($this->coupona),
            'products'=>@OrderProductResource::collection($this->orderproducts),
            'offer'=>@OfferResource::make($this->offer),
        ];
    }
}
