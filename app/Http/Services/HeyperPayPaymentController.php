<?php

namespace App\Http\Services;

use GuzzleHttp\Client;
use GuzzleHttp\Psr7\Request;
use App\Models\Cart;
use App\Models\CartProduct;
use App\Models\Customer;
use Illuminate\Support\Facades\Auth;

class HeyperPayPaymentController
{
    protected $demoUrl;
    protected $demoToken;
    protected $demoVisaEntityId;
    protected $demoMadaEntityId;
    protected $demoAppleEntityId;
    protected $liveUrl;
    protected $liveToken;
    protected $liveEntityId;
    protected $demoBaseUrl;
    protected $liveBaseUrl;
    public function __construct()
    {
        $this->demoBaseUrl=env('heyperpay_test_base_url')??'https://test.oppwa.com/';
        $this->liveBaseUrl=env('heyperpay_live_base_url')??'https://test.oppwa.com/';
        $this->demoUrl=env('heyperpay_test_url')??'https://test.oppwa.com/v1/checkouts';
        $this->liveUrl=env('heyperpay_live_url')??'https://test.oppwa.com/v1/checkouts';
        $this->demoToken=env('heyperpay_test_token')??'OGFjN2E0Yzg3MGQzNmQ3NjAxNzBlMjNlMmViNzA2MjB8czh6UHpCMmJCOQ==';
        $this->liveToken=env('heyperpay_live_token')??'';
        $this->demoVisaEntityId=env('heyperpay_test_visa_entity_id')??'8ac7a4c870d36d760170e23e76900625';
        $this->demoMadaEntityId=env('heyperpay_test_mada_entity_id')??'8ac7a4ca7e573862017e626f8f190725';
        $this->demoAppleEntityId=env('heyperpay_test_apple_entity_id')??'8ac7a4c9829f72f60182a0be7270013d';
        $this->liveEntityId=env('heyperpay_live_entity_id')??'';

    }



    function getCheckOutId($amount,$paymentType,$target,$user=null, $cart_id = NULL)
    {
        $params = 
            "&amount=" . str_replace( ',', '', $amount ) .
            "&currency=SAR" .
            "&paymentType=DB" .
            "&merchantTransactionId=".uniqid() .
           // "&billing.street1=" . $this->getUserDetails($cart_id)->city->englishName .
           // "&billing.city=" . $this->getUserDetails($cart_id)->city->englishName .
           // "&billing.state=" . $this->getUserDetails($cart_id)->city->englishName .
            "&billing.country=SA".
            "&billing.postcode=123456".
            "&customer.email=test@test.com" .
            "&customer.givenName=" . $this->getUserDetails($cart_id)->name .
            "&customer.surname=" . $this->getUserDetails($cart_id)->name ;

        if ($paymentType =='visa' && $target=='test'){
            $data = 
                "entityId=".$this->demoVisaEntityId . $params;
                // "&shopperResultUrl=".urlencode('https://178.128.30.65/api/customer/payment/visa-status');
                return $this->getTestCheckout($data, $cart_id);
        }
        if ($paymentType =='mada' && $target=='test'){
            $data = "entityId=".$this->demoMadaEntityId . $params;

                //"&shopperResultUrl=".url('api/customer/payment/mada-status')

                return $this->getTestCheckout($data, $cart_id);
        }
        if ($paymentType =='apple' && $target=='test'){
            $data = "entityId=".$this->demoAppleEntityId. $params;

                return $this->getTestCheckout($data, $cart_id);
        }
        return false;
    }
    protected function getTestCheckout($data, $cart_id){
        $url=$this->demoUrl;
        $token=$this->demoToken;

        try {
            $ch = curl_init();
            curl_setopt($ch, CURLOPT_URL, $url);
            curl_setopt($ch, CURLOPT_HTTPHEADER, array(
                'Authorization:Bearer '.$token));
            curl_setopt($ch, CURLOPT_POST, 1);
            curl_setopt($ch, CURLOPT_POSTFIELDS, $data);
            curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);// this should be set to true in production
            curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
            $responseData = curl_exec($ch);
            if (curl_errno($ch)) {
                return curl_error($ch);
            }
            curl_close($ch);
            $response = json_decode($responseData, true);
            //dd($response);
            if($response['result']['code'] == '000.200.100')
            {
                $checkout_id = $response['id'];
                Cart::where('id',$cart_id)->update([
                    'checkout_id'=>$checkout_id
                ]);
            }
            return json_decode($responseData, true);
        } catch (\Exception $e) {
            return $e->getMessage();
        }
    }
    function getCheckoutStatus($resourcePath,$paymentType,$target,$user=null)
    {
        $resource_path_array = explode('/', $resourcePath);
        if(is_array($resource_path_array)){
            $checkout_id = $resource_path_array[2]??null;
        }
        if ($paymentType =='visa' && $target=='test'){

            $url = $this->demoBaseUrl.'/' . $resourcePath;
            $url .= "?entityId=".$this->demoVisaEntityId;
            $responseData = $this->heyperPayPaymentStatus($url,$this->demoToken);
            // return($responseData);
            // if($responseData['result']['code'] != ''){
            //     $cart = Cart::where('checkout_id','LIKE', $checkout_id)->first();
            //     CartProduct::where('cartId', $cart->id)->delete();
            //     $cart->delete();
            // }
            return $responseData;
        }

        if ($paymentType =='mada' && $target=='test'){
            $madaurl = $this->demoBaseUrl.'/' . $resourcePath;
            $madaurl .= "?entityId=".$this->demoMadaEntityId;
            return $this->heyperPayPaymentStatus($madaurl,$this->demoToken);
        }
        if ($paymentType =='apple' && $target=='test'){
            $appleurl = $this->demoBaseUrl.'/' . $resourcePath;
            $appleurl .= "?entityId=".$this->demoAppleEntityId;
            return $this->heyperPayPaymentStatus($appleurl,$this->demoToken);
        }
        return false;
    }

    protected function heyperPayPaymentStatus($resourcePath,$token)
    {
        $ch = curl_init();
        curl_setopt($ch, CURLOPT_URL, $resourcePath);
        curl_setopt($ch, CURLOPT_HTTPHEADER, array(
            'Authorization:Bearer '.$token));
        curl_setopt($ch, CURLOPT_CUSTOMREQUEST, 'GET');
        curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);// this should be set to true in production
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        $responseData = curl_exec($ch);
        if (curl_errno($ch)) {
            return curl_error($ch);
        }
        curl_close($ch);
        return json_decode($responseData, true);
    }

    public function getUserDetails($cart_id)
    {
        $cart = Cart::find($cart_id);
        $customer = Customer::where('id', auth()->user()->id)->with('city')->first();
        return $customer;
    }
}
