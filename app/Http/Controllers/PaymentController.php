<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
namespace App\Http\Controllers;
use Illuminate\Support\Facades\Validator;
use App\Models\Setting;
use App\Models\Checkout;
use App\Models\PaymentNotification;

use Illuminate\Support\Str;
use App\Models\Address;
use App\Models\Cart;
use App\Models\CartProduct;
use App\Models\OrderProduct;
use App\Models\AgentProduct;
use App\Models\Order;
use App\Models\User;
use App\Models\Agent;
use App\Models\FavoriteProduct;
use App\Models\Delegator;
use App\Models\Coupon;
use Carbon\Carbon;


class PaymentController extends Controller
{
    //checkout
    public function checkout(Request $request)
    {
        try {
            // $data = $request->only(['userId','amount']);
            // $rules = [
            //     'userId' => 'required',
            //     'amount' => 'required|numeric',
            // ];

            $data = $request->only(['userId', 'cartId','addressId','amount','coupon','points']);
            $rules = [
                'userId' => 'required|numeric',
                'cartId' => 'required|numeric',
                'addressId' => 'required|numeric',
                'amount' => 'required|numeric',
                // 'points' => 'numeric',
                // 'coupon' => 'required',
            ];

            $validator = Validator::make($data, $rules);
            if ($validator->fails()) {
                return $this->response(false,$this->validationHandle($validator->messages()));
            } else {

                $sameAgent= true;
                // check user id
                $user = User::find($data['userId']);
                if($user == null){
                    return $this->response(false,'not valid user id');
                }

                if($user->language == 'en'){
                    return $this->response(false,'Online payment is not enalbed for now');
                } else {
                    return $this->response(false,' عذراً خاصية الدفع الالكتروني غير مفعلة الان' );
                }


                if($request->points != null && $request->points > 0 ){
                    $request->points = $this->convert($request->points);
                    $min_points_to_replace = Setting::where('name','min_points_to_replace')->first()->value;
                    if($request->points < $min_points_to_replace){
                        if($user->language == 'en'){
                            return $this->response(false,'you can replace '.$min_points_to_replace.' points atleast');
                        } else {
                            return $this->response(false,' لا تستطيع استبدل نقاط أقل من ' .$min_points_to_replace );
                        }
                    }

                }

                // check address id
                $address = Address::where('id',$data['addressId'])->where('userId',$data['userId'])->first();
                // return $address;
                if($address == null){
                    return $this->response(false,'not valid address');
                }

                // check if address is supported or no
                $locationAgent = new Agent;
                $locationAgent = $locationAgent->search($address->lat,$address->lng);
                if($locationAgent == null)
                {
                    if($user->langauge == 'en')
                    {

                        return $this->response(false,'not supported address');
                    } else {
                        return $this->response(false,'الموقع غير مدعوم، الرجاء التواصل مع الرقم الموحد');
                    }
                }


                $cartId = $data['cartId'];
                $cart =  Cart::find($cartId);
                // return $cart;
                if($cart == null)
                {
                    return $this->response(false,'not valid cart id');
                } else {
                    if($cart->addressType != $address->type){
                        // remove cart with addressType = address->type
                        Cart::where('userId',$request->userId)->where('addressType',$address->type)->delete();
                        // replace address type in cart
                        $result = Cart::where('userId',$request->userId)->where('addressType',$cart->addressType)
                        ->update(['addressType' => $address->type]);
                        $cart->addressType = $address->type;
                    //    return $cart;
                       // calculate amount and update it in cart
                    }
                    $agent = Agent::find($cart->agentId);
                    if($agent == null){
                        return $this->response(false,'not valid agent id');
                    } else {
                        // check if cart agent is same of address agent
                        if($agent->id != $locationAgent->id){
                            $cart->agentId = $locationAgent->id;
                            $cart->save();
                            $sameAgent= false;
                        }
                    }

                    $agentProducts = AgentProduct::where('agentId',$cart->agentId)->get();
                    // return $agentProducts;
                    $products = CartProduct::where('cartId',$cartId)->get();
                    if($products == null){
                        return $this->response(false,'not valid cart id');
                    } else {
                        $couponDiscount = 0;
                        // check if Coupon is valid
                        if($request->coupon != null){
                            $coupon = Coupon::where('code',$data['coupon'])
                            ->where('status',1)
                            ->where('notBefore','<=',Carbon::now())->where('notAfter','>=',Carbon::now())->first();
                            if($coupon != null)
                            {

                                if($coupon->minAmount > $request->amount){
                                    if($user->language == 'ar')
                                    {
                                        return $this->response(false,' لاستخدام كود الخصم، يجب أن تكون تكلفة الطلب أكثر من  '.$coupon->minAmount );
                                    }
                                    return $this->response(false,'minimum amount to use this coupon code should be '.$coupon->minAmount );
                                }
                                if($coupon->type == 'flat'){
                                    $couponDiscount = $coupon->value;
                                }
                                else{
                                    if($coupon->value <=1) // value  = percentage
                                        $couponDiscount = $data['amount'] * $coupon->value;
                                }
                            }
                            else{
                                if($user->language == 'ar')
                                    return $this->response(false,'كود الخصم غير صحيح');

                                return $this->response(false,'not valid coupon');
                            }
                        }
                        // check points
                        if($request->points!= null && $request->points > 0){
                            // check user point
                            if($user->points < $request->points){
                                if($user->language == 'ar')
                                {
                                    return $this->response(false,' عفوا لا تستطيع استبدال اقل من ' .$min_points_to_replace. ' نقطة ' );
                                }
                                return $this->response(false,"You don't have enough points");
                            }
                            $replace_points	 = Setting::where('name','replace_points')->first()->value;
                            $couponDiscount = $couponDiscount + ($request->points/$replace_points);
                        }
                    }

                    }


                $entityId =  Setting::withTrashed()->where('name','hyperpay_entity_id')->first()->value;
                $token =  Setting::withTrashed()->where('name','hyperpay_access_token')->first()->value;
                $currency =  Setting::withTrashed()->where('name','hyperpay_currency')->first()->value;
                $paymentType =  Setting::withTrashed()->where('name','hyperpay_payment_type')->first()->value;
                $url =  Setting::withTrashed()->where('name','hyperpay_url_checkout_test')->first()->value;
                $uuid = (string) Str::uuid();
                $responseData = $this->request($url,$token,$entityId,$request->amount,$currency,$paymentType,$uuid);
                $jsonData = json_decode($responseData, true);
                Checkout::create([
                    'userId' => $request->userId,
                    'amount' => $request->amount -$couponDiscount,
                    'hyperpayResponse' => $responseData,
                    'uuid' => $uuid,
                ]);
                $jsonData['uuid'] = $uuid;
            return $this->response(true,'success',$jsonData);
            }
        } catch (Exception $e) {
            return $this->response(false,'system error');
        }

    }

    public function getStatus(Request $request)
    {
        try {
            $data = $request->only(['id']);
            $rules = [
                'id' => 'required',
            ];

            $validator = Validator::make($data, $rules);
            if ($validator->fails()) {
                return $this->response(false,$this->validationHandle($validator->messages()));
            } else {
                $entityId =  Setting::withTrashed()->where('name','hyperpay_entity_id')->first()->value;
                $token =  Setting::withTrashed()->where('name','hyperpay_access_token')->first()->value;
                $url =  Setting::withTrashed()->where('name','hyperpay_url_checkout_test')->first()->value;

                $responseData = $this->getStatusFromHyperpay($url,$entityId,$request->id,$token);
                $jsonData = json_decode($responseData, true);
            return $this->response(true,'success',$jsonData);
            }
        } catch (Exception $e) {
            return $this->response(false,'system error');
        }

    }

    //list all
    public function list(Request $request)
    {
        try {
            return $this->response(true,'success',Checkout::all());
        } catch (Exception $e) {
            return $this->response(false,'system error');
        }
    }

    //update status
    public function notify(Request $request)
    {
        PaymentNotification::create(['hyperpayRequest'=>$request->id]);
        return $this->response(true,'success');
    }

    function request($url, $token, $entityId, $amount, $currency, $paymentType,$uuid) {
        $data = "entityId=".$entityId .
                    "&amount=".$amount .
                    "&merchantTransactionId=".$uuid.
                    "&currency=".$currency.
                    "&testMode=EXTERNAL".
                    "&paymentType=".$paymentType.
                    "&notificationUrl=".url('/')."/checkout/notify";
        $ch = curl_init();
        curl_setopt($ch, CURLOPT_URL, $url);
        curl_setopt($ch, CURLOPT_HTTPHEADER, array(
                            'Accept: application/json',
                       'Authorization:Bearer '.$token));
        curl_setopt($ch, CURLOPT_POST, 1);
        curl_setopt($ch, CURLOPT_POSTFIELDS, $data);
        curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);// this should be set to true in production
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        $responseData = curl_exec($ch);
        if(curl_errno($ch)) {
            return curl_error($ch);
        }
        curl_close($ch);
        return $responseData;
    }

    function getStatusFromHyperpay($url,$entityId,$id,$token) {
        $url = $url."/".$id."/payment";
        $url .= "?entityId=".$entityId;

        $ch = curl_init();
        curl_setopt($ch, CURLOPT_URL, $url);
        curl_setopt($ch, CURLOPT_HTTPHEADER, array(
                       'Authorization:Bearer '.$token));
        curl_setopt($ch, CURLOPT_CUSTOMREQUEST, 'GET');
        curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);// this should be set to true in production
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        $responseData = curl_exec($ch);
        if(curl_errno($ch)) {
            return curl_error($ch);
        }
        curl_close($ch);
        return $responseData;
    }

}
