<?php

namespace App\Http\Controllers\Api\Customer;

use App\Http\Controllers\Controller;
use App\Http\Resources\OrderRejectReasonsResource;
use App\Http\Resources\OrderResource;
use App\Jobs\SendGoogleNotification;
use App\Models\Agent;
use App\Models\Cart;
use App\Models\CartProduct;
use App\Models\Coupon;
use App\Models\Customer;
use App\Models\DeliveryFlatLocation;
use App\Models\Offer;
use App\Models\Order;
use App\Models\OrderProduct;
use App\Models\Points;
use App\Models\RejectionReason;
use App\Models\Setting;
use App\Models\User;
use App\Notifications\OrderCreated;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;
use Illuminate\Validation\Rules\RequiredIf;
use mysql_xdevapi\Exception;
use Notification;
use DB;
use App\Notifications\OrderCancelled;
use App\Notifications\OrderAssigned;
use App\Notifications\OrderCompleted;
class OrderController extends Controller
{

    public function place(Request $request){
        $data = $request->only([
            'cart_id',
            'coupon_id',
            'time_slot_id',
            'schedule_slot_id',
            'flat_location_id',
            'payment_type_id',
            'type',
            'delivery_schedule_date',
            'is_paid',
            'payment_transaction_id',
            'delivery_date',
            'points',
            'use_points',
        ]);
        $rules = [
            'cart_id' => 'required|numeric|exists:cart,id',
            'coupon_id' => 'nullable|numeric|exists:coupons,id',
            'time_slot_id' => 'required|numeric|exists:time_slots,id',
            'schedule_slot_id' => 'required|numeric|exists:order_schedule_slots,id',
            'flat_location_id' => 'required|numeric|exists:delivery_flat_locations,id',
            'payment_type_id' => 'required|numeric|exists:payment_types,id',
            'type' => 'required|in:normal,offer',
            'delivery_date'=>'required|in:immediately,schedule',
            'delivery_schedule_date' =>  new RequiredIf($request->delivery_date =='schedule' ),
            'is_paid' => 'nullable',
            'payment_transaction_id' => 'nullable',
            'points' =>  new RequiredIf($request->use_points ==true ||$request->use_points ==1 ),
            'use_points' => 'required',
        ];
        $validator = Validator::make($data, $rules);
        if ($validator->fails()) {
            return $this->newResponse(false,$this->validationHandle($validator->messages()));
        }
        try{
                $user=auth()->user();
                $cart=Cart::find($request->cart_id);
                 $delivery_cost=0;
                 $coupon_discount=0;
                 $point_discount_value=0;
                $use_customer_points=false;
                 // coupon discount
                if($request->coupon_id){
                    $coupon_discount=$this->getCouponDiscount($request->coupon_id,$cart->total);
                    if($coupon_discount ==-1){
                            return $this->newResponse(false,__('api.not_valid_coupon'));
                    }
                }
                // end coupon discount

            // delivery cost
            $delivery_flat_location_model=DeliveryFlatLocation::find($request->flat_location_id);
            if($delivery_flat_location_model){
                $delivery_cost_per_carton=$delivery_flat_location_model->delivery_cost;
                $delivery_cost=floatval(($cart->total_qty *$delivery_cost_per_carton));
            }
            // end delivery cost



            $amount_after_coupon_discount=floatval(($cart->total-$coupon_discount));

            //  tax calculation
            $tax_ratio=Setting::valueOf('tax_ratio',0);
            $tax=(($tax_ratio/100)*$amount_after_coupon_discount);
            //  end calculation

            $net_total_amount_before_points=$amount_after_coupon_discount+$tax+$delivery_cost;

            // start point discount
                if($request->use_points){
                    if($user->points >=$request->points ){
                        $request->points = $this->convertEnglishNumber($request->points);
                         $min_points_to_replace = Setting::valueOf('min_points_to_replace');
                        if($request->points < $min_points_to_replace){
                            return $this->newResponse(false,__('api.system_min_point_to_use',['points_num'=>$min_points_to_replace]));
                        }else{
                            $points_per_1_sar=Setting::valueOf('replace_points');
                            $points_money=($request->points/$points_per_1_sar);
                            if($points_money<=$net_total_amount_before_points){
                                $point_discount_value=$points_money;
                                $use_customer_points=true;
                            }else{
                                return $this->newResponse(false,__('api.use_less_points_in_order'));
                            }
                        }

                    }else{
                        return $this->newResponse(false,__('api.not_enough_points'));
                    }
                }
            // end point discount
                $total_discount=$coupon_discount+$point_discount_value;
                $total_order_amount=floatval($net_total_amount_before_points-$point_discount_value);

                $order_data['userId']=$user->id;
                $order_data['assignDate'] = Carbon::now();;
                $order_data['addressId']=$cart->address_id;
                $order_data['agentId']=$cart->agentId;
                $order_data['coupon_id']=$request->coupon_id;
                $order_data['time_slot_id']=$request->time_slot_id;
                $order_data['schedule_slot_id']=$request->schedule_slot_id;
                $order_data['flat_location_id']=$request->flat_location_id;
                $order_data['payment_type_id']=$request->payment_type_id;
                $order_data['type']=$request->type;
                $order_data['sub_total']=$cart->total;
                $order_data['total_discount']=$total_discount;
                $order_data['sub_total_2']=$amount_after_coupon_discount;
                $order_data['tax_ratio']=$tax_ratio;
                $order_data['tax']=$tax;
                $order_data['amount']=$total_order_amount;
                $order_data['delivery_cost']=$delivery_cost;
                $order_data['use_points']=$request->use_points;
                $order_data['points']=$request->points;
                $order_data['pointsDiscount']=$point_discount_value;
                $order_data['couponDiscount']=$coupon_discount;
                $order_data['delivery_schedule_date']=Carbon::parse($request->delivery_schedule_date)->format('Y-m-d');
                $order_data['is_paid']=$request->is_paid;
                $order_data['payment_transaction_id']=$request->payment_transaction_id;
                $order_data['paymentReference']=$request->payment_transaction_id;

                     $order=Order::create($order_data);
                     if($order){
                            $order->creatable()->associate($user)->save();
                         if($request->use_points && $use_customer_points){
                             $pointsRecord=new Points();
                             $pointsRecord->clientId = $order->userId;
                             $pointsRecord->type = 'discount';
                             $pointsRecord->points = $request->points;
                             $pointsRecord->orderId = $order->id;
                             $pointsRecord->agentId = $order->agentId;
                             $pointsRecord->save();
                             $user_points_after_discount_it=intval($user->points-$request->points);
                             $user->update(['points'=>$user_points_after_discount_it]);
                         }
                             $orderProducts = [];
                             // add products to order
                             foreach($cart->cartProducts as $cartProduct)
                             {
                                 $orderProducts[] =[
                                     'orderId' => $order->id,
                                     'productId' => $cartProduct->productId,
                                     'amount' => $cartProduct->amount,
                                     'price' => $cartProduct->price,
                                     'total' => $cartProduct->total,
                                     'created_at' => Carbon::now(),
                                 ];
                             }
                             OrderProduct::insert($orderProducts);
                             // clear cart
                             CartProduct::where('cartId',$cart->id)->delete();
                             $cart->delete();

                         // add new notification that order created
                         Notification::send($user, new OrderCreated($order));
                         if($user->fcmToken){
                            $this->sendNotification($user->fcmToken,'App\Notifications\OrderCreated',app()->getLocale());
                         }
                         $agent=Agent::find($cart->agentId);

                         if($agent){

                         Notification::send($agent, new OrderCreated($order));
                         $this->sendNotification($agent->fcmToken,'App\Notifications\OrderCreatedAgent',$agent->language);
                         }
                            if($user->agent_id != $cart->agentId){
                                $user->agent_id = $cart->agentId;
                                $user->save();
                            }

                     }else{
                         return $this->newResponse(false,__('api.fails_response'));
                     }

        }catch (\Exception $e){
            \Log::info('Create new normal order exception mobile : '.$e->getMessage());
            return $this->newResponse(false,$e->getMessage()); //_('api.failed_place_order')
        }
        return $this->newResponse(true,__('api.order_has_been_sent_successfully'));
    }
    public function getCustomerOrder(Request $request){
        $user=auth()->user();
        $orders=[
            'current_orders'=>OrderResource::collection($user->orders()->where(function($q){
                $q->where('status','created')->orWhere('status','on_the_way');
            })->orderBy('created_at','desc')->get()),
            'completed_orders'=>OrderResource::collection($user->orders()->where('status','completed')->orderBy('created_at','desc')->get()),
            'canceled_orders'=>OrderResource::collection($user->orders()->where(function($q){
                $q->where('status','cancelledByClient')->orWhere('status','cancelledByApp');
            })->orderBy('created_at','desc')->get()),
        ];
        return $this->newResponse(true,__('api.success_response'),'',[],$orders);
    }
    public function getCouponDiscount($couponId,$amount)
    {
             $discount_value=0;
            $coupon = Coupon::where('status',1)->where('notBefore', '<',Carbon::today())->where('notAfter', '>',Carbon::today())->where('id',$couponId)->first();

            if($coupon){
                if($coupon->minAmount > $amount)
                {

                    return $this->newResponse(false,__('api.coupon_exceed_min_amount',['amount'=>$coupon->minAmount]));
                }else{
                    if($coupon->type == 'percentage')
                    {
                        $discount_value = ((float)$amount * (float)$coupon->value);
                    }else{
                        $discount_value=(float)$coupon->value;

                    }
                }
            }else{
                return -1;
            }
            return $discount_value;
    }

    public function getCancelReasons(Request $request){
        $reasons = RejectionReason::where('status',1)->get();
        $reasons=OrderRejectReasonsResource::collection($reasons);
        return $this->newResponse(true,__('api.success_response'),'order_cancel_reasons',$reasons);
    }
    public function cancelOrder(Request $request){
        $data = $request->only([
//            'reason_id',
            'order_id',

        ]);
        $rules = [
//            'reason_id' => 'required|numeric|exists:rejectionreasons,id',
            'order_id' => 'required|numeric|exists:orders,id',

        ];
        $validator = Validator::make($data, $rules);
        if ($validator->fails()) {
            return $this->newResponse(false,$this->validationHandle($validator->messages()));
        }

        try{
                $order=Order::find($request->order_id);
                if($order){
                    $order->status='cancelledByClient';
                    $order->cancel_reason_id=$request->reason_id;
                    $order->save();
                    return $this->newResponse(true,__('api.order_cancel_successfully'));
                }
        }catch (\Exception $e){
            \Log::error('cancelOrder error :',$e->getMessage(),'',[],$e->getCode());
            return $this->newResponse(false,__('api.fails_response'));
        }
        return $this->newResponse(false,__('api.fails_response'));
    }
//    protected function sendNotification($token,$type,$language){
//
//        $title = '';
//        $body = '';
//        if($language == 'ar'){
//            $title = $this->getArabicNotificationTitle($type);
//            $body = $this->getArabicNotificationDescription($type);
//        } else {
//            $title = $this->getEnglishNotificationTitle($type);
//            $body = $this->getEnglishNotificationDescription($type);
//        }
//
//        SendGoogleNotification::dispatch($token,$title,$body);
//    }


    public function offerOrders(Request $request){
        $data = $request->only([
            'offer_id',
            'address_id',
            'agent_id',
            'time_slot_id',
            'payment_type_id',
            'is_paid',
            'payment_transaction_id',
            'points',
            'use_points',
        ]);
        $rules = [
            'offer_id' => 'required|numeric|exists:offers,id',
            'address_id' => 'required|numeric|exists:address,id',
//            'agent_id' => 'required|numeric|exists:agents,id',
            'time_slot_id' => 'required|numeric|exists:time_slots,id',
            'payment_type_id' => 'required|numeric|exists:payment_types,id',
//            'delivery_date'=>'nullable|in:immediately,schedule',
            'is_paid' => 'nullable',
            'payment_transaction_id' => 'nullable',
        ];
        $validator = Validator::make($data, $rules);
        if ($validator->fails()) {
            return $this->newResponse(false,$this->validationHandle($validator->messages()));
        }
        try{
            $user=auth('customer')->user();
            $offer=Offer::find($request->offer_id);
            //  tax calculation
            $tax_ratio=Setting::valueOf('tax_ratio',0);
            $tax=(($tax_ratio/100)*$offer->price);
            //  end calculation
            $type="offer";
            $total_order_amount=round($offer->price+$tax,2);
            $order_data['userId']=$user->id;
            $order_data['assignDate'] = Carbon::now();;
            $order_data['addressId']=$request->address_id;
            $order_data['agentId']=$offer->agent_id;
            $order_data['time_slot_id']=$request->time_slot_id;
//            $order_data['schedule_slot_id']=$request->schedule_slot_id;
//            $order_data['flat_location_id']=$request->flat_location_id;
            $order_data['payment_type_id']=$request->payment_type_id;
            $order_data['type']=$type;
            $order_data['sub_total']=$offer->price;
            $order_data['total_discount']=0;
            $order_data['sub_total_2']=$total_order_amount;
            $order_data['tax_ratio']=$tax_ratio;
            $order_data['tax']=$tax;
            $order_data['amount']=$total_order_amount;
//            $order_data['delivery_cost']=0;
//            $order_data['use_points']=0;
//            $order_data['points']=$request->points;
//            $order_data['pointsDiscount']=$point_discount_value;
//            $order_data['couponDiscount']=$coupon_discount;
//            $order_data['delivery_schedule_date']=Carbon::parse($request->delivery_schedule_date)->format('Y-m-d');
            $order_data['is_paid']=$request->is_paid;
            $order_data['payment_transaction_id']=$request->payment_transaction_id;
            $order_data['paymentReference']=$request->payment_transaction_id;
            $order_data['offer_id']=$offer->id;
             $order=Order::create($order_data);
            if($order){
                // add new notification that order created
                Notification::send($user, new OrderCreated($order));
                if($user->fcmToken){
                    $this->sendNotification($user->fcmToken,'App\Notifications\OrderCreated',app()->getLocale());
                }
                $agent=Agent::find($request->agent_id);

                if($agent){

                    Notification::send($agent, new OrderCreated($order));
                    $this->sendNotification($agent->fcmToken,'App\Notifications\OrderCreatedAgent',$agent->language);
                }
                if($user->agent_id != $offer->agent_id){
                    $user->agent_id = $offer->agent_id;
                    $user->save();
                }

            }
//            else{
//                return $this->newResponse(false,__('api.fails_response'));
//            }

        }catch (\Exception $e){
            return $e->getMessage();
            \Log::info('Create new offers order exception mobile : '.$e->getMessage());
            return $this->newResponse(false,_('api.failed_place_order'));
        }
        return $this->newResponse(true,__('api.order_has_been_sent_successfully'));
    }

    public function cancelOrderByclient(Request $request)
    {
        $data = $request->only('order_id');
        $rules = [
            'order_id' => 'required|numeric|exists:orders,id',
        ];
        $validator = Validator::make($data, $rules);
        if ($validator->fails()) {
            return $this->newResponse(false,$this->validationHandle($validator->messages()));
        }


        try {

                $user=auth()->user();
                if(!$user)
                {
                    return $this->newResponse(false,__('api.not_exist_user'));
                }
                $order =    $user->orders()->where('status','created')->find($request->order_id);
                if(!$order)
                {
                    return $this->newResponse(false,__('api.not_exist_order'));
                }

                DB::beginTransaction();
                    $order->update([
                        'status'=>'cancelledByClient',
                        'cancelDate'=> Carbon::now(),

                    ]);


                // remove points if order is cancelled
                $user = User::find(auth()->user());
                $pointsRecord = Points::where('orderId',$request->order_id)->first();
                $user_points = $user->points + $pointsRecord->points;
                $user->update([
                    'points' => $user_points
                ]);
                $pointsRecord->delete();

                DB::commit();
                Notification::send($user, new OrderCancelled($order));
                if($user->fcmToken){

                $this->sendNotification($user->fcmToken,'App\Notifications\OrderCancelled',app()->getLocale());
                }
                return $this->newResponse(true,__('api.success_response'));

        } catch (Exception $e) {
            \Log::info('error cancel order ',$e->getMessage());
            DB::rollBack();
            return $this->newResponse(false,__('api.fails_response'));
        }




    }
    public function review(Request $request)
    {
        $data = $request->only('order_id','serviceReview','delegatorReview','reviewText','productsReview');
        $rules = [
            'order_id' => 'required|numeric',
            'serviceReview' => 'required|numeric',
            'delegatorReview' => 'required|numeric',
            'productsReview' => 'required|numeric',
            'reviewText' => 'required',
        ];
        $validator = Validator::make($data, $rules);
        if ($validator->fails()) {
            return $this->newResponse(false,$this->validationHandle($validator->messages()));
        }

        try {
                $user= $request->user('customer');

                $order=$user->orders()->where('id',$request->order_id)->where('status','completed')->first();
                if($order){
                    $order->reviewText = $request->reviewText;
                    $order->productsReview = $request->productsReview;
                    $order->delegatorReview = $request->delegatorReview;
                    $order->serviceReview = $request->serviceReview;
                    $order->save();
                    if($user->fcmToken){

                    $this->sendNotification($user->fcmToken,'App\Notifications\OrderReviewed',app()->getLocale());
                    }
                    return $this->newResponse(true,__('api.success_response'),'order',OrderResource::make($order));
                }else{
                    return $this->newResponse(false,__('api.fails_response'));
                }


                // send notification to user that we received the review



        } catch (\Exception $e) {
            return $this->newResponse(false,__('api.fails_response'));
        }

    }



}
