<?php

namespace App\Http\Controllers;
use App\Models\City;
use App\Models\Region;
use Illuminate\Support\Facades\Validator;

use Illuminate\Http\Request;

class CityController extends Controller
{
    //list all address
    public function list(Request $request)
    {
        try {
            return $this->response(true,'success',City::all());
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
            return $this->response(true,'success',City::where('region_id',$regionId)->get());
        } catch (Exception $e) {
            return $this->response(false,'system error');
        }
       
        
    }
    //details
    public function details($id)
    {
        try {
            $address = City::find($id);
            if($address == null){
                return $this->response(false,'id is not found');
            }
            return $this->response(true,'success',$address);
        } catch (Exception $e) {
            return $this->response(false,'system error');
        }
    }
    //delete
    public function delete($id)
    {    
        try {
            $address = City::find($id);
            if($address == null){
                return $this->response(false,'id is not found');
            }
            if($address->delete())
            {
                return $this->response(true,'success');

            }else {
                return $this->response(false,'failed');
            }
        } catch (Exception $e) {
            return $this->response(false,'system error');
        }
        
    }
    //create
    public function create(Request $request)
    {
        try {
            $data = $request->only(['regionId','englishName','arabicName']);
            $rules = [
                'regionId' => 'required|numeric',
                'englishName' => 'required',
                'arabicName' => 'required',
                
            ];

            $validator = Validator::make($data, $rules);
            if ($validator->fails()) {
                return $this->response(false,$this->validationHandle($validator->messages()));
            } else {
                $data = ['region_id'=>$request->regionId,'englishName'=>$request->englishName,'arabicName'=>$request->arabicName];
                $newRecord =  City::create($data);
                return $this->response(true,'success',$newRecord);
            }
        } catch (Exception $e) {
            return $this->response(false,'system error');
        }
        
    }

    //update status
    public function changeStatus(Request $request)
    {
        
        try {
            $data = $request->only(['ids', 'status']);
            $rules = [
                'ids' => 'required',
                'status' => 'required|numeric',
            ];

            $validator = Validator::make($data, $rules);
            if ($validator->fails()) {
                return $this->response(false,$this->validationHandle($validator->messages()));
            } else {
                $result = City::whereIn('id', $request->ids)
                ->update(
                    ['status' => $request->status]);
                if($result == 0) // no update
                {
                    return $this->response(false,'not valid id');
                }
                return $this->response(true,'success');

            }
        } catch (Exception $e) {
            return $this->response(false,'system error');
        }
        
    }

    //update
    public function update(Request $request)
    {
        
        try {
            $data = $request->only(['id', 'regionId','englishName','arabicName','status']);
            $rules = [
                'id' => 'required|numeric',
                'regionId' => 'required|numeric',
                'englishName' => 'required',
                'status' => 'required',
                'arabicName' => 'required',
            ];

            $validator = Validator::make($data, $rules);
            if ($validator->fails()) {
                return $this->response(false,$this->validationHandle($validator->messages()));
            } else {
                $result = City::where('id', $request->id)
                ->update(
                    ['region_id' => $request->regionId,
                    'englishName' => $request->englishName,
                    'status' => $request->status,
                    'arabicName' => $request->arabicName]);
                if($result == 0) // no update
                {
                    return $this->response(false,'not valid id');
                }
                return $this->response(true,'success');
            }
        } catch (Exception $e) {
            return $this->response(false,'system error');
        }
        
    }
}
