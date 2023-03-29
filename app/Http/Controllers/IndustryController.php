<?php

namespace App\Http\Controllers;

use App\Models\Customer;
use Illuminate\Http\Request;
use App\Models\Industry;
use App\Models\Agent;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Facades\Auth;
use Exception;
use App\Jobs\SendSms;

class IndustryController extends Controller
{

    //details
    public function details($id)
    {


        try {
            $record = Industry::find($id);
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
            $users = Industry::all();
            return $this->response(true,'success',$users);

        } catch (Exception $e) {
            return $this->response(false,'system error');
        }

    }

    public function getIndustry(Request $request)
    {

        try {
            $industry = auth()->guard('industry')->user();
            return $this->response(true,'success',$industry);
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
                'language'   => 'required',
                'fcmToken'   => 'required',
                'password' => 'required|min:4'
            ];
            $validator = Validator::make($data, $rules);
            if ($validator->fails()) {
                return $this->response(false,$this->validationHandle($validator->messages()));
            } else {
                $credentials = request(['mobile', 'password']);

                if (! $token = auth()->guard('industry')->attempt($credentials)) {

                    if($request->language == 'ar'){
                        return response()->json(['status' => false,'message' => ' رقم الجوال او كلمة المرور غير صحيحة','data' => new Exception], 200);
                    }
                    return response()->json(['status' => false,'message' => 'mobile number or password is not correct','data' => new Exception], 200);
                }
                $user = Industry::where('mobile', $request->get('mobile'))->first();
                $user->fcmToken = $request->fcmToken;
                $user->language = $request->language;
                $user->save();

                $user->api_token = $token;
                return $this->response(true,'success',$user);
            }
        } catch (Exception $e) {
            return $this->response(false,'system error');
        }

    }
    public function loginFromPortal(Request $request)
    {
        $data = $request->only(['mobile','password','isAgent']);
        $rules = [
            'mobile'   => 'required',
            'password' => 'required|min:4',
            'isAgent' => 'required'
        ];

        $validator = Validator::make($data, $rules);
        if ($validator->fails()) {
            return $this->newResponse(false,$this->validationHandle($validator->messages()));
        }

        try {

                $credentials = request(['mobile', 'password']);
//                if($request->isAgent)
//                {
//                    if (! $token = auth()->guard('agent')->attempt($credentials)) {
//                        return $this->response(false,'Unauthorized');
//                    } else {
//                        $agent = Agent::where('mobile', $request->get('mobile'))->first();
//                        $agent->api_token = $token;
//                        $agent->isAdmin = 3; // agent
//                        return $this->response(true,'success',$agent);
//                    }
//                }

//                else {
//                    if (! $token = auth()->guard('industry')->attempt($credentials)) {
//                        return response()->json(['status' => false,'message' => 'Unauthorized','data' => new Exception], 200);
//                    }
                    $industry = Industry::where('mobile', $request->get('mobile'))->first();
                    $industry->api_token = $token = auth()->guard('industry')->attempt($credentials);
                    return $this->response(true,'success',$industry);
//                }


        } catch (Exception $e) {
            // return $e;
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
                $result = Industry::whereIn('id', $request->ids)
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
            $data = $request->only(['name','mobile','password','fcmToken','isAdmin','language']);
            $rules = [
                'name' => 'required',
                'mobile' => 'required',
                'isAdmin' => 'required',
                // 'status' => 'required',
                'password' => 'required',
                // 'fcmToken' => 'required',
                // 'language' => 'required',
            ];

            $validator = Validator::make($data, $rules);
            if ($validator->fails()) {
                return $this->response(false,$this->validationHandle($validator->messages()));
            } else {
                    $oldREcord = Industry::where('mobile',$data['mobile'])->first();
                    if($oldREcord  == null)
                    {
                        $newRecord =  Industry::create($data);
                        $token =  Auth::guard('industry')->login($newRecord);
                        $newRecord->api_token = $token;
                        return $this->response(true,'success',$newRecord);
                    } else {
                        return $this->response(false,'mobile already existed');

                    }

            }

        } catch (Exception $e) {
            return $this->response(false,'system error');
        }

    }

    public function update(Request $request)
    {
        try {
            $data = $request->only(['id','name','mobile','status','isAdmin']);
            $rules = [
                'id' => 'required',
                'name' => 'required',
                'isAdmin' => 'required',

                'mobile' => 'required',
                'status' => 'required',
            ];

            $validator = Validator::make($data, $rules);
            if ($validator->fails()) {
                return $this->response(false,$this->validationHandle($validator->messages()));
            } else {

                // check region
                // try {

                    $oldRecord = Industry::where('id',$data['id'])->first();
                    if($oldRecord  == null)
                    {
                        return $this->response(false,'id is not valid');
                    }

                    $oldRecord = Industry::where('id','<>',$data['id'])->where('mobile',$data['mobile'])->first();
                    if($oldRecord  != null)
                    {
                        return $this->response(false,'mobile already existed');
                    }
                    $result = Industry::where('id', $request->id)
                    ->update(
                        [
                            'name' => $request->name,
                            'isAdmin' => $request->isAdmin,
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
                    $result = Industry::where('id', $request->id)
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
                $industry = Industry::where('mobile',$request->mobile)->first();
                if($industry == null)
                {
                    if($request->language !=null && $request->language == 'en')
                    {
                        return $this->response(false,'not valid information');
                    } else {
                        return $this->response(false,'رقم الجوال غير صحيح');
                    }
                }
                $otp = rand(1000, 9999);
                $this->sendSms('Your OTP is '.$otp,$request->mobile);

                // send sms
                $industry->otp =$otp;
                $industry->save();
                return $this->response(true,'success',$industry->id);
            }
        } catch (Exception $e) {
            return $this->response(false,'system error');
        }
    }

    public function checkIndustryToResetPassword(Request $request)
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
                $result = Industry::where('mobile',$request->mobile)->first();
                if($result == null)
                {
                    if($request->language !=null && $request->language == 'en')
                    {
                        return $this->response(false,'not valid information');
                    } else {
                        return $this->response(false,'رقم الجوال غير صحيح');
                    }                }

                // send sms
                $otp = rand(1000, 9999);
                $result->otp = $otp;
                $this->sendSms('Your OTP is '.$otp,$request->mobile);

                $result->save();
                return $this->response(true,'success',$result->id);
            }
        } catch (Exception $e) {
            return $this->response(false,'system error');
        }

    }

    public function changePassword(Request $request)
    {

        try {
            $data = $request->only(['industryId','otp','password']);
            $rules = [
                'industryId' => 'required|numeric',
                'otp' => 'required',
                'password' => 'required',
            ];
            $validator = Validator::make($data, $rules);
            if ($validator->fails()) {
                return $this->response(false,$this->validationHandle($validator->messages()));
            } else {
                // update user status
                $result = Industry::where('id',$request->industryId)->where('otp',$request->otp)
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

    public function resetPasswordFromPortal(Request $request)
    {

        try {
            $data = $request->only(['id','password']);
            $rules = [
                'id' => 'required|numeric',
                'password' => 'required',
            ];
            $validator = Validator::make($data, $rules);
            if ($validator->fails()) {
                return $this->response(false,$this->validationHandle($validator->messages()));
            } else {
                // update user status
                $result = Industry::where('id',$request->id)
                ->update(['password'=>bcrypt($request->password)]);
                if($result == 0)
                {
                    return $this->response(false,'failed to change password');
                }
                return $this->response(true,'success');
            }
        } catch (Exception $e) {
            return $this->response(false,'system error');
        }

    }


    public function delete($id)
    {
        $user = Industry::find($id);
        if($user) {
            if ($user->delete()) {
                return $this->newResponse(true, __('api.success_response'));
            }
        }

        return $this->newResponse(false,__('api.fails_response'));
    }

    public function getUserByMobile(Request $request)
    {
        try {
            $data = $request->only(['mobile']);
            $rules = [
                'mobile' => 'required|numeric',
            ];
            $validator = Validator::make($data, $rules);
            if ($validator->fails()) {
                return $this->response(false,$this->validationHandle($validator->messages()));
            } else {
                // update user status
                $result = Industry::where('mobile',$request->mobile)->first();
                if($result)
                {
                    return $this->response(true,'success', $result);
                }
                return $this->response(false,'User Not existed');
            }
        } catch (Exception $e) {
            return $this->response(false,'system error');
        }
    }
}
