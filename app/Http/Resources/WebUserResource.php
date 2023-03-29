<?php

namespace App\Http\Resources;

use App\Http\Resources\Address\AddressResource;
use App\Models\Address;
use Illuminate\Http\Resources\Json\JsonResource;

class WebUserResource extends JsonResource
{
    /**
     * Transform the resource into an array.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return array
     */
    public function toArray($request)
    {
        $lastOrderAddress=$this->lastOrder()?$this->lastOrder()->addressId:null;
        return[
            'id'=>$this->id,
            'avatar_url'=>$this->avatar_url,
            'name'=>$this->name,
            'mobile'=>$this->mobile,
            'status'=>$this->status,
            'language'=>$this->language,
            'points'=>$this->finalPoints(),
            'total_completed_order'=>$this->completedOrdersCount(),
            'agent_id'=>$this->agent_id,
            'agent'=>@AgentResource::make($this->agent),
            'created_at'=>$this->created_at,
            'updated_at'=>$this->updated_at,
            'address'=>@AddressResource::make($this->defaultAddress)
//            'last_order'=>@$lastOrder,
//            'address'=>@AddressResource::make(Address::find($lastOrderAddress))
        ];
    }
}
