<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Resources\AreaRegionCityDistrictResource;
use App\Http\Resources\BannarImageResource;
use App\Http\Resources\ProductResource;
use App\Models\Agent;
use App\Models\Banner;
use App\Models\City;
use App\Models\District;
use App\Models\Product;
use App\Models\Region;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;
use Illuminate\Validation\Rule;
use DB;
class HomeController extends Controller
{
    public function home(Request $request){
        $Requestdata = $request->only(['lat','lng']);
        $rules = [
            'lat' => 'required',
            'lng' => 'required',
        ];
        $validator = Validator::make($Requestdata, $rules);
        if ($validator->fails()){
            return $this->newResponse(false,$this->validationHandle($validator->messages()));
        }
        $agent = new Agent();
        $agent = $agent->search($request->lat,$request->lng);
        if($agent){
            $banners = Banner::where('status',1)->get();
            $data['banners']= BannarImageResource::collection($banners);

             $products = Product::leftJoin('agentProducts','agentProducts.productId','=','products.id')
                ->where('agentProducts.agentId',$agent->id)
//                ->where('products.type', 1)
                // ->where('agentProducts.status',1)
                ->select('products.id as id','arabicName','englishName','picture','agentProducts.mosquePrice','agentProducts.homePrice as price','agentProducts.officialPrice','agentProducts.otherPrice','agentProducts.status',
                 'agentProducts.min_order_qty')
                ->paginate(20);

            $data['agent_id']=$agent->id;
            $data['has_more_pages']=$products->hasMorePages();
            $data['products'] = ProductResource::collection($products);

            return $this->newResponse(true,__('api.success_response'),'',[],$data);
        }

        return $this->newResponse(false,__('api.not_available_area'));
    }
    public function regions(Request $request){
        $regions=Region::search($request)->get();
        $regions=AreaRegionCityDistrictResource::collection($regions);
        return $this->newResponse(true,__('api.success_response'),'regions',$regions);
    }
    public function cities(Request $request,$regionId){
        $cities=City::where('region_id',$regionId)->search($request)->get();
        $cities=AreaRegionCityDistrictResource::collection($cities);
        return $this->newResponse(true,__('api.success_response'),'cities',$cities);
    }
    public function districts(Request $request,$cityId){
        $districts=District::where('city_id',$cityId)->search($request)->get();
        $districts=AreaRegionCityDistrictResource::collection($districts);
        return $this->newResponse(true,__('api.success_response'),'districts',$districts);
    }

}
