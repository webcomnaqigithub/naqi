<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\RejectionReason;
use Illuminate\Support\Facades\Validator;
use Carbon\Carbon;
class RejectionReasonController extends Controller
{
    //

    //list all 
    public function list(Request $request)
    {
        
        try {
            return $this->response(true,'success',RejectionReason::all());
        } catch (Exception $e) {
            return $this->response(false,'system error');
        }
    }
    //list all for app 
    public function listForApp(Request $request)
    {
        
        try {
            $lastOption = new RejectionReason;
            $lastOption->id = 0;
            $lastOption->arabicReason = 'أخرى';
            $lastOption->englishReason = 'others';
            $lastOption->status = 1;
            $lastOption->created_at = Carbon::now();
            $lastOption->updated_at = Carbon::now();
            $reasons = RejectionReason::where('status',1)->get();
            $reasons[] = $lastOption;
            return $this->response(true,'success',$reasons);
        } catch (Exception $e) {
            return $this->response(false,'system error');
        }
    }
   
    //details
    public function details($id)
    {
        
        try {
            $address = RejectionReason::find($id);
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
            $address = RejectionReason::find($id);
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
            $data = $request->only(['arabicReason','englishReason']);
            $rules = [
                'arabicReason' => 'required',
                'englishReason' => 'required',
                
            ];
    
            $validator = Validator::make($data, $rules);
            if ($validator->fails()) {
                return $this->response(false,$this->validationHandle($validator->messages()));
            } else {
                $newRecord =  RejectionReason::create($data);
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
                $result = RejectionReason::whereIn('id', $request->ids)
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
            $data = $request->only(['id','arabicReason','englishReason','status']);
            $rules = [
                'id' => 'required|numeric',
                'arabicReason' => 'required',
                'status' => 'required',
                'englishReason' => 'required',
            ];
    
            $validator = Validator::make($data, $rules);
            if ($validator->fails()) {
                return $this->response(false,$this->validationHandle($validator->messages()));
            } else {
                $result = RejectionReason::where('id', $request->id)
                ->update(
                    [
                    'arabicReason' => $request->arabicReason,
                    'status' => $request->status,
                    'englishReason' => $request->englishReason]);
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
