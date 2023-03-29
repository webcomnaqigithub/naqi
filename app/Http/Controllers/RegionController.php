<?php

namespace App\Http\Controllers;
use App\Models\City;
use App\Models\Region;
use Illuminate\Support\Facades\Validator;
use Illuminate\Http\Request;

class RegionController extends Controller
{
    //list all address
    public function list(Request $request)
    {
        
        try {
            return $this->response(true,'success',Region::all());
        } catch (Exception $e) {
            return $this->response(false,'system error');
        }
    }
   
    //details
    public function details($id)
    {
        
        try {
            $address = Region::find($id);
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
            $address = Region::find($id);
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
            $data = $request->only(['englishName','arabicName']);
            $rules = [
                'englishName' => 'required',
                'arabicName' => 'required',
                
            ];
    
            $validator = Validator::make($data, $rules);
            if ($validator->fails()) {
                return $this->response(false,$this->validationHandle($validator->messages()));
            } else {
                $newRecord =  Region::create($data);
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
                $result = Region::whereIn('id', $request->ids)
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
            $data = $request->only(['id','englishName','arabicName','status']);
            $rules = [
                'id' => 'required|numeric',
                'englishName' => 'required',
                'status' => 'required',
                'arabicName' => 'required',
            ];
    
            $validator = Validator::make($data, $rules);
            if ($validator->fails()) {
                return $this->response(false,$this->validationHandle($validator->messages()));
            } else {
                $result = Region::where('id', $request->id)
                ->update(
                    [
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
