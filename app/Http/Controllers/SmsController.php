<?php

namespace App\Http\Controllers;
use App\Models\Sms;
use App\Models\User;
use App\Models\Cart;
use Illuminate\Support\Facades\Validator;
use Illuminate\Http\Request;
use Carbon\Carbon;

class SmsController extends Controller
{
    //check
    public function check(Request $request)
    {

        try {
            $data = $request->only(['userId','otp']);
            $rules = [
                'userId' => 'required|numeric',
                'otp' => 'required',otp
            ];
            $validator = Validator::make($data, $rules);
            if ($validator->fails()) {
                return $this->response(false,$this->validationHandle($validator->messages()));
            } else {
                $user = User::find($request->userId);
                if($user == null)
                {
                    return $this->response(false,'not valid user');
                }

                $result = Sms::where('userId',$request->userId)
                ->where('otp',$request->otp)
                ->where('status',2)
                ->update(['status' => 1]);
                if($result == 0) // no update
                {
                    if($user->language == 'ar')
                    {
                        return $this->response(false,'كود التفعيل غير صحيح');
                    }
                    return $this->response(false,'not valid otp');
                }
                // update user status
                $user->update(['status' => 1,'mobile_verified_at'=>Carbon::now()]);

                // replace guest cart (fcmToken with user Id)
                // move guest cart to user cart
                $result = Cart::where('userId',$user->fcmToken)
                ->update(['userId' => $user->id]);

                return $this->response(true,'success');
            }
        } catch (Exception $e) {
            return $this->response(false,'system error');
        }

    }

    public function changePassword(Request $request)
    {

        try {
            $data = $request->only(['userId','otp','password']);
            $rules = [
                'userId' => 'required|numeric',
                'otp' => 'required',
                'password' => 'required',
            ];
            $validator = Validator::make($data, $rules);
            if ($validator->fails()) {
                return $this->response(false,$this->validationHandle($validator->messages()));
            } else {
                $user = User::find($request->userId);
                if($user == null)
                {
                    return $this->response(false,'not valid user');
                }

                $result = Sms::where('userId',$request->userId)->where('otp',$request->otp)->where('status',2)
                ->update(['status' => 1]);
                if($result == 0) // no update
                {
                    if($user->language == 'ar')
                    {
                        return $this->response(false,'كود التفعيل غير صحيح');
                    }
                    return $this->response(false,'not valid otp');
                }
                // update user status
                $user = User::where('id',$request->userId)->first();
                User::where('id',$request->userId)->update(['status' => 1,'password'=>bcrypt($request->password)]);

                $this->sendNotification($user->fcmToken,'App\Notifications\PasswordChanged',$user->language);


                return $this->response(true,'success');
            }
        } catch (Exception $e) {
            return $this->response(false,'system error');
        }

    }
}
