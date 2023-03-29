<?php

namespace App\Http\Controllers;
use App\Http\Resources\OrderResource;
use App\Http\Resources\OrderWebResource;
use App\Models\OrderProduct;
use App\Models\FavoriteProduct;
use App\Models\Order;
use Illuminate\Support\Facades\Validator;
use Illuminate\Http\Request;
use Illuminate\Support\Arr;

class OrderProductController extends Controller
{
    //details
    public function details($id)
    {

        try {
            $record =  Order::leftJoin('users', 'users.id', '=', 'orders.userId')
            ->leftJoin('agents', 'agents.id', '=', 'orders.agentId')
            ->leftJoin('delegators', 'delegators.id', '=', 'orders.delegatorId')
            ->leftJoin('address', 'address.id', '=', 'orders.addressId')
            ->leftJoin('regions_lite', 'regions_lite.id', '=', 'orders.region_id')
            ->leftJoin('cities_lite', 'cities_lite.id', '=', 'orders.city_id')
            ->leftJoin('districts_lite', 'districts_lite.id', '=', 'orders.district_id')

            ->select('orders.*','users.name as clientName','users.mobile as clientMobile','address.lat','address.lng',
            'agents.name as agentName','agents.mobile as agentMobile','delegators.name as delegatorName','delegators.mobile as delegatorMobile','regions_lite.arabicName as region','cities_lite.arabicName as city','districts_lite.arabicName as district')
            ->where('orders.id',$id)
            ->orderBy('created_at', 'desc')->first() ;
            if($record == null){
                return $this->response(false,'id is not found');
            }
            $productsOfOrder = OrderProduct::select('orderProducts.id','orderProducts.productId','orderProducts.orderId','products.arabicName','products.englishName','products.picture',
            'products.mosquePrice','products.otherPrice','products.homePrice','products.officialPrice','orderProducts.amount')
            ->leftJoin('products', 'products.id', '=', 'orderProducts.productId')
            ->where('orderId',$id)->get();
            $record->products = $productsOfOrder;
            return $this->response(true,'success',$record);
        } catch (Exception $e) {
            return $this->response(false,'system error');
        }

    }
    //details
    public function viewDetails(Order $id)
    {
        $order=OrderWebResource::make($id);
        return $this->newResponse(true,__('api.success_response'),'order',$order);
    }

    //list all products of order
    public function listOrderProduct(Request $request)
    {
        try {
            $productsOfOrder = OrderProduct::select('orderProducts.id','orderProducts.productId','orderProducts.orderId','products.arabicName','products.englishName','products.picture',
            'products.mosquePrice','products.otherPrice','products.homePrice','products.officialPrice','orderProducts.amount')
            ->leftJoin('products', 'products.id', '=', 'orderProducts.productId')
            ->where('orderId',$request->orderId)->get();

            // add favorite flag
            $favoriteProducts = FavoriteProduct::where('userId',$request->userId)->get();//->pluck('productId');
            foreach ($productsOfOrder as $product) {
                if($favoriteProducts->contains('productId', $product->productId)) {
                    $product->isFavorite = 1;
                }else{
                    $product->isFavorite = 0;
                }
            }
            return $this->response(true,'success',$productsOfOrder);
        } catch (Exception $e) {
            return $this->response(false,'system error');
        }
    }
}
