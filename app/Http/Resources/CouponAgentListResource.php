<?php

namespace App\Http\Resources;

use Illuminate\Http\Resources\Json\JsonResource;

class CouponAgentListResource extends JsonResource
{
    /**
     * Transform the resource into an array.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return array
     */
    public function toArray($request)
    {
//        return $this->agent;
        return [
            "id"=>@$this->agent->id,
            "name"=>@$this->agent->name,
        ];
//        return parent::toArray($request);
    }
}
