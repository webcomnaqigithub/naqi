<?php

namespace App\Http\Resources;

use App\Models\Agent;
use App\Models\Customer;
use App\Models\Delegator;
use App\Models\Product;
use Illuminate\Http\Resources\Json\JsonResource;

class OrderReviewResource extends JsonResource
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
            'orderNumber'=>$this->id,
            'clientComment'=>$this->reviewText,
            ///// 'products'=>ProductResource::collection($this->products),
            'created_at'=>$this->created_at->format('Y-m-d h:i A'),
            'delegatorReview'=> $this->delegatorReview,
            'agentName'=>$this->agentName,
            // 'product' => Product::get('id', 'name'),
            'productsReview' => $this->productsReview,
            'delegatorName' => $this->delegatorName,
            'client' => $this->clientName,
            'delegatorReviewText' => $this->delegatorReviewText,
            'serviceReview' => $this->serviceReview,
        ];

    }
}
