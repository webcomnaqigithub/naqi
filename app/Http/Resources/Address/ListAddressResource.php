<?php

namespace App\Http\Resources\Address;

use App\Http\Resources\CustomerResource;
use Illuminate\Http\Resources\Json\JsonResource;

class ListAddressResource extends JsonResource
{
    // public static $wrap = 'user';

    /**
     * Transform the resource into an array.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return array
     */
    public function toArray($request)
    {
        return [
            "id"=> $this->id,
            "mobile"=> $this->mobile,
            "clientName"=> $this->clientName,
            "userId"=> $this->userId,
            "name"=> $this->name,
            "varchar"=> $this->varchar,
            "lat"=> $this->lat,
            "lng"=> $this->lng,
            "default"=> $this->default,
            "status"=> $this->status,
            'type'=>$this->type,
            'type_label'=>$this->type_label,
            "customer"=>@CustomerResource::make($this->customer,false),
            "created_at"=> $this->created_at,
            "updated_at"=> $this->updated_at,
            "deleted_at"=> $this->deleted_at,
            "agent_id"=> $this->agent_id,

        ];

    }

}
