<?php

namespace App\Http\Controllers;
use App\Models\City;
use App\Models\Region;
use App\Models\District;
use Illuminate\Support\Facades\Validator;

use Illuminate\Http\Request;

use App\Http\Resources\City\MobileCityCollection;
use App\Http\Resources\Region\MobileRegionCollection;
use App\Http\Resources\District\MobileDistrictCollection;


class AreaLookupController extends Controller
{
    //list all address
    public function listRegions(Request $request)
    {
        try {
            return new MobileRegionCollection(Region::where('status',1)->get());
        } catch (Exception $e) {
            return $this->response(false,'system error');
        }
        
    }

    //list all address
    public function listCities(Request $request)
    {
        try {
            return new MobileRegionCollection(City::where('status',1)->get());
        } catch (Exception $e) {
            return $this->response(false,'system error');
        }
        
    }
    //list user address
    public function listRegionCities($regionId)
    {
        try {
            $region = Region::find($regionId);
            if($region == null){
                return $this->response(false,'id is not found');
            }
            return new MobileRegionCollection(City::where('region_id',$regionId)->where('status',1)->get());
        } catch (Exception $e) {
            return $this->response(false,'system error');
        }
       
        
    }

    //list user address
    public function listRegionCityDistricts($regionId,$cityId = null)
    {
        try {
            $region = Region::find($regionId);
            if($region == null){
                return $this->response(false,'id is not found');
            }

            if(!is_null($cityId)){
                $city = City::find($cityId);
                if($city == null){
                    return $this->response(false,'id is not found');
                }

            }
            $data = District::where('status','1');
            $data = $data->where('region_id',$regionId);

            if(!is_null($cityId)){
                $data = $data->where('city_id',$cityId);
            }

            // return (new MobileDistrictCollection($data->get()));
            return (new MobileDistrictCollection($data->get()))->serializeForList(request());
        } catch (Exception $e) {
            return $this->response(false,'system error');
        }
       
        
    }

}
