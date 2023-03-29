<?php

namespace App\Http\Resources;

use Illuminate\Http\Resources\Json\JsonResource;

class CouponAgentResource extends JsonResource
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
            'target_agent'=>$this->target_agent,
            'created_at'=>$this->created_at,
            'update_at'=>$this->update_at,
            'is_used_one_time'=>$this->is_used_one_time,
            'agents'=>@AgentResource::collection($this->agents)

        ];
//        return parent::toArray($request);
    }
}
