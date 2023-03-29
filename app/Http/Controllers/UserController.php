<?php

namespace App\Http\Controllers;

use App\Http\Resources\CustomerResource;
use App\Models\Customer;
use Illuminate\Support\Arr;
use Illuminate\Support\Str;
use Illuminate\Http\Request;
use App\Models\User;
use App\Models\Setting;
use App\Models\Points;
use App\Models\ReferralProgram;
use Illuminate\Support\Facades\Validator;
use Grimzy\LaravelMysqlSpatial\Types\Polygon;
use Grimzy\LaravelMysqlSpatial\Types\LineString;
use Grimzy\LaravelMysqlSpatial\Types\Point;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Auth;
use Illuminate\Validation\Rule;
use Intervention\Image\Facades\Image;
use Tymon\JWTAuth\Facades\JWTAuth;
use App\Models\Sms;
use App\Models\Agent;
use App\Models\Address;
use App\Models\Delegator;
use App\Models\Industry;
use App\Models\Yamamah;
use App\Models\Cart;
use Exception;
use Carbon\Carbon;

use GuzzleHttp;
use App\Jobs\SendSms;
use App\Http\Resources\UserResource;
use App\Http\Resources\UserCollection;
use DB;
class UserController extends Controller
{
    //details
    public function details($id)
    {

        try {
            $record = Customer::find($id);
            if($record == null){
                return $this->response(false,'id is not found');
            }
            $customer = CustomerResource::make($record,false,true);

            return $this->response(true,'success',$customer);
        } catch (Exception $e) {
            return $this->response(false,'system error');
        }


    }

    //delete
    public function delete($id)
    {



            $user = Customer::find($id);
            if($user) {
                if ($user->delete()) {
                    return $this->newResponse(true, __('api.success_response'));
                }
            }

            return $this->newResponse(false,__('api.fails_response'));
    }

    //list all
    public function list(Request $request)
    {
        $users=new Customer();
        $customer_counts=$users->count();
        $customers= new UserCollection($users->paginate($request->get('perPage','20')));
        return $this->newResponse(true,__('api.success_response'),'',[],[
            'total_customers'=>$customer_counts,
            'customers'=>$customers,

        ]);

    }
    public function search(Request $request)
    {

                $users = new Customer();
                $paginationParams = [];

                if($request->id)
                {
                    $users = $users->where('id',$request->id);
                    $paginationParams['id'] = $request->id;
                }
                if($request->mobile)
                {
                    $users = $users->where('mobile',$request->mobile);
                    $paginationParams['mobile'] = $request->mobile;

                }
                if($request->name)
                {
                    $name = utf8_decode($request->name);
                    if(str_contains($name,'?')){
                        $name = $request->name;
                    }
                    $users = $users->where('name','like','%'.$name.'%');
                    $paginationParams['name'] = utf8_encode($request->name);
                }
                if($request->from != null && $request->to != null)
                {
                    $users = $users->whereBetween('created_at', [$request->from,$request->to]) ;
                    $paginationParams['from'] = $request->from;
                    $paginationParams['to'] = $request->to;
                }

                if($request->agentId){

                    $users = $users->whereIn('agent_id',$request->agentId);
                    $paginationParams['agent_id'] = $request->agentId;
                }
                 $customer_counts=$users->count();
                $users = $users->paginate($request->get('perPage','20'));
                if(!empty($paginationParams)){
                    $users->appends($paginationParams);
                }

                $customers= new UserCollection($users);
                return $this->newResponse(true,__('api.success_response'),'',[],[
                    'total_customers'=>$customer_counts,
                    'customers'=>$customers,

                ]);
    }

    public function getUserPoints(Request $request)
    {

        try {
            $data = $request->all();
            $rules = [
                // 'userId'   => 'required',
            ];
            $validator = Validator::make($data, $rules);
            if ($validator->fails()) {
                return $this->response(false,$this->validationHandle($validator->messages()));
            } else {
                $user = Auth::user();
                // return $user;

                // $user = User::where('id', $request->get('userId'))->where('status', 1)->first();
                if($user == null)
                {
                    return response()->json(['status' => false,'message' => 'Unauthorized','data' => new Exception], 200);
                }
                $user->subscriptionCount =  ReferralProgram::where('fromUser',$user->id)->count();

                $replacePoints = Setting::where('name','replace_points')->first()->value;
                $minPoints = Setting::where('name','min_points_to_replace')->first()->value;
                $user->replacePoints=$replacePoints;
                $user->minPoints=$minPoints;

                // get points from points table
                $points = Points::where('clientId',$user->id)
                ->select(DB::raw('type, sum(points) as sum' ))
                ->whereDate('created_at', '>', \Carbon\Carbon::now()->subMonth(4))
                ->groupBy('type')->get();
                if(count($points) > 0){
                    $user->points = 0;
                    foreach ($points as $point) {
                        if($point->type == 'bonus'){
                            $user->points = $user->points + $point->sum;
                        }
                        if($point->type == 'discount'){
                            $user->points = $user->points - $point->sum;
                        }
                    }
                    if($user->points<0){
                        $user->points = 0;
                    }

                } else{
                    $user->points = 0;
                }
                return $this->response(true,'success',$user);
            }
        } catch (Exception $e) {
            return $this->response(false,'system error');
        }

    }

    public function login(Request $request)
    {
                // User::where('id','189743')->update(['status' => 1,'password'=>bcrypt('password')]);


        try {
            $data = $request->only(['mobile','password','lat','lng','language','fcmToken']);
            $rules = [
                'mobile'   => 'required',
                'lat'   => 'required',
                'lng'   => 'required',
                'password' => 'required|min:4',
                'language' => 'in:ar,en'
            ];
            $validator = Validator::make($data, $rules);
            if ($validator->fails()) {
                return $this->response(false,$this->validationHandle($validator->messages()));
            } else {
                $credentials = request(['mobile', 'password']);
                if (! $token = auth()->guard('api')->check($credentials)) {
                    if($request->language == 'ar')
                        return response()->json(['status' => false,'message' => 'رقم الجوال  أو كلمة المرور غير صحيحة','data' => new Exception], 200);
                    return response()->json(['status' => false,'message' => 'invalid mobile number or password','data' => new Exception], 200);
                }
                $user = User::where('mobile', $request->get('mobile'))->where('status', 1)->first();
                if($user == null)
                {
                    if($request->language == 'ar')
                        return response()->json(['status' => false,'message' => 'رقم الجوال  أو كلمة المرور غير صحيحة','data' => new Exception], 200);

                    return response()->json(['status' => false,'message' => 'invalid username or password','data' => new Exception], 200);
                }
                $user->language=$request->language;
                $user->fcmToken=$request->fcmToken;

                $user->save();
                $user->api_token = $token;

                $user->region_name = $user->region->arabicName ?? null;
                $user->city_name = $user->city->arabicName ?? null;
                $user->district_name = $user->district->arabicName ?? null;

                unset($user->region);
                unset($user->city);
                unset($user->district);

                // replace guest cart (fcmToken with user Id)
                // move guest cart to user cart
                $result = Cart::where('userId',$request->fcmToken)
                ->update(['userId' => $user->id]);
                $this->subscribeTopic($request->fcmToken,"clients");
                return $this->response(true,'success',$user);
            }
        } catch (Exception $e) {
            return $this->response(false,$e->getMessage());
        }

    }

    //update status
    public function changeStatus(Request $request)
    {

        try {
            $data = $request->only(['status','ids']);
            $rules = [
                'ids' => 'required',
                'status' => 'required|numeric',
            ];

            $validator = Validator::make($data, $rules);
            if ($validator->fails()) {
                return $this->response(false,$this->validationHandle($validator->messages()));
            } else {
                $result = User::whereIn('id', $request->ids)
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

    public function addFriendMobile(Request $request)
    {
        try{

            $data = $request->only(['id','lat','lng','friendMobile','mobile']);
            $rules = [
                'id' => 'required',
                'mobile' => 'required',
                'lat' => 'required',
                'lng' => 'required',
                'friendMobile' => 'different:mobile',
            ];

            $validator = Validator::make($data, $rules);
            if ($validator->fails()) {
                return $this->response(false,$this->validationHandle($validator->messages()));
            } else {

                $user =  User::where('mobile',$data['mobile'])->where('id',$data['id'])->first();
                if($user == null){
                    return $this->response(false,'user infomation is not valid');
                }
                $friendUser =  User::where('mobile',$data['friendMobile'])->first();
                if($friendUser == null){
                    return $this->response(false,'friend mobile is not found');
                } else{

                    $count = ReferralProgram::where('fromUser',$friendUser->id)->where('toUser',$request->id)->count();
                    if($count){
                        if($user->language == 'en')
                            return $this->response(false,'you added this friend before');
                        return $this->response(false,'تم إضافة هذا الصديق من قبل');
                    }
                    ReferralProgram::create(['fromUser'=>$friendUser->id,'toUser'=>$request->id]);

                    // increase old user with share points


                    // $pointsRecord->clientId = $newOrder->userId;


                    $points = Setting::get('share_points');
                    $friendUser->points = $friendUser->points+$points->value;
                    $friendUser->save();


                    $pointsRecord = new Points();
                    $pointsRecord->type = 'subscription';
                    $pointsRecord->clientId = $friendUser->id;
                    $pointsRecord->points = $points->value;
                    $pointsRecord->save();




                    $results = $this->getProductsInMainScreen($request,$user->id,'home');
                    return $this->response(true,'success');
                }
            }

        } catch (Exception $e) {
            return $this->response(false,'system error');
        }

    }

    public function addSbscriptionPoints(Request $request)
    {
        try{

            $data = $request->only(['id','points']);
            $rules = [
                'id' => 'required',
                'points' => 'required',
            ];

            $validator = Validator::make($data, $rules);
            if ($validator->fails()) {
                return $this->response(false,$this->validationHandle($validator->messages()));
            } else {


                $points = Setting::get('share_points');

                $pointsRecord = new Points();
                $pointsRecord->type = 'subscription';
                $pointsRecord->clientId = $request->id;
                $pointsRecord->points = $request->points;
                $pointsRecord->save();

                return $this->response(true,'success');
            }

        } catch (Exception $e) {
            return $e;
            return $this->response(false,'system error');
        }

    }
    public function register(Request $request)
    {
        $data = $request->only([
            'name','mobile','password','password_confirmation',
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
                'regex:/^(05)(5|0|3|6|4|9|1|8|7)([0-9]{7})$/'
            ],
            'password' => 'required|min:4|max:25|confirmed',
//            'avatar' => 'nullable|image|mimes:jpg,jpeg,png',
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
                'region_id',
                'city_id',
                'district_id',
                'lat',
                'lng',
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

//            if($customer && $request->hasFile('avatar')){
//                $file=$request->file('avatar');
//
//                $img = Image::make($file->getRealPath());
//                $img=$img->resize('200','200');
//                $extension =$file->getClientOriginalExtension();
//                $path=public_path('uploads');
//                if(!File::exists($path)) {
//                    File::makeDirectory($path, 0776, true, true);
//                    // path does not exist
//                }
//                $randomName=rand(0,100).time().'.'.$extension;
//                $img->save(public_path('uploads/'.$randomName));
//                $customer->avatar=$randomName;
//                $customer->save();
//            }

            // send sms
            $otp= $this->sendOTP($customer);
            $customer->otp_sms=$otp;
            $customer=CustomerResource::make($customer);

            return $this->newResponse(true,__('api.register_successfully'),'customer',$customer);


        } catch (Exception $e) {
//            return $e->getMessage();
            return $this->newResponse(false,__('api.fails_response'));
        }

    }
    public function create(Request $request)
    {
        $data = $request->only(['name','lat','lng','mobile','password','fcmToken','friendMobile','language' ,'city_id','district_id','region_id']);
        $rules = [
            'name' => 'required',
            'lat' => 'required',
            'lng' => 'required',
            'mobile' => ['required',  Rule::unique('users')->whereNull('deleted_at'),
                'regex:/^(05)(5|0|3|6|4|9|1|8|7)([0-9]{7})$/'
            ],
            'password' => 'required',
            'fcmToken' => 'required',
            'region_id' => 'required',
            'city_id' => 'required',
            'district_id' => 'required',
            'friendMobile' => 'different:mobile',
            'language' => 'in:ar,en'

        ];

        $validator = Validator::make($data, $rules);
        if ($validator->fails()) {
            return $this->response(false,$this->validationHandle($validator->messages()));
        }
        try {
                        $newRecord =  User::create($data);
                        $address_data['lat']=$data['lat'];
                        $address_data['lng']=$data['lng'];
                        $address_data['region_id']=$data['region_id'];
                        $address_data['city_id']=$data['city_id'];
                        $address_data['district_id']=$data['district_id'];
                        $address_data['default']=true;
                        $address_data['type']='home';
                        $address_data['name']='العنوان الافتراضي';
                        $newRecord->addresses()->create($address_data);
                    return $this->response(true,'success',$newRecord);


        } catch (Exception $e) {
            return $this->response(false,$e->getMessage());

        } catch (QueryException $e) {
            return $this->response(false,'system error');
        }

    }
    public function logout(Request $request)
    {
        $data = $request->only(['userId','fcmToken','type']);
        $rules = [
            'userId' => 'required',
            'fcmToken' => 'required',
            'type' => 'required|in:client,agent,delegator,industry',

        ];
        $validator = Validator::make($data, $rules);
        if ($validator->fails()) {
            return $this->response(false,$this->validationHandle($validator->messages()));
        } else {

            // remove fcmToken if user logout

            switch ($request->type) {
                case 'client':
                    // $result = User::where('id', $request->userId)
                    // ->update(
                    //     [
                    //         'fcmToken' => '',
                    //     ]);
                    // if($result == 0) // no update
                    // {
                    //     return $this->response(false,'not valid id');
                    // }
                    return $this->response(true,'success');
                case 'agent':
                        $result = Agent::where('id', $request->userId)
                        ->update(
                            [
                                'fcmToken' => '',
                            ]);
                        if($result == 0) // no update
                        {
                            return $this->response(false,'not valid id');
                        }
                    return $this->response(true,'success');
                case 'delegator':
                        $result = Delegator::where('id', $request->userId)
                        ->update(
                            [
                                'fcmToken' => '',
                            ]);
                        if($result == 0) // no update
                        {
                            return $this->response(false,'not valid id');
                        }
                    return $this->response(true,'success');

                case 'industry':
                        $result = Industry::where('id', $request->userId)
                        ->update(
                            [
                                'fcmToken' => '',
                            ]);
                        if($result == 0) // no update
                        {
                            return $this->response(false,'not valid id');
                        }
                    return $this->response(true,'success');

                default:
                    # code...
                    break;
            }
            return $this->response(true,'success');
        }

    }

    public function checkUserToResetPassword(Request $request)
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
                $result = User::where('mobile',$request->mobile)->first();
                if($result == null)
                {
                    if($request->language !=null && $request->language == 'en')
                    {
                        return $this->response(false,'not valid information');
                    } else {
                        return $this->response(false,'رقم الجوال غير صحيح');
                    }                }
                // return $result;
                // send sms
                $otp= rand(1000, 9999);

                $this->sendSms('Your OTP is '.$otp,$request->mobile);
                $data = ['userId' => $result->id,'otp' => $otp,'status' => 2];
                $sms =  Sms::create($data);
                // return userId to use in reset password

                return $this->response(true,'success',$result->id);
            }
        } catch (Exception $e) {
            return $this->response(false,'system error');
        }

    }


    public function update(Request $request)
    {
        try {
            $data = $request->only(['id','name','mobile','status', 'lat', 'lng']);
            $rules = [
                'id' => 'required',
                'name' => 'required',
                'mobile' => 'required',
                'status' => 'required',
                'lat' => 'required',
                'lng' => 'required',
            ];

            $validator = Validator::make($data, $rules);
            if ($validator->fails()) {
                return $this->response(false,$this->validationHandle($validator->messages()));
            } else {

                    $oldRecord = Customer::where('id',$data['id'])->first();
                    if($oldRecord  == null)
                    {
                        return $this->response(false,'id is not valid');
                    }

                    $oldRecord = Customer::where('id','<>',$data['id'])->where('mobile',$data['mobile'])->first();
                    if($oldRecord  != null)
                    {
                        return $this->response(false,'mobile already existed');
                    }
                    $result = Customer::where('id', $request->id)
                    ->update(
                        [
                            'name' => $request->name,
//                            'city_id' => $request->city_id,
//                            'district_id' => $request->district_id,
//                            'region_id' => $request->region_id,
                            'mobile' => $request->mobile,
//                            'lat' => $request->lat,
//                            'lng' => $request->lng,
                            'status' => $request->status,
                        ]);

                    $user = Address::where('userId', $data['id'])->where('default', 1)->first();
                    if($user !== null){
                        $user->update([
                            'lat' => $request->lat,
                            'lng' => $request->lng
                        ]);
                    }
                    
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
    public function updateNameAndMobile(Request $request)
    {
        try {
            $data = $request->only(['id','name','mobile']);
            $rules = [
                'id' => 'required',
                'name' => 'required',
                'mobile' => 'required',
            ];

            $validator = Validator::make($data, $rules);
            if ($validator->fails()) {
                return $this->response(false,$this->validationHandle($validator->messages()));
            } else {

                    $oldRecord = User::where('id',$data['id'])->first();
                    if($oldRecord  == null)
                    {
                        return $this->response(false,'id is not valid');
                    }

                    $oldRecord = User::where('id','<>',$data['id'])->where('mobile',$data['mobile'])->first();
                    if($oldRecord  != null)
                    {
                        return $this->response(false,'mobile already existed');
                    }
                    $result = User::where('id', $request->id)
                    ->update(
                        [
                            'name' => $request->name,
                            'mobile' => $request->mobile,
                        ]);
                    if($result == 0) // no update
                    {
                        return $this->response(false,'not valid id');
                    }
                    $newRecord = User::find($data['id']);

                    return $this->response(true,'success',$newRecord);
            }
        } catch (Exception $e) {
            return $this->response(false,'system error');
        }

    }
    public function updateLanguage(Request $request)
    {
        try {
            $data = $request->only(['id','language','type']);
            $rules = [
                'id' => 'required',
                'language' => 'required|in:en,ar',
                'type' => 'required|in:client,agent,delegator,industry',
            ];

            $validator = Validator::make($data, $rules);
            if ($validator->fails()) {
                return $this->response(false,$this->validationHandle($validator->messages()));
            } else {

                switch ($request->type) {
                    case 'client':
                        $result = User::where('id', $request->id)
                        ->update(
                            [
                                'language' => $request->language,
                            ]);
                        if($result == 0) // no update
                        {
                            return $this->response(false,'not valid id');
                        }
                        return $this->response(true,'success');
                    case 'agent':
                            $result = Agent::where('id', $request->id)
                            ->update(
                                [
                                    'language' => $request->language,
                                ]);
                            if($result == 0) // no update
                            {
                                return $this->response(false,'not valid id');
                            }
                        return $this->response(true,'success');
                    case 'delegator':
                            $result = Delegator::where('id', $request->id)
                            ->update(
                                [
                                    'language' => $request->language,
                                ]);
                            if($result == 0) // no update
                            {
                                return $this->response(false,'not valid id');
                            }
                        return $this->response(true,'success');

                    case 'industry':
                            $result = Industry::where('id', $request->id)
                            ->update(
                                [
                                    'language' => $request->language,
                                ]);
                            if($result == 0) // no update
                            {
                                return $this->response(false,'not valid id');
                            }
                        return $this->response(true,'success');

                    default:
                        # code...
                        break;
                }

            }
        } catch (Exception $e) {
            return $this->response(false,'system error');
        }

    }
    public function sendNotificationsToTopic(Request $request)
    {
        try {
            $data = $request->only(['topicName','title','body']);
            $rules = [
                'topicName' => 'required',
                'title' => 'required',
                'body' => 'required',
            ];

            $validator = Validator::make($data, $rules);
            if ($validator->fails()) {
                return $this->response(false,$this->validationHandle($validator->messages()));
            } else {
                $this->sendToTopic($request->topicName,$request->title,$request->body);
                return $this->response(true,'success');
            }
        } catch (Exception $e) {
            return $this->response(false,'system error');
        }
    }

    public function test(Request $request)
    {
                // $this->sendSms('Your OTP is '.'1234','0555371182');

    dd('asd');
        //$token,$type,$language
        $this->sendNotification('dJZm-Z7e1kp9h4O4QT6klI:APA91bHlOzWPvbZsgTGjRf_hkffntP7n79qle_5cmnhu7Z7Ff6STJOXFIG-EBbs3a8rSp5ChNQ8m-kyZQcVzISBa7EVXQ1X8FI0I5RuaJeIKMKKcC9sNZxn8Ao1ogW54tAvIWUApQRhx','App\Notifications\PointUsed','ar');
        return 'test';
    }

    public function getSearchResults(Request $request) {

        $data = $request->get('search');
        $results = User::where('mobile', 'like', "%{$data}%")
                         ->orWhere('name', 'like', "%{$data}%")
                         ->get();
        return $this->response(true,'success',$results);
    }

    public function searchInIndex(Request $request)
    {
        // MeiliSearch is typo-tolerant:
        $results = Address::search($request->search)->get();


        // $results = User::search('harry pottre')->first();
        // $results =Address::search('home')->paginate();
        // ->get();

        // 'name','userId','type','lat','lng','default'
        // $book = new Address();
        // $book->name = 'safwan';
        // $book->userId = 1;
        // $book->type = 'home';
        // $book->lat = 24.7406;
        // $book->lng = 46.56971;
        // $book->save();

        return $results ;
    }
}
