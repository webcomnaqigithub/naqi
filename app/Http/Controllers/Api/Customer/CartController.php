<?php

namespace App\Http\Controllers\Api\Customer;

use App\Http\Controllers\Controller;
use App\Http\Resources\AgentResource;
use App\Http\Resources\CartResource;
use App\Models\Agent;
use App\Models\AgentProduct;
use App\Models\Cart;
use App\Models\CartProduct;
use App\Models\Coupon;
use App\Models\DeliveryFlatLocation;
use App\Models\FavoriteProduct;
use App\Models\Product;
use App\Models\Setting;
use App\Models\User;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;

class CartController extends Controller
{
    public function add(Request $request)
    {
        $data = $request->only(['agent_id','address_id','product_id','amount']);
        $rules = [
            'address_id' => 'required|numeric|exists:address,id,deleted_at,NULL',
            'agent_id' => 'required|numeric||exists:agents,id,deleted_at,NULL',
//                'addressType' => 'required|in:mosque,home,company',
            'product_id' => 'required|exists:products,id,deleted_at,NULL',
            'amount' => 'required|integer|min:1',
        ];
        $validator = Validator::make($data, $rules);
        if ($validator->fails()) {
            return $this->newResponse(false,$this->validationHandle($validator->messages()));
        }

        try {
                $user =$request->user();
                if($user){
                      $address=  $user->addresses()->find($request->address_id);
                      if($address){
                          $agent = Agent::find($request->agent_id);
                          $agentProductCheck= $agent->products()->where('productId',$request->product_id)->exists();
                        $cart=  $this->addProductToCart($user->id,$agent->id,$address,$request->amount,$request->product_id);
//                          if($agentProductCheck){

//                                    dump($cart);
                              if($cart){
//                                  $resposeCart=CartResource::make($cart);
                                  return $this->newResponse(true,__('api.success_response'));
                              }else{
                                  return $this->newResponse(false,__('api.failed_to_add_product'));
                              }
//                          }else{
//                              return $this->newResponse(false, __('api.not_exist_product'));
//                          }

                      }else{
                          return $this->newResponse(false, __('api.not_exist_address'));
                      }
                }else{
                  return $this->newResponse(false,__('api.fails_response'));
                }
        } catch (\Exception $e) {
            \Log::info($e->getMessage());
            return $this->newResponse(false,__('api.fails_response'));
        }

    }
    public function remove(Request $request)
    {
        $data = $request->only(['cart_product_id','cart_id']);
        $rules = [
            'cart_product_id' => 'required|numeric|exists:cart_products,id',
            'cart_id' => 'required|numeric|exists:cart,id',
        ];
        $validator = Validator::make($data, $rules);
        if ($validator->fails()) {
            return $this->newResponse(false,$this->validationHandle($validator->messages()));
        }

        try {


                       $cart_product= CartProduct::destroy([$request->cart_product_id]);
                        if($cart_product){
                            $cart=Cart::find($request->cart_id);
                            $cartUpdate=CartResource::make($cart);

                            return $this->newResponse(true,__('api.success_response'),'cart',$cartUpdate);

                        }

                       return $this->newResponse(false,__('api.fails_response'));

        } catch (\Exception $e) {
            \Log::info($e->getMessage());
            return $this->newResponse(false,__('api.fails_response'));
        }

    }
    public function getCustomerCart(Request $request){
        $data = $request->only(['agent_id','address_id','product_id','amount']);
        $rules = [
            'address_id' => 'required|numeric|exists:address,id,deleted_at,NULL',
            'agent_id' => 'required|numeric||exists:agents,id,deleted_at,NULL',
        ];
        $validator = Validator::make($data, $rules);
        if ($validator->fails()) {
            return $this->newResponse(false,$this->validationHandle($validator->messages()));
        }
        try{
            $user=auth()->user();
            if($user){
//                $cart = Cart::where('userId',$user->id)->where('agentId',$request->agent_id)
//                    ->where('address_id',$request->address_id)->first();
                $cart = Cart::where('userId',$user->id)->first();
                if($cart){
                    $resposeCart=CartResource::make($cart);
                    return $this->newResponse(true,__('api.success_response'),'cart',$resposeCart);

                }else{
                    return $this->newResponse(true,__('api.customer_not_have_cart'));
                }

            }
            return $this->newResponse(false,__('api.fails_response'));

        }catch(\Exception $e){
            return $this->newResponse(false,$e->getMessage());
        }

    }

    public function getSettings(){
        $tax=Setting::valueOf('tax',15);
        return $tax;
    }


    public function getFlatLocationPrice(Request $request)
    {
        $data = $request->only(['agent_id', 'flat_id']);
        $rules = [
            'agent_id' => 'required',
            'flat_id' => 'required',
        ];
        $validator = Validator::make($data, $rules);
        if ($validator->fails()) {
            return $this->newResponse(false, $this->validationHandle($validator->messages()));
        }

        try{
            $flatLocationPrice=DeliveryFlatLocation::where('id',$request->flat_id)->whereHas('agents',function($q) use ($request){
                $q->where('agent_id',$request->agent_id);
            })->first();
            if($flatLocationPrice){
                return $this->newResponse(true,__('api.success_response'),'',[

                ],[
                    'flat_location_id'=>$flatLocationPrice->id,
                    'delivery_cost'=>$flatLocationPrice->delivery_cost,
                ]);
            }
            $flatLocationPricee=DeliveryFlatLocation::find($request->flat_id);
            if($flatLocationPricee){
                return $this->newResponse(true,__('api.success_response'),'',[],[
                    'flat_location_id'=>$flatLocationPricee->id,
                    'delivery_cost'=>$flatLocationPricee->default_cost,
                ]);
            }

            return $this->newResponse(true,__('api.success_response'),'',[],[
                'flat_location_id'=>0,
                'delivery_cost'=>0,
            ]);
        }catch (\Exception $e){
            return $e->getMessage();
        }
    }

    public function getFlatPriceByAgent(Request $request)
    {
        $data = $request->only(['agent_id']);
        $rules = [
            'agent_id' => 'required',
        ];
        $validator = Validator::make($data, $rules);
        if ($validator->fails()) {
            return $this->newResponse(false, $this->validationHandle($validator->messages()));
        }

        try{
            $agent = Agent::where('id', $request->agent_id)->with(['DeliveryFlatLocation'=>function($e){
                $e->where('is_active', 1);
            }])->first();
            $agent_locatoin = new AgentResource($agent);
            return $this->response(true, 'success', $agent_locatoin);

        }catch (\Exception $e){
            return $e->getMessage();
        }
    }
}
