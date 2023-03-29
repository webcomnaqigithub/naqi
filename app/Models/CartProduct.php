<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class CartProduct extends Model
{
    //
    protected $table = 'cart_products';
    protected $guarded=[];
//    protected $fillable = ['cartId','productId','amount'];
    protected $appends=['price','total'];

    public function getProductsOfCart($cartId){
        $products = CartProduct::select('cart_products.id','cart_products.productId','cart_products.cartId','products.arabicName','products.englishName','products.picture',
        'products.mosquePrice','products.otherPrice','products.type','products.homePrice','products.officialPrice','cart_products.amount')
        ->leftJoin('products', 'products.id', '=', 'cart_products.productId')
        ->where('cartId',$cartId)->get();
        foreach($products as $product){
            $product->picture = url('/').$product->picture;
        }
        return $products;
    }


    public function cart(){
        return $this->belongsTo(Cart::class,'cartId');
    }
    public function product(){
        $address=$this->cart->address;
        $agentId=$this->cart->agentId;
        $target_price = '';
        switch ($address->type){
            case 'home':
                $target_price = 'homePrice';
                break;
            case 'mosque':
                $target_price = 'mosquePrice';
                break;
            case 'company':

                $target_price = 'officialPrice';
                break;
            default:
                $target_price = 'homePrice';
        }
        return $this->belongsTo(Product::class,'productId')
            ->leftJoin('agentProducts', 'agentProducts.productId', '=', 'products.id')
            ->where('agentProducts.agentId', $agentId)
            ->select('products.id as id', 'arabicName', 'englishName', 'picture', 'agentProducts.'.$target_price.' as price','agentProducts.status');
    }
    public function getPriceAttribute(){
        return $this->product->price;


    }
    public function getTotalAttribute(){
        return $this->getPriceAttribute() * $this->amount;
    }
}
