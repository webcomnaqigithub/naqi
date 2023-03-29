<?php

namespace App\Http\Controllers;

use App\Models\CartProduct;
use App\Models\AgentProduct;
use App\Models\Cart;
use App\Models\Product;
use App\Models\User;
use App\Models\Agent;
use App\Models\Setting;
use App\Models\FavoriteProduct;
use Illuminate\Support\Facades\Validator;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Log;
class CartProductController extends Controller
{

    private function getCart($userId,$addressType )
    {
        $cart  = Cart::where('userId',$userId)->where('addressType',$addressType)->first();
                if($cart == null)
                {
                    return null;
                }
                $productsOfCart =CartProduct::select('cart_products.id','cart_products.productId','cart_products.cartId','products.arabicName','products.englishName','products.picture',
                'agentProducts.mosquePrice','agentProducts.otherPrice','agentProducts.homePrice','agentProducts.officialPrice','cart_products.amount')
                ->leftJoin('products', 'products.id', '=', 'cart_products.productId')
                ->join('agentProducts', 'agentProducts.productId', '=', 'products.id')
                ->where('cartId',$cart->id)
                ->where('agentProducts.agentId',$cart->agentId)
                ->get();
                // add favorite flag
                $favoriteProducts =FavoriteProduct::where('userId',$cart->userId)->get();//->pluck('productId');
                $totalPrice = 0;
                $totalItems = 0;
                foreach ($productsOfCart as $product) {
                    if($favoriteProducts->contains('productId', $product->productId)) {
                        $product->isFavorite = 1;
                    }else{
                        $product->isFavorite = 0;
                    }
                    $product->picture = url('/').$product->picture;
                    $price= $product->mosquePrice;//mosque,home,company
                    if($cart->addressType == 'home' )
                    {
                        $price= $product->homePrice;
                    }
                    if($cart->addressType == 'company' )
                    {
                        $price= $product->officialPrice;
                    }
                    $totalPrice = $totalPrice+ ($product->amount * $price);
                    $totalItems = $totalItems+ $product->amount;
                }
                $cart->products= $productsOfCart;
                $cart->productCount= $totalItems;
                $cart->totalPrice= $totalPrice;

                // get the minimum of quantity and minimum of cost
                $agent = Agent::find($cart->agentId);
                //  Log::info($agent->minimum_cartons);
                $minimumAmount = Setting::where('name','minimum_amount')->first();
                $minimumCost = Setting::where('name','minimum_cost')->first();

                $cart->minimumAmount=$agent->minimum_cartons;

                if($minimumCost != null)
                {
                    $cart->minimumCost=$minimumCost->value;
                } else {
                    $cart->minimumCost = 0;
                }
                // if($minimumAmount != null)
                // {
                //     $cart->minimumAmount=$agent->minimum_cartons;
                // } else {
                //     $cart->minimumAmount = 0;
                // }
                return $cart;
    }
    //list all products of cart
    public function listCartProduct(Request $request)
    {
        try {
            $data = $request->only(['addressType','userId']);
            $rules = [
                // 'cartId' => 'required',
                'userId' => 'required',
                'addressType' => 'required|in:home,mosque,company',
            ];
            $validator = Validator::make($data, $rules);
            if ($validator->fails()) {
                return $this->response(false,$this->validationHandle($validator->messages()));
            } else {
                $cart = $this->getCart($request->userId,$request->addressType);
                if($cart ==null)
                {
                    return $this->response(false,'empty cart');
                }
                return $this->response(true,'success',$cart);
            }
        } catch (Exception $e) {
            return $this->response(false,'system error');
        }
    }

    //delete
    public function clear($cartId)
    { 
        try {
            // $user = Auth::user();
            $cart  = Cart::where('id',$cartId)->first();
            if($cart == null)
            {
                return $this->response(false,'invalid cart id');
            }

            $record = CartProduct::where('cartId',$cartId)->delete();
            if($record > 0)
            {
                return $this->response(true,'success');
            }else {
                $products = [];
                return $this->response(true,'no record to delete',$products);
            }
        } catch (Exception $e) {
            return $this->response(false,'system error');
        }
        
    }

    //delete multiple
    public function deleteMultiple(Request $request)
    {   
        try {
            $data = $request->only(['cartId']);
            $rules = [
                'cartId' => 'required',
            ];
            $validator = Validator::make($data, $rules);
        if ($validator->fails()) {
            return $this->response(false,$this->validationHandle($validator->messages()));
        } else {
            $cart  = Cart::where('id',$request->cartId)->first();
            if($cart == null)
            {
                return $this->response(false,'invalid cart id');
            }
            $record = CartProduct::whereIn('productId', $request->ids)->where('cartId', $request->cartId)->delete();
            if($record == 0){
                return $this->response(true,'no record to remvoe');
            } else {
                $cart = $this->getCart($cart->userId,$cart->addressType);
                if($cart ==null)
                {
                    return $this->response(false,'empty cart');
                }
                return $this->response(true,'success',$cart);
            }
        }
        } catch (Exception $e) {
            return $this->response(false,'system error');
        }
    }

    

    //create
    public function update(Request $request)
    {
        try {
            $data = $request->only(['userId','agentId','addressType','productId','amount']);
            $rules = [
                'userId' => 'required',
                'agentId' => 'required|numeric',
                'addressType' => 'required|in:mosque,home,company',
                'productId' => 'required|numeric',
                'amount' => 'required|integer|min:1',
            ];
            $validator = Validator::make($data, $rules);
            if ($validator->fails()) {
                return $this->response(false,$this->validationHandle($validator->messages()));
            } else {
                $user = User::find($request->userId);
                // if($user == null){
                //     return $this->response(false,'user is not found');
                // }
                $product = Product::find($request->productId);
                if($product == null){
                    return $this->response(false,'product is not available now');
                }
                $agentProduct = AgentProduct::where('productId',$request->productId)
                ->where('agentId',$request->agentId)
                ->where('status',1)->first();
                if($agentProduct == null){
                    if($user->language == 'ar')
                    {
                        return $this->response(false,'المنتج غير متوفر حالياً');
                    } else{
                        return $this->response(false,'product is not available now');
                    }
                    
                }
                $cart=  parent::addToCart($request->userId,$request->agentId,$request->addressType,$data['amount'],$data['productId']);
                if($cart == null){
                    return $this->response(false,'failed to add product');
                }
                $cart = $this->getCart($cart->userId,$cart->addressType);
                if($cart == null)
                {
                    return $this->response(false,'empty cart');
                }
                return $this->response(true,'success',$cart);
            }
        } catch (Exception $e) {
            return $this->response(false,'system error');
        }
        
    }


    public function addFavoriteToCart(Request $request)
    {
        try {
            $data = $request->only(['userId','agentId','addressType']);
            $rules = [
                'userId' => 'required',
                'addressType' => 'required',
                'agentId' => 'required',
            ];
            $validator = Validator::make($data, $rules);
            if ($validator->fails()) {
                return $this->response(false,$this->validationHandle($validator->messages()));
            } else {
                $user = Auth::user();
                if($user->id != $request->userId)
                {
                    return $this->response(false,'invalid user id');
                }
                


                $ids = FavoriteProduct::where('userId',$request->userId)->pluck('productId');
                $agentProducts = AgentProduct::where('agentId',$request->agentId)->where('status',1)
                ->whereIn('productId',$ids)
                ->pluck('productId');
                if(count($agentProducts) != count($ids))
                {
                    // some of products are not supported in new agent
                    if($user->language == 'en')
                    {
                        return $this->response(false,'some of products are not available now');
                    }else{
                        return $this->response(false,'بعض المنتجات غير متوفرة حالياً');
                    }             
                }

                
                $cart=  new Cart;
                $favoriteProducts = FavoriteProduct::select('*')
                ->leftJoin('agentProducts','agentProducts.productId', '=', 'favoriteProducts.productId')
                ->where('userId',$request->userId)
                ->where('agentProducts.agentId',$request->agentId)
                ->get();//->pluck('productId');
                if($favoriteProducts == null || count($favoriteProducts) == 0)
                {
                    return $this->response(false,'no products in cart');
                }

                

                // remove other carts
                Cart::where('userId',$request->userId)->delete();
                $totalPrice = 0;
                foreach ($favoriteProducts as $product) {
                    $cart=  parent::addToCart($request->userId,$request->agentId,$request->addressType,1,$product['productId']);
                    $product->isFavorite = 1;

                    $price= $product->mosquePrice;//mosque,home,company
                    if($cart->addressType == 'home' )
                    {
                        $price= $product->homePrice;
                    }
                    if($cart->addressType == 'company' )
                    {
                        $price= $product->officialPrice;
                    }
                    $totalPrice = $totalPrice+$price;

                }
                $cart->productCount= count($favoriteProducts);
                $cart->totalPrice= $totalPrice;
                

                return $this->response(true,'success',$cart);
            }
        } catch (Exception $e) {
            return $this->response(false,'system error');
        }
        
    }
}
