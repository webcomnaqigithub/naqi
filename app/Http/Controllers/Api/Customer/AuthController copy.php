<?php

namespace App\Http\Controllers\Api\Customer;

use App\Http\Controllers\Controller;
use App\Http\Resources\CustomerResource;
use App\Models\Cart;
use App\Models\Customer;
use App\Models\Sms;
use App\Models\User;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Validator;
use Illuminate\Validation\Rule;
use Intervention\Image\Facades\Image;
use File;
use Illuminate\Support\Arr;
class AuthController extends Controller
{

    public function register(Request $request)
    {

        $data = $request->only(['name','mobile','password','password_confirmation','avatar',
            'region_id',
            'city_id',
            'district_id',
            'lat',
            'lng',
            'address_name',
            'fcmToken',
            ]);
        $rules = [
            'name' => 'required',
//            'mobile' => ['required|unique:users,mobile,deleted_at,NULL'],
            'mobile' => ['required',  Rule::unique('users')->whereNull('deleted_at'),
                //  'regex:/^(05)(5|0|3|6|4|9|1|8|7)([0-9]{7})$/'
            ],
            'password' => 'required|min:4|max:25|confirmed',
            'avatar' => 'nullable|image|mimes:jpg,jpeg,png',
            'region_id' => 'required|exists:regions_lite,id',
            'city_id' => 'required|exists:cities_lite,id',
            'district_id' => 'nullable|exists:districts_lite,id',
            'lat' => 'required',
            'address_name' => 'required',
//            'fcmToken' => 'required',
            'lng' => 'required',
        ];

        $validator = Validator::make($data, $rules);
        if ($validator->fails()) {
            return $this->newResponse(false,$this->validationHandle($validator->messages()));
        }
        try {
                    $data['status']=3;
                    $customer =  Customer::create( Arr::except($data, [
                        // 'region_id',
                        // 'city_id',
                        // 'district_id',
                        // 'lat',
                        // 'lng',
                        'address_name',
                        ]));
                        $data['default']=1;
                        $data['name']=$data['address_name'];

                    $data['type']='home';

                    $customer->addresses()->create( Arr::except($data, [
                        'mobile',
                        'password',
                        'avatar',
                        'address_name',
                        'password_confirmation',
                        'fcmToken',
                    ]));

                    if($customer && $request->hasFile('avatar')){
                        $file=$request->file('avatar');

                        $img = Image::make($file->getRealPath());
                        $img=$img->resize('200','200');
                        $extension =$file->getClientOriginalExtension();
                        $path=public_path('uploads');
                        if(!File::exists($path)) {
                            File::makeDirectory($path, 0776, true, true);
                            // path does not exist
                        }
                        $randomName=rand(0,100).time().'.'.$extension;
                        $img->save(public_path('uploads/'.$randomName));
                        $customer->avatar=$randomName;
                        $customer->save();
                    }

                        // send sms
                        $otp= $this->sendOTP($customer);
                        $customer->otp_sms=$otp;
                        $customer=CustomerResource::make($customer);

                    return $this->newResponse(true,__('api.register_successfully'),'customer',$customer);


        } catch (Exception $e) {
            return $this->newResponse(false,__('api.fails_response'));
        }
//        catch (QueryException $e) {
//            return $this->newResponse(false,__('api.fails_response'));
//        }

    }

    public function updateProfile(Request $request)
    {
        $data = $request->only(['name','avatar']);
        $rules = [
            'name' => 'required',
            'avatar' => 'nullable|image|mimes:jpg,jpeg,png',
        ];

        $validator = Validator::make($data, $rules);
        if ($validator->fails()) {
            return $this->newResponse(false,$this->validationHandle($validator->messages()));
        }
        try {

            $customer =$request->user('customer');

            if($customer){
                $customer->update(['name'=>$request->name]);
            }
            if($customer && $request->hasFile('avatar')){
                $file=$request->file('avatar');

                $img = Image::make($file->getRealPath());
                $img=$img->resize('200','200');
                $extension =$file->getClientOriginalExtension();
                $path=public_path('uploads');
                if(!File::exists($path)) {
                    File::makeDirectory($path, 0776, true, true);
                    // path does not exist
                }
                $randomName=rand(0,100).time().'.'.$extension;
                $img->save(public_path('uploads/'.$randomName));
                $customer->avatar=$randomName;
                $customer->save();
            }
            $customer=CustomerResource::make($customer);
            return $this->newResponse(true,__('api.success_response'),'customer',$customer);

        } catch (Exception $e) {
            return $this->newResponse(false,__('api.fails_response'));
        }
//        catch (QueryException $e) {
//            return $this->newResponse(false,__('api.fails_response'));
//        }

    }
    public function login(Request $request)
    {
            $data = $request->only(['mobile','password','fcmToken']);
            $rules = [
                'mobile'   => ['required','regex:/^(05)(5|0|3|6|4|9|1|8|7)([0-9]{7})$/'],

                'password' => 'required|string|min:4',
//                'language' => 'in:ar,en'
            ];
            $validator = Validator::make($data, $rules);
            if ($validator->fails()) {
                return $this->newResponse(false,$this->validationHandle($validator->messages()));
            }
              $customer = Customer::where('mobile', $request->mobile)->first();
            if($customer && Hash::check($request->password,$customer->password)){
                if($customer->status==1){
                    $token= $this->createUserToken($customer);
                    $customer->access_token=$token;
                }
                if(!$customer->is_verified){
                    $otp=$this->sendOTP($customer);
                    $customer->otp_sms=$otp;
                }


                $customer = CustomerResource::make($customer);

                return $this->newResponse(true,__('api.success_response'),'customer',$customer);
            }
        return $this->newResponse(false,__('api.fails_response'), 'system error');
    }


    public function logout(Request $request)
    {
            $user = request()->user(); //or Auth::user()
//            if($user->tokens()->where('id', $user->currentAccessToken()->id)->delete()){
            if($user->tokens()->delete()){

            return $this->newResponse(true,__('api.success_response'));
            }
        return $this->newResponse(false,__('api.fails_response'));
    }

    public function forgetPassword(Request $request)
    {

        $data = $request->all();
        $rules = [
            'mobile' => ['required','exists:users,mobile,deleted_at,NULL','regex:/^(05)(5|0|3|6|4|9|1|8|7)([0-9]{7})$/'],
        ];
        $validator = Validator::make($data, $rules);
        if ($validator->fails()) {
            return $this->newResponse(false,$this->validationHandle($validator->messages()));
        }

        try {

                $result = Customer::where('mobile',$request->mobile)->first();


                // send sms
                $otp= $this->sendOTP($result);;
                // return userId to use in reset password
                return $this->newResponse(true,__('api.verification_otp_code_sent'),'customer_id',$result->id);
        } catch (Exception $e) {
            Log::info('Sent reset password code failed: '.$e->getMessage());
            return $this->newResponse(false,__('api.fails_response'));
        }

    }
    public function customerSendOtp(Request $request){
        $user=$request->user();
        if($user){
            $otp= $this->sendOTP($user);
            return $this->newResponse(true,__('api.success_response'),'',[],[
                'otp_code'=>$otp
            ]);
        }
        return $this->newResponse(false,__('api.fails_response'));

    }
    public function sendOtpToCustomer(Request $request){
        $data = $request->only(['mobile']);
        $rules = [
            'mobile'   => ['required','regex:/^(05)(5|0|3|6|4|9|1|8|7)([0-9]{7})$/'],
//                'language' => 'in:ar,en'
        ];
        $validator = Validator::make($data, $rules);
        if ($validator->fails()) {
            return $this->newResponse(false,$this->validationHandle($validator->messages()));
        }
        $user=Customer::where('mobile',$request->mobile)->first();
        if($user){
            $otp= $this->sendOTP($user);
            return $this->newResponse(true,__('api.success_response'),'',[],[
                'otp_code'=>$otp
            ]);
        }
        return $this->newResponse(false,__('api.fails_response'));

    }

    public function updateFcm(Request $request)
    {
        $data = $request->only(['fcm_token']);
        $rules = [
            'fcm_token' => 'required',
        ];
        $validator = Validator::make($data, $rules);
        if ($validator->fails()) {
            return $this->newResponse(false,$this->validationHandle($validator->messages()));
        }

        $user=$request->user();
        if($user){
            if($user->update(['fcmToken'=>$request->fcm_token])){
               return $this->newResponse(true,__('api.success_response'));
            }
            return $this->newResponse(false,__('api.fails_response'));
        }
        return $this->newResponse(false,__('api.fails_response'));
    }

    public function checkOtp(Request $request)
    {
        $data = $request->only(['customer_id','otp']);
        $rules = [
            'customer_id' => 'required|numeric|exists:users,id,deleted_at,NULL',
            'otp' => 'required',
        ];
        $validator = Validator::make($data, $rules);
        if ($validator->fails()) {
            return $this->newResponse(false,$this->validationHandle($validator->messages()));
        }
        try {

                $user = Customer::find($request->customer_id);

                $last_otp_sms=Sms::where('userId',$request->customer_id)->where('status',3)->latest('created_at')->first();

                if($last_otp_sms){
                    if($last_otp_sms->otp == $request->otp){
                        if($last_otp_sms->created_at <= Carbon::now()->subMinutes(2)->toDateTimeString()){
                            return $this->newResponse(false,'الرمز المدخل انتهى, يرجى اعادة تسجيل الدخول مرة أخرى');
                        }
                        $last_otp_sms->update(['status' => 1]);
                        $user->status=1;
                        $user->mobile_verified_at=Carbon::now();
                        $user->save();
//                        $user->update(['status' => 1,'mobile_verified_at'=>Carbon::now()]);
                    }else{
                        return $this->newResponse(false,__('api.not_valid_otp'));
                    }
                }else{
                    return $this->newResponse(false,__('api.not_valid_otp'));
                }
                // replace guest cart (fcmToken with user Id)
//                // move guest cart to user cart
//                $result = Cart::where('userId',$user->fcmToken)
//                    ->update(['userId' => $user->id]);
                $token=$this->createUserToken($user);
                $user->access_token=$token;
                $customer=CustomerResource::make($user);

                return $this->newResponse(true,__('api.success_verify_otp'),'customer',$customer);

        } catch (Exception $e) {
            return $this->newResponse(false,__('api.fails_response'));
        }

    }
    public function resetPassword(Request $request)
    {
        $data = $request->only(['customer_id','password','password_confirmation']);
        $rules = [
            'customer_id' => 'required|numeric|exists:users,id,deleted_at,NULL',
            'password' => 'required|min:6|max:25|confirmed',
        ];
        $validator = Validator::make($data, $rules);
        if ($validator->fails()) {
            return $this->newResponse(false,$this->validationHandle($validator->messages()));
        }
        try {

            $customer = Customer::find($request->customer_id);
            
            $last_otp_sms=Sms::where('userId',$request->customer_id)->where('status',3)->latest('created_at')->first();

            if($last_otp_sms){
                if($last_otp_sms->otp == $request->otp){
                    if($last_otp_sms->created_at <= Carbon::now()->subMinutes(2)->toDateTimeString()){
                        return $this->newResponse(false,'الرمز المدخل انتهى, يرجى اعادة تسجيل الدخول مرة أخرى');
                    }
                    $last_otp_sms->update(['status' => 1]);
                    $user->status=1;
                    $user->mobile_verified_at=Carbon::now();
                    $user->save();
//                        $user->update(['status' => 1,'mobile_verified_at'=>Carbon::now()]);
                }else{
                    return $this->newResponse(false,__('api.not_valid_otp'));
                }
            }else{
                return $this->newResponse(false,__('api.not_valid_otp'));
            }









            // update user status
            $customer->status=1;
            $customer->password=$request->password;
            $customer->save();

//                $customer->update(['status' => 1,'password'=>Hash::make($request->password)]);
            if($customer->fcmToken){
                $this->sendNotification($customer->fcmToken,'App\Notifications\PasswordChanged',$customer->language);
            }
            $token= $this->createUserToken($customer);
            $customer->access_token=$token;
            $customer = CustomerResource::make($customer);
            return $this->newResponse(true,__('api.password_changed_successfully'),'customer',$customer);

        } catch (Exception $e) {
            return $this->newResponse(false,__('api.fails_response'));
        }

    }


}
