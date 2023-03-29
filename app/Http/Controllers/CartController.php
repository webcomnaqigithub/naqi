<?php

namespace App\Http\Controllers;
use App\Models\Cart;
use Illuminate\Support\Facades\Validator;
use Illuminate\Http\Request;

class CartController extends Controller
{
    //

    //details
    public function details($id)
    {
        
        try {
            $cart = Cart::find($id);
            if($cart == null){
                return $this->response(false,'id is not found');
            }
            return $this->response(true,'success',$cart);
        } catch (Exception $e) {
            return $this->response(false,'system error');
        }

        
    }

    public function detailsByUserId($userId)
    {
        try {
            $cart = Cart::where('userId',$userId)->first();
            if($cart == null){
                return $this->response(false,'id is not found');
            }
            return $this->response(true,'success',$cart);
        } catch (Exception $e) {
            return $this->response(false,'system error');
        }
        
    }

}
