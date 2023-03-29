<?php

namespace App\Http\Controllers\Api\Customer;

use App\Http\Controllers\Controller;
use App\Http\Services\HeyperPayPaymentController;
use App\Models\Cart;
use App\Models\CartProduct;
use App\Models\DeliveryFlatLocation;
use App\Models\Offer;
use App\Models\Order;
use App\Models\OrderProduct;
use App\Models\Points;
use App\Models\Setting;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;
use Illuminate\Validation\Rules\RequiredIf;
use Illuminate\Support\Facades\Auth;
use App\Models\Agent;

class HyperPayPaymentController extends Controller
{
    protected $payment;

    public function __construct(HeyperPayPaymentController $payment)
    {
        $this->payment = $payment;
    }

    public function getPaymentCheckoutId(Request $request)
    {
        $data = $request->only([
            'cart_id',
            'coupon_id',
            'flat_location_id',
            'payment_type_id',
            'points',
            'use_points',
        ]);
        $rules = [
            'cart_id' => 'required|numeric|exists:cart,id',
            'coupon_id' => 'nullable|numeric|exists:coupons,id',
            'flat_location_id' => 'required|numeric|exists:delivery_flat_locations,id',
            'payment_type_id' => 'required|numeric|exists:payment_types,id|in:2,3,4',
            'use_points' => 'required',
            'points' => new RequiredIf($request->use_points == true || $request->use_points == 1),
        ];
        $validator = Validator::make($data, $rules);
        if ($validator->fails()) {
            return $this->newResponse(false, $this->validationHandle($validator->messages()));
        }
        try {
            $user = $request->user('customer');
            $cart = Cart::find($request->cart_id);
            $delivery_cost = 0;
            $coupon_discount = 0;
            $point_discount_value = 0;
            $use_customer_points = false;
            // coupon discount
            if ($request->coupon_id) {
                $coupon_discount = $this->getCouponDiscount($request->coupon_id, $cart->total);
                if ($coupon_discount == -1) {
                    return $this->newResponse(false, __('api.not_valid_coupon'));
                }
            }
            // end coupon discount

            // delivery cost
            $delivery_flat_location_model = DeliveryFlatLocation::find($request->flat_location_id);
            if ($delivery_flat_location_model) {
                $delivery_cost_per_carton = $delivery_flat_location_model->delivery_cost;
                $delivery_cost = floatval(($cart->total_qty * $delivery_cost_per_carton));
            }
            // end delivery cost


            $amount_after_coupon_discount = floatval(($cart->total - $coupon_discount));

            //  tax calculation
            $tax_ratio = Setting::valueOf('tax_ratio', 0);
            $tax = (($tax_ratio / 100) * $amount_after_coupon_discount);
            //  end calculation

            $net_total_amount_before_points = $amount_after_coupon_discount + $tax + $delivery_cost;

            // start point discount
            if ($request->use_points) {
                if ($user->points >= $request->points) {
                    $request->points = $this->convertEnglishNumber($request->points);
                    $min_points_to_replace = Setting::valueOf('min_points_to_replace');
                    if ($request->points < $min_points_to_replace) {
                        return $this->newResponse(false, __('api.system_min_point_to_use', ['points_num' => $min_points_to_replace]));
                    } else {
                        $points_per_1_sar = Setting::valueOf('replace_points');
                        $points_money = ($request->points / $points_per_1_sar);
                        if ($points_money <= $net_total_amount_before_points) {
                            $point_discount_value = $points_money;
                            $use_customer_points = true;
                        } else {
                            return $this->newResponse(false, __('api.use_less_points_in_order'));
                        }
                    }

                } else {
                    return $this->newResponse(false, __('api.not_enough_points'));
                }
            }
            // end point discount
            $total_discount = $coupon_discount + $point_discount_value;
            $total_order_amount = number_format(($net_total_amount_before_points - $point_discount_value),2, '.', '');

            switch($request->payment_type_id){
                case 2:
                    $payment_type = 'visa';
                break;
                case 3:
                    $payment_type = 'mada';
                break;
                default:
                    $payment_type = 'apple';
                break;

            }
            // if ($request->payment_type_id == 2) {

            // }

            // if ($request->payment_type_id == 3) {

            // }

            // if ($request->payment_type_id == 4) {
            //     $payment_type = 'apple';
            // }
            // dd($request->cart_id);
            return $this->payment->getCheckOutId($total_order_amount, $payment_type, 'test', NULL, $request->cart_id);

        } catch (\Exception $e) {
            \Log::info('Create new normal order exception mobile : ' . $e->getMessage() . " payment checkout data" . json_encode($request->all()));
            return $this->newResponse(false, $e->getMessage()); //__('api.fails_response')
        }

    }

    public function paymentVisaStatus(Request $request)
    {
        //dd($request->all());

        //$user = $request->user('customer');
        //dd(auth('sanctum')->user()->id);
        //dd(auth('api')->user());
        //$agent = new Agent;
        //dd($agent->id);
        return $this->payment->getCheckoutStatus($request->get('resourcePath'), 'visa', 'test');
    }

    public function paymentMadaStatus(Request $request)
    {
        return $this->payment->getCheckoutStatus($request->get('resourcePath'), 'mada', 'test');
    }

    public function paymentAppleStatus(Request $request)
    {
        return $this->payment->getCheckoutStatus($request->get('resourcePath'), 'apple', 'test');
    }


    public function getOfferPaymentCheckout(Request $request)
    {
        $data = $request->only([
            'offer_id',
            'payment_type_id',

        ]);
        $rules = [
            'offer_id' => 'required|numeric|exists:offers,id,deleted_at,NULL',
            'payment_type_id' => 'required|numeric|exists:payment_types,id|in:2,3,4',
        ];
        $validator = Validator::make($data, $rules);
        if ($validator->fails()) {
            return $this->newResponse(false, $this->validationHandle($validator->messages()));
        }
        try {
            $offer = Offer::find($request->offer_id);
            //  tax calculation
            if ($offer) {
                $tax_ratio = Setting::valueOf('tax_ratio', 0);
                $tax = (($tax_ratio / 100) * $offer->price);
                $total_order_amount = number_format(($tax + $offer->price), 2);
                if ($request->payment_type_id == 2) {
                    return $this->payment->getCheckOutId($total_order_amount, 'visa', 'test');
                }

                if ($request->payment_type_id == 3) {
                    return $this->payment->getCheckOutId($total_order_amount, 'mada', 'test');
                }

                if ($request->payment_type_id == 4) {
                    return $this->payment->getCheckOutId($total_order_amount, 'apple', 'test');
                }
            }
        } catch (\Exception $e) {
            \Log::info('Create new normal order exception mobile : ' . $e->getMessage() . " payment checkout data" . json_encode($request->all()));
            return $this->newResponse(false, __('api.fails_response')); // 
        }
        return $this->newResponse(false, __('api.fails_response'));
    }

}
