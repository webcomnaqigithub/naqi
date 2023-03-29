<?php

namespace App\Http\Resources\Order;

use Illuminate\Http\Resources\Json\JsonResource;

class CustomerResource extends JsonResource
{
    /**
     * Transform the resource into an array.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return array
     */

//    protected $from,$to;
//    public function __construct($resource,$from=null,$to=null)
//    {
//        parent::__construct($resource);
//        $this->from=$from;
//        $this->to=$to;
//    }

    public function toArray($request)
    {
//        $total_orders=0;
//        if($this->from && $this->to){
//            $total_orders= $this->orders()->whereBetween('created_at', [$request->from,$request->to])->count();
//        }else{
//            $total_orders=$this->orders()->count();
//        }

        return [
            'id'=>$this->id,
            'name'=>$this->name,
            'mobile'=>$this->mobile,
            'total_orders'=>$this->total_orders,
        ];
//        return parent::toArray($request);
    }
}
