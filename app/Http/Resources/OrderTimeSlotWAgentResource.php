<?php

namespace App\Http\Resources;

use Carbon\Carbon;
use Illuminate\Http\Resources\Json\JsonResource;

class OrderTimeSlotWAgentResource extends JsonResource
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
            'start_at'=>Carbon::parse($this->start_at)->format('h:m A'),
            'end_at'=>Carbon::parse($this->end_at)->format('h:m A'),
        ];
//        return parent::toArray($request);
    }
}
