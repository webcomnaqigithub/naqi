<?php

namespace App\Http\Resources;

use Illuminate\Http\Resources\Json\JsonResource;

class UserResource extends JsonResource
{
    /**
     * Transform the resource into an array.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return array
     */
    public function toArray($request)
    {
       return[
           'id'=>$this->id,
           'avatar_url'=>$this->avatar_url,
           'name'=>$this->name,
           'mobile'=>$this->mobile,
           'status'=>$this->status,
           'language'=>$this->language,
           'points'=>$this->finalPoints(),
           'agent_id'=>$this->agent_id,
           'agent'=>@AgentResource::make($this->agent),
           'created_at'=>$this->created_at,
           'updated_at'=>$this->updated_at,
       ];
    }
}
