<?php

namespace App\Http\Controllers;

use App\Models\PostponeOrderRequest;
use App\Models\PostponeReason;
use Illuminate\Support\Str;
use Illuminate\Http\Request;
use App\Models\Delegator;
use App\Models\Industry;
use App\Models\Sms;
use App\Models\City;
use App\Models\Region;
use Illuminate\Support\Facades\Validator;
use Grimzy\LaravelMysqlSpatial\Types\Polygon;
use Grimzy\LaravelMysqlSpatial\Types\LineString;
use Grimzy\LaravelMysqlSpatial\Types\Point;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Auth;
use Tymon\JWTAuth\Facades\JWTAuth;
use Exception;
use App\Jobs\SendSms;

class DelegatorController extends Controller
{
    //details
    public function details($id)
    {

        try {

        $record = Delegator::find($id);
        if($record == null){
            return $this->response(false,'id is not found');
        }
        return $this->response(true,'success',$record);
        } catch (Exception $e) {
            return $this->response(false,'system error');
        }
    }
    //list all
    public function list(Request $request)
    {

        try {
            $delegators = Delegator::all();
            return $this->response(true,'success',$delegators);
        } catch (Exception $e) {
            return $this->response(false,'system error');
        }
    }
    public function listPerAgent(Request $request)
    {

        try {
            $data = $request->only(['agentId']);
            $rules = [
                'agentId'   => 'required',
            ];
            $validator = Validator::make($data, $rules);
            if ($validator->fails()) {
                return $this->response(false,$this->validationHandle($validator->messages()));
            } else {
                $delegators = Delegator::where('agentId',$request->agentId)->get();
                return $this->response(true,'success',$delegators);
            }

        } catch (Exception $e) {
            return $this->response(false,'system error');
        }
    }

    public function getDelegator(Request $request)
    {

        try {
            $delegator = auth()->guard('delegator')->user();
            return $this->response(true,'success',$delegator);
        } catch (Exception $e) {
            return $this->response(false,'system error');
        }


    }

    public function login(Request $request)
    {

        try {
            $data = $request->only(['mobile','password','language','fcmToken']);
            $rules = [
                'mobile'   => 'required',
                'fcmToken'   => 'required',
                'password' => 'required|min:4',
                'language' => 'required|in:ar,en',
            ];
            $validator = Validator::make($data, $rules);
            if ($validator->fails()) {
                return $this->response(false,$this->validationHandle($validator->messages()));
            } else {
                $credentials = request(['mobile', 'password']);
                if (! $token = auth()->guard('delegator')->attempt($credentials)) {

                    if($request->language == 'ar')
                        return response()->json(['status' => false,'message' => 'رقم الجوال  أو كلمة المرور غير صحيحة','data' => new Exception], 200);
                    return response()->json(['status' => false,'message' => 'invalid mobile number or password','data' => new Exception], 200);


                    // return response()->json(['status' => false,'message' => 'Unauthorized','data' => new Exception], 200);
                }
                $delegator = Delegator::where('mobile', $request->get('mobile'))->whereIn('status', [1,3])->first();
                if($delegator == null){
                    if($request->language == 'ar')
                        return response()->json(['status' => false,'message' => 'رقم الجوال  أو كلمة المرور غير صحيحة','data' => new Exception], 200);
                    return response()->json(['status' => false,'message' => 'invalid mobile number or password','data' => new Exception], 200);
                }
                $delegator->language = $request->language;
                $delegator->fcmToken = $request->fcmToken;
                $delegator->save();
                $delegator->api_token = $token;
                return $this->response(true,'success',$delegator);
            }
        } catch (Exception $e) {
            return $this->response(false,'system error');
        }
    }

    //delete
    public function delete($id)
    {

        try {

            $address = Delegator::find($id);
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


        //update status
    public function changeStatus(Request $request)
    {
        try {
            $data = $request->only(['ids','status']);
            $rules = [
                'ids' => 'required',
                'status' => 'required|numeric',
            ];

            $validator = Validator::make($data, $rules);
            if ($validator->fails()) {
                return $this->response(false,$this->validationHandle($validator->messages()));
            } else {
                $result = Delegator::whereIn('id', $request->ids)
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

    public function create(Request $request)
    {
        try {
            $data = $request->only(['agentId','name','region','city','mobile','password','fcmToken' ,'city_id','district_id','region_id']);
            $rules = [
                'name' => 'required',
                'agentId' => 'required',
                'region' => 'required',
                'city' => 'required',
                'mobile' => 'required',
                // 'status' => 'required',
                'password' => 'required',
                'fcmToken' => 'required',
            ];

            $validator = Validator::make($data, $rules);
            if ($validator->fails()) {
                return $this->response(false,$this->validationHandle($validator->messages()));
            } else {

                // check region
                $region=Region::find($data['region']);
                if($region == null){
                    return $this->response(false,'invalid region');
                }

                // check city
                $city=City::where('id',$data['city'])->where('region_id',$data['region'])->first();
                if($city == null){
                    return $this->response(false,'invalid city');
                }


                $oldREcord = Delegator::where('mobile',$data['mobile'])->first();
                if($oldREcord  == null)
                {
                    $newRecord =  Delegator::create($data);
                    $token =  Auth::guard('delegator')->login($newRecord);
                    $newRecord->api_token = $token;
                    return $this->response(true,'success',$newRecord);
                } else {
                    return $this->response(false,'mobile already existed');
                }
            }
        } catch (Exception $e) {
            // return $e;
            return $this->response(false,'system error');
        }


    }


    public function update(Request $request)
    {
        try {

            $data = $request->only(['agentId','id','region','name','city','mobile','status' ,'city_id','district_id','region_id']);
            $rules = [
                'id' => 'required',
                'agentId' => 'required',
                'name' => 'required',
                'region' => 'required',
                'city' => 'required',
                'mobile' => 'required',
                'status' => 'required',
            ];

            $validator = Validator::make($data, $rules);
            if ($validator->fails()) {
                return $this->response(false,$this->validationHandle($validator->messages()));
            } else {

                // check region
                // try {
                    $region=Region::find($data['region']);
                    if($region == null){
                        return $this->response(false,'invalid region');
                    }

                    // check city
                    $city=City::where('id',$data['city'])->where('region_id',$data['region'])->first();
                    if($city == null){
                        return $this->response(false,'invalid city');
                    }

                    $oldRecord = Delegator::where('id',$data['id'])->first();
                    if($oldRecord  == null)
                    {
                        return $this->response(false,'id is not valid');
                    }

                    $oldRecord = Delegator::where('id','<>',$data['id'])->where('mobile',$data['mobile'])->first();
                    if($oldRecord  != null)
                    {
                        return $this->response(false,'mobile already existed');
                    }
                    $result = Delegator::where('id', $request->id)
                    ->update(
                        [


                            'name' => $request->name,
                            'agentId' => $request->agentId,
                            'region' => $request->region,
                            'city_id' => $request->city_id,
                            'district_id' => $request->district_id,
                            'region_id' => $request->region_id,
                            'city' => $request->city,
                            'mobile' => $request->mobile,
                            'status' => $request->status,
                        ]);
                    if($result == 0) // no update
                    {
                        return $this->response(false,'not valid id');
                    }
                    return $this->response(true,'success');
                // } catch (\Throwable $th) {
                //     return $this->response(false,$th);
                // }
            }
        } catch (Exception $e) {
            return $this->response(false,'system error');
        }


    }

    public function updateProfile(Request $request)
    {
        try {

            $data = $request->only(['id','name']);
            $rules = [
                'id' => 'required',
                'name' => 'required',
            ];

            $validator = Validator::make($data, $rules);
            if ($validator->fails()) {
                return $this->response(false,$this->validationHandle($validator->messages()));
            } else {
                    $result = Delegator::where('id', $request->id)
                    ->update(
                        [
                            'name' => $request->name,
                        ]);
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

    public function requestOtpToResetPassword(Request $request)
    {
        try {
            $data = $request->all();
            $rules = [
                'mobile' => 'required',
            ];
            $validator = Validator::make($data, $rules);
            if ($validator->fails()) {
                return $this->response(false,$this->validationHandle($validator->messages()));
            } else {
                $delegator = Delegator::where('mobile',$request->mobile)->first();
                if($delegator == null)
                {
                    if($request->language !=null && $request->language == 'en')
                    {
                        return $this->response(false,'not valid information');
                    } else {
                        return $this->response(false,'رقم الجوال غير صحيح');
                    }
                }
                $otp = rand(1000, 9999);
                // send sms
                $this->sendSms('Your OTP is '.$otp,$request->mobile);
                $delegator->otp =$otp;
                $delegator->save();
                return $this->response(true,'success',$delegator->id);
            }
        } catch (Exception $e) {
            return $this->response(false,'system error');
        }
    }
    public function changePassword(Request $request)
    {

        try {
            $data = $request->only(['delegatorId','otp','password']);
            $rules = [
                'delegatorId' => 'required|numeric',
                'otp' => 'required',
                'password' => 'required',
            ];
            $validator = Validator::make($data, $rules);
            if ($validator->fails()) {
                return $this->response(false,$this->validationHandle($validator->messages()));
            } else {
                // update user status
                $result = Delegator::where('id',$request->delegatorId)->where('otp',$request->otp)
                ->update(['status' => 1,'password'=>bcrypt($request->password)]);
                if($result == 0)
                {
                    return $this->response(false,'invalid OTP');
                }
                return $this->response(true,'success');
            }
        } catch (Exception $e) {
            return $this->response(false,'system error');
        }

    }

    public function checkDelegatorToResetPassword(Request $request)
    {
        try {
            $data = $request->all();
            $rules = [
                'mobile' => 'required',
            ];
            $validator = Validator::make($data, $rules);
            if ($validator->fails()) {
                return $this->response(false,$this->validationHandle($validator->messages()));
            } else {
                $result = Delegator::where('mobile',$request->mobile)->first();
                if($result == null)
                {
                    if($request->language !=null && $request->language == 'en')
                    {
                        return $this->response(false,'not valid information');
                    } else {
                        return $this->response(false,'رقم الجوال غير صحيح');
                    }                }
                $otp = rand(1000, 9999);
                $result->otp = $otp;
                $this->sendSms('Your OTP is '.$otp,$request->mobile);
                $result->save();
                // return userId to use in reset password

                return $this->response(true,'success',$result->id);
            }
        } catch (Exception $e) {
            return $this->response(false,'system error');
        }

    }

    public function listRejectionReasons(Request $request)
    {


    }

    public function postponeReasonsList(Request $request){

        $reasons=PostponeReason::where('is_active',true)->get(['id','title_ar','title_en']);

        return $this->response(true,__('api.success_response'),$reasons);
    }

    public function postponeOrder(Request $request){


        $data = $request->only(['order_id','delegator_id','reason_id']);
        $rules = [
            'order_id' => 'required|numeric|exists:orders,id',
            'delegator_id' => 'required|numeric|exists:delegators,id',
            'reason_id' => 'required|numeric|exists:postpone_reasons,id',
        ];
        $validator = Validator::make($data, $rules);
        if ($validator->fails()) {
            return $this->response(false,$this->validationHandle($validator->messages()));
        }

        $delegatore = Delegator::find($request->delegator_id);
        if($delegatore) {
            $order = $delegatore->orders()->find($request->order_id);
            $postpone=PostponeOrderRequest::create([
                'order_id'=>$request->order_id,
                'delegator_id'=>$request->delegator_id,
                'status'=>'opened',
                'reason_id'=>$request->reason_id,

            ]);

            return $this->response(true,__('api.success_response'));

        }
        return $this->response(false,__('api.fails_response'));

    }
}
