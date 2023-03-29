<?php

namespace App\Http\Controllers\Api\Customer;

use App\Http\Controllers\Controller;
use App\Models\Coupon;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;

class CouponController extends Controller
{
    public function check(Request $request)
    {
        $data = $request->only(['code','amount','userId','agent_id', 'carton_qty']);
        $rules = [
            'agent_id' => 'required',
            'code' => 'required',
            'amount' => 'required',
            'carton_qty' => 'required',
        ];
        $validator = Validator::make($data, $rules);
        if ($validator->fails()) {
            return $this->newResponse(false,$this->validationHandle($validator->messages()));
        }

        $customer=$request->user('customer');
        if($customer){
            $agentId=$request->agent_id;
             $coupon = Coupon::where('code',$request->code)->where('status',1)
                ->where('notBefore', '<=',Carbon::today())
                ->where('notAfter', '>',Carbon::today())->where(function ($q) use ($agentId){
                    $q->where('target_agent','all')->orWhereHas('agents',function($q) use ($agentId){
                        $q->where('agent_id',$agentId);
                    });
                })->first();

            if($coupon){
                if($coupon->is_used_one_time){
                   $used_before= $customer->orders()->whereIn('status',['created','completed','on_the_way'])
                       ->where('coupon_id',$coupon->id)
                       ->exists();
                   if($used_before){
                       return $this->newResponse(false,__('api.coupon_used_before'));
                   }
                }
                if($coupon->minAmount > $request->carton_qty)
                {
                    return $this->newResponse(false,__('api.coupon_exceed_carton',['carton'=>$coupon->minAmount]));
                }elseif($coupon->value > $request->amount && $coupon->type == 'flat'){
                    return $this->newResponse(false,__('api.coupon_exceed_value',['value'=>$coupon->value]));
                }else{
                    if($coupon->type == 'percentage')
                    {
                        $discount_value = ((float)$request->amount * (float)$coupon->value);
                        return $this->newResponse(true,__('api.coupon_used_successfully'),'coupon',[
                            'id'=>$coupon->id,
                            'type'=>$coupon->type,
                            'min_amount'=>(float)$coupon->minAmount,
                            'coupon_discount'=>(float)$coupon->value,
                            'expire_date'=>Carbon::parse($coupon->notAfter)->format('Y-m-d'),
                        ],[

                            'discount_value'=>(float)$discount_value,
                        ]);
                    }else{

                        return $this->newResponse(true,__('api.coupon_used_successfully'),'coupon',[
                            'id'=>$coupon->id,
                            'type'=>$coupon->type,
                            'min_amount'=>(float)$coupon->minAmount,
                            'coupon_discount'=>(float)$coupon->value,
                            'expire_date'=>Carbon::parse($coupon->notAfter)->format('Y-m-d'),
                        ],[

                            'discount_value'=>(float)$coupon->value,
                        ]);
                    }
                }
            }else{
                return $this->newResponse(false,__('api.not_valid_coupon'));
            }

        }else{
            return $this->newResponse(false,__('api.no_customer'));
        }







                return $this->response(true,'success',$code);




    }
}
