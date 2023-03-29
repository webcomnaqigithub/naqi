<?php

namespace App\Http\Resources;

use Illuminate\Http\Resources\Json\JsonResource;

class DeliveryFlatLocation extends JsonResource
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
            'title'=>$this->title,
            'title_en'=>$this->title_en,
            'title_ar'=>$this->title_ar,
            'delivery_cost'=>$this->delivery_cost,
            'agents'=>@AgentResource::collection($this->agents)
        ];
    }
}
