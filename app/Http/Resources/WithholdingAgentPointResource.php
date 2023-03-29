<?php

namespace App\Http\Resources;

use Carbon\Carbon;
use Illuminate\Http\Resources\Json\JsonResource;

class WithholdingAgentPointResource extends JsonResource
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
            'agent'=>AgentResource::make($this->agent),
            'from'=>Carbon::parse($this->from)->format('Y-m-d'),
            'to'=>Carbon::parse($this->to)->format('Y-m-d'),
            'created_at'=>Carbon::parse($this->created_at)->format('Y-m-d'),
            'updated_at'=>Carbon::parse($this->updated_at)->format('Y-m-d'),
        ];
//        return parent::toArray($request);
    }
}
