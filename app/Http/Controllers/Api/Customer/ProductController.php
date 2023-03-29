<?php

namespace App\Http\Controllers\Api\Customer;


use App\Http\Controllers\Controller;
use App\Http\Resources\BannarImageResource;
use App\Http\Resources\OfferResource;
use App\Http\Resources\ProductResource;
use App\Models\Agent;
use App\Models\AgentArea;
use App\Models\Banner;
use App\Models\Offer;
use App\Models\Product;
use App\Models\Favorite;
use Carbon\Carbon;
use Illuminate\Http\Request;
use DB;
use Illuminate\Support\Facades\Validator;

class ProductController extends Controller
{
    public function HomeProducts(Request $request)
    {
//        $homeData['banners']=BannarImageResource::collection(Banner::where('status',1)->get());
//        $products=Product::get();
//         $homeData['products'] = ProductResource::collection($products);
//        $homeData['has_more_pages'] =$products->hasMorePages();
//        return $this->newResponse(true,__('api.success_response'),'home',$homeData);
    }

    //search by location


    public function home(Request $request)
    {
        $data = $request->only(['address_id']);
        $rules = [
            'address_id' => 'required|exists:address,id,deleted_at,NULL',
//            'lng' => 'required',
//            'lat' => 'required',
//            'address_type' => 'nullable|in:home,mosque,company',
        ];
        $validator = Validator::make($data, $rules);
        if ($validator->fails()) {
            return $this->newResponse(false, $this->validationHandle($validator->messages()));
        }

        $user = $request->user();
        try {
            if ($user) {
                $customer_address = $user->addresses()->find($request->address_id);
                if ($customer_address) {
                    $target_price = '';
                    switch ($customer_address->type){
                        case 'home':
                            $target_price = 'homePrice';
                            break;
                        case 'mosque':
                            $target_price = 'mosquePrice';
                            break;
                        case 'company':

                            $target_price = 'officialPrice';
                            break;
                    }
                    $agent_area = new AgentArea();
                    $agent = $agent_area->search($customer_address->lat, $customer_address->lng);
                    if ($agent) {
                        $banners = Banner::where('status', 1)->get();
                        $data['banners'] = BannarImageResource::collection($banners);

                        $products = Product::leftJoin('agentProducts', 'agentProducts.productId', '=', 'products.id')
                            ->where('agentProducts.agentId', $agent->agent_id)
                            ->where('agentProducts.status',1)
                            ->select('products.id as id', 'arabicName', 'englishName', 'picture', 'agentProducts.'.$target_price.' as price','agentProducts.status',
                            'agentProducts.min_order_qty')
                            //  ->orderBy('agentProducts.status')
                            ->paginate(20);

                        $data['agent_id'] = $agent->agent_id;
                        $data['has_more_pages'] = $products->hasMorePages();
                        if($products->count() > 0){
                            $data['products'] = ProductResource::collection($products);
                        }else{
                            $data['products'] = [];
                        }

                        return $this->newResponse(true, __('api.success_response'), '', [], $data);
                    }else{
                        return $this->newResponse(false,__('api.not_available_area'));
                    }


                } else {
                    return $this->newResponse(false, __('api.not_exist_address'));
                }

            }
            $agent = new Agent();
            $agent = $agent->search($request->lat, $request->lng);
            if ($agent) {
                $banners = Banner::where('status', 1)->get();
                $data['banners'] = BannarImageResource::collection($banners);

                $products = Product::leftJoin('agentProducts', 'agentProducts.productId', '=', 'products.id')
                    ->where('agentProducts.agentId', $agent->id)
                    //                ->where('products.type', 1)
                    // ->where('agentProducts.status',1)
                    ->select('products.id as id', 'arabicName', 'englishName', 'picture', 'agentProducts.mosquePrice', 'agentProducts.homePrice as price', 'agentProducts.officialPrice', 'agentProducts.otherPrice', 'agentProducts.status')
                    ->orderBy('agentProducts.status')
                    ->paginate(10);

                $data['agent_id'] = $agent->id;
                $data['has_more_pages'] = $products->hasMorePages();
                $data['products'] = ProductResource::collection($products);

                return $this->newResponse(true, __('api.success_response'), '', [], $data);
            }
        } catch (\Throwable $th) {
            return $this->newResponse(false,$th->getMessage());
        }

    }


    public function home2(Request $request)
    {
        $data = $request->only(['lng', 'lat', 'address_type', 'customer_id']);
        $rules = [
            'customer_id' => 'nullable',
            'lng' => 'required',
            'lat' => 'required',
            'address_type' => 'nullable|in:home,mosque,company',
        ];
        $validator = Validator::make($data, $rules);
        if ($validator->fails()) {
            return $this->newResponse(false, $this->validationHandle($validator->messages()));
        }
        $homeData = $this->getProductsInMainScreen($request, $request->customer_id, $request->address_type);
        return $homeData;
        return $this->newResponse(true, __('api.success_response'), 'home', $homeData);
    }

    public function favoriteList(Request $request)
    {
        if(!$request->get('agent_id') && !$request->get('address_id')){
            return $this->newResponse(false, __('api.fails_response'));
        }
        $agent_id=(int)$request->get('agent_id');
        $address_id=(int)$request->get('address_id');
        $customer = $request->user('customer');
        try{

            $customer_address = $customer->addresses()->find($address_id);

            if ($customer_address) {
                $target_price = '';
                switch ($customer_address->type) {
                    case 'home':
                        $target_price = 'homePrice';
                        break;
                    case 'mosque':
                        $target_price = 'mosquePrice';
                        break;
                    case 'company':

                        $target_price = 'officialPrice';
                        break;
                }

                $favoriteProducts = $customer->favorites()->orderBy('created_at', 'desc')->with('content')->get()->pluck('content');
                $products_list = collect($favoriteProducts)->pluck('id')->toArray();
                $products = Product::leftJoin('agentProducts', 'agentProducts.productId', '=', 'products.id')
                    ->where('agentProducts.agentId', $agent_id)
//                ->where('products.type', 1)
                    // ->where('agentProducts.status',1)
                    ->select('products.id as id', 'arabicName', 'englishName', 'picture', 'agentProducts.' . $target_price . ' as price', 'agentProducts.status')
                    ->get();
                $products=collect($products)->whereIn('id',$products_list);
                $products = ProductResource::collection($products);
                return $this->newResponse(true, __('api.success_response'), 'products', $products);
            }
        }catch (\Exception $e){
                return $this->newResponse(false, $e->getMessage());
        }

//        return $this->newResponse(false, __('api.fails_response'));
    }

    public function addRemoveProductFavorite(Request $request)
    {
        $validator = \Validator::make($request->only('product_id'), [
            'product_id' => 'required|exists:products,id,deleted_at,NULL',
        ]);
        if ($validator->fails()) {
            return $this->newResponse(false, $this->validationHandle($validator->messages()));
        }


        try {
//    DB::beginTransaction();
            $user = $request->user();

            $prev = Favorite::where('content_id', $request->product_id)->where('content_type', Product::class)
                ->where('user_id', $user->id)->first();
            if ($prev) {
                Favorite::destroy($prev->id);

                return $this->newResponse(true, __('api.success_remove_from_favorite'));
            } else {
                $product = Product::find($request->get('product_id'));
                if ($product) {

                    $product->favorites()->create(['user_id' => $user->id]);
                    return $this->newResponse(true, __('api.success_add_to_favorite'));


                }

            }
//            DB::commit();
        } catch (\Exception $e) {
//            DB::rollback();
            return $this->newResponse(false, $e->getMessage());
        }

    }

    public function offersList(Request $request)
    {

        // if($request->get('agent_id')){
        //     $today=Carbon::today();
        //     $offers=Offer::where('agent_id',$request->get('agent_id'))->
        //     where('is_active',true)->where('start_date', '<=',$today)
        //         ->where('expire_date', '>',$today);
        //     $activebanners=$offers->where('is_banner',true)->get();
        //     $offers_list=$offers->get();
        //     $data['banners'] = OfferResource::collection($activebanners);
        //     $data['offers'] = OfferResource::collection($offers_list);
        //     return $this->newResponse(true, __('api.success_response'), '', [], $data);
        // }else{
        //     return $this->newResponse(false, __('api.send_agent_id'));
        // }


        if($request->get('agent_id')){
            $today=Carbon::today();
            // $offers=Offer::where('agent_id',$request->get('agent_id'))->
            // where('is_active',true)->where('start_date', '<=',$today)
            //     ->where('expire_date', '>',$today);
            $activebanners=Offer::where('agent_id',$request->get('agent_id'))->
            where('is_active',true)->where('start_date', '<=',$today)
                ->where('expire_date', '>',$today)->where('is_banner',true)->get();
            $offers_list=Offer::where('agent_id',$request->get('agent_id'))->
            where('is_active',true)->where('start_date', '<=',$today)
                ->where('expire_date', '>',$today)->get();
            $data['banners'] = OfferResource::collection($activebanners);
            $data['offers'] = OfferResource::collection($offers_list);
            return $this->newResponse(true, __('api.success_response'), '', [], $data);
        }else{
            return $this->newResponse(false, __('api.send_agent_id'));
        }

        return $this->newResponse(false, __('api.fails_response'));
    }
}
