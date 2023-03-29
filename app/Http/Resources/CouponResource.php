<?php

namespace App\Http\Resources;

use Illuminate\Http\Resources\Json\JsonResource;

class CouponResource extends JsonResource
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
            'code'=>$this->code,
            'type'=>$this->type,
            'value'=>$this->value,
            'used'=>$this->used,
            'minAmount'=>$this->minAmount,
            'notBefore'=>$this->notBefore,
            'notAfter'=>$this->notAfter,
            'status'=>$this->status,
        ];
//        return parent::toArray($request);
    }
}
