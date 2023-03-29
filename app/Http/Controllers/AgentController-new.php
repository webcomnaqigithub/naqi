<?php
namespace App\Http\Controllers;
use App\Models\AgentArea;
use App\Models\Delegator;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;
use Illuminate\Http\Request;
use App\Models\Agent;
use App\Models\City;
use App\Models\Region;
use Illuminate\Support\Facades\Validator;
use Grimzy\LaravelMysqlSpatial\Types\Polygon;
use Grimzy\LaravelMysqlSpatial\Types\LineString;
use Grimzy\LaravelMysqlSpatial\Types\Point;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Auth;
use Tymon\JWTAuth\Facades\JWTAuth;
use Notification;
use Log;
use Exception;
use App\Jobs\SendSms;
use App\Models\Industry;

class AgentController extends Controller
{
    //details
    public function details($id)
    {

        try {
            $record = Agent::find($id);
            if($record == null){
                return $this->response(false,'id is not found');
            }
            // $record =parent::convertPolygon($record);
            return $this->response(true,'success',$record);
        } catch (Exception $e) {
            return $this->response(false,$e->getMessage());//'system error'
        }
    }

    //delete
    public function delete($id)
    {

        try {

            $agent = Agent::find($id);
            $industry = Industry::where('mobile', $agent->mobile)->first();
            if($agent == null){
                return $this->response(false,'id is not found');
            }
            if($agent->delete())
            {
                $industry->delete();
                return $this->response(true,'success');
            }else {
                return $this->response(false,'failed');
            }
        } catch (Exception $e) {

            return $this->response(false,'system error');
        }
    }

    //list all
    public function list(Request $request)
    {


        try {
            $agents = Agent::with('areas')->get();//dd($agents);
            //            foreach($agents as $agent){
            //                $agent =parent::convertPolygon($agent);
            //            }
            //convertPolygon
            return $this->response(true,'success',$agents);
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
                $result = Agent::whereIn('id', $request->ids)
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

    public function search(Request $request)
    {
        try {
            $data = $request->only(['lat','lng']);
            $rules = [
                'lat' => 'required',
                'lng' => 'required',
            ];

            $validator = Validator::make($data, $rules);
            if ($validator->fails()) {
                return $this->response(false,$this->validationHandle($validator->messages()));
            } else {
                $agent = new Agent;
                $agent = $agent->search($request->lat, $request->lng);
                if($agent == null)
                    return $this->response(false,'location is not supported now');
                return $this->response(true,'success',$agent);
            }
        } catch (Exception $e) {
            return $this->response(false,'system error');
        }
    }

    public function testSearch(Request $request)
    {

        try {
            $data = $request->only(['lat','lng']);
            $rules = [
                'lat' => 'required',
                'lng' => 'required',
            ];

            $validator = Validator::make($data, $rules);
            if ($validator->fails()) {
                return $this->response(false,$this->validationHandle($validator->messages()));
            } else {
                $point =  new Point($request->lat, $request->lng);
                $agent = Agent::contains('area',$point)->where('status',1)
                ->orderBy('id','asc')->get();
                if($agent == null)
                    return null;
                return $agent;
            }
        } catch (Exception $e) {
            return $this->response(false,'system error');
        }


    }

    public function getAgent(Request $request)
    {
        try {
            $agent = Auth::user();

            return $this->response(true,'success',$agent);
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
                'password' => 'required|min:4',
                'fcmToken' => 'required',
                'language' => 'required|in:ar,en',
            ];
            $validator = Validator::make($data, $rules);
            if ($validator->fails()) {
                return $this->response(false,$this->validationHandle($validator->messages()));
            } else {
                $credentials = request(['mobile', 'password']);
                if (! $token = auth()->guard('agent')->attempt($credentials)) {
                    if($request->language == 'ar')
                        return response()->json(['status' => false,'message' => ' رقم الجوال أو كلمة المرور غير صحيحة','data' => new Exception], 200);
                    return response()->json(['status' => false,'message' => 'invalid mobile number or password','data' => new Exception], 200);
                }
                $agent = Agent::where('mobile', $request->get('mobile'))->whereIn('status', [1,3])->first();

                if($agent == null){
                    if($request->language == 'ar')
                        return response()->json(['status' => false,'message' => ' رقم الجوال أو كلمة المرور غير صحيحة','data' => new Exception], 200);
                    return response()->json(['status' => false,'message' => 'invalid mobile number or password','data' => new Exception], 200);
                }

                $agent->fcmToken = $request->fcmToken;
                $agent->language = $request->language;
                $agent->save();
                $agent->api_token = $token;


                // this will return industry_id to use it for admin panel in the permission scenario
                // and create new record for the old agent in the industry table (note: new agents was created industry records when create a new agent) 
                $industry_record = Industry::where('mobile', $request->mobile)->where('status', 3)->first();
                if($industry_record){
                    $agent['industry_id'] = $industry_record->id;
                }else{
                    $new_industry_record = Industry::create([
                        'name'   => $agent->name,
                        'mobile'   => $request->mobile,
                        'password' => '',
                        'fcmToken' => $request->fcmToken,
                        'language' => $request->language,
                        'status' => 3,
                        'isAdmin' => 5,
                    ]);
                    $agent['industry_id'] = $new_industry_record->id;
                }
                
                
                // $agent =parent::convertPolygon($agent); // commented 28/09/2022

                return $this->response(true,'success',$agent);
            }

        } catch (Exception $e) {
            return $this->response(false,$e->getMessage());
        }

    }

    public function create(Request $request)
    {
        $data = $request->only(['region','name','city',
            'mobile','password','minimum_cartons',
            'englishSuccessMsg','arabicSuccessMsg','areas']);
        $rules = [
            'name' => 'required',
            // 'region' => 'required',
            // 'city' => 'required',
            'areas' => 'required',
            'mobile' => 'required',
            // 'status' => 'required',
            'password' => 'required',
            'areas.*.area' => 'required',
            'areas.*.minimum_cartons' => 'required',
            // 'fcmToken' => 'required',
        ];
        $validator = Validator::make($data, $rules);
        if ($validator->fails()) {
            return $this->response(false,$this->validationHandle($validator->messages()));
        }
        try {

                DB::beginTransaction();

                // check region
//                $region=Region::find($data['region']);
//                if($region == null){
//                    return $this->response(false,'invalid region');
//                }

                // check city
//                $city=City::where('id',$data['city'])->where('region_id',$data['region'])->first();
//                if($city == null){
//                    return $this->response(false,'invalid city');
//                }
                $oldRecord = Agent::where('mobile',$data['mobile'])->first();
                if($oldRecord  != null)
                {
                    return $this->response(false,'mobile already existed');
                }

                $newRecord =  Agent::create($data);
                    if($newRecord) {
                        foreach ($data['areas'] as $area) {
                            $points=[];
                            $firstPoint=null;
                            foreach ($area['area'] as $key=>$point) {
                                if($key==0){
                                    $firstPoint = new Point($point[0], $point[1]);
                                }
                                $point1 = new Point($point[0], $point[1]);
                                $points[$key] = $point1;
                            }
                            array_push($points,$firstPoint);
                            $areaData['area']= new Polygon([new LineString($points)]);
                            $areaData['minimum_cartons']=$area['minimum_cartons'];
                            $areaData['agent_id']=$newRecord->id;
                            AgentArea::create($areaData);
                        }
                    }
                    $data['isAdmin'] = 5;
                    $data['status'] = 3;
                    $data['password'] = '';
                    Industry::create($data);
//                $token =  Auth::guard('agent')->login($newRecord);
//                $newRecord->api_token = $token;
            DB::commit();
                return $this->response(true,'success',$newRecord);

        } catch (Exception $e) {
            DB::rollBack();
            return $this->response(false,$e->getMessage());
        }
    }
    
    public function updateAgentArea(Request $request)
    {
        $data = $request->only(['agent_area_id','minimum_cartons','area']);
        $rules = [
            'agent_area_id' => 'required|exists:agent_areas,id',
            'minimum_cartons' => 'required',
            'area' => 'required',

        ];
        $validator = Validator::make($data, $rules);
        if ($validator->fails()) {
            return $this->response(false,$this->validationHandle($validator->messages()));
        }
        try {
            DB::beginTransaction();
                $agentArea=AgentArea::find($request->agent_area_id);
                if($agentArea){
                        $points=[];
                        $firstPoint=null;
                        foreach ($request->area as $key=>$point) {
                            if($key==0){
                                $firstPoint = new Point($point[0], $point[1]);
                            }
                            $point1 = new Point($point[0], $point[1]);
                            $points[$key] = $point1;
                        }
                        array_push($points,$firstPoint);
                        $areaData['area']= new Polygon([new LineString($points)]);


                    $areaData['minimum_cartons']=$request->minimum_cartons;
                    $agentArea->update($areaData);
                    DB::commit();
                 return $this->response(true,'success',__('api.success_response'));
                }else{
                    return $this->response(false,__('api.fails_response'));
                }

        }catch (\Exception $exception){
            DB::rollBack();
            return $this->response(false,$exception->getMessage());
        }

    }

    public function createNewAgentArea(Request $request)
    {
        $data = $request->only(['minimum_cartons','area', 'agent_id']);
        $rules = [
            'minimum_cartons' => 'required',
            'area' => 'required',
            'agent_id' => 'required|numeric'
        ];
        $validator = Validator::make($data, $rules);
        if ($validator->fails()) {
            return $this->response(false,$this->validationHandle($validator->messages()));
        }
        try {
            DB::beginTransaction();
                $points=[];
                $firstPoint=null;
                foreach ($request->area as $key=>$point) {
                    if($key==0){
                        $firstPoint = new Point($point[0], $point[1]);
                    }
                    $point1 = new Point($point[0], $point[1]);
                    $points[$key] = $point1;
                }
                array_push($points,$firstPoint);
                $areaData['area']= new Polygon([new LineString($points)]);

                $areaData['minimum_cartons']=$request->minimum_cartons;
                $areaData['agent_id'] = $request->agent_id;
                AgentArea::create($areaData);
            DB::commit();
            return $this->response(true,'success',__('api.success_response'));
        }catch (\Exception $exception){
            DB::rollBack();
            return $this->response(false,$exception->getMessage());
        }

    }
    
    public function deleteAgentArea(Request $request)
    {
        $data = $request->only(['agent_area_id']);
        $rules = [
            'agent_area_id' => 'required|exists:agent_areas,id',

        ];
        $validator = Validator::make($data, $rules);
        if ($validator->fails()) {
            return $this->response(false,$this->validationHandle($validator->messages()));
        }
        try {

            if(AgentArea::where('id',$request->agent_area_id)->delete()){
                return $this->response(true,'success',__('api.success_response'));
            }else{
                return $this->response(false,__('api.fails_response'));
            }
        }catch (\Exception $exception){
            return $this->response(false,$exception->getMessage());
        }

    }

    public function delegators(Request $request)
    {
        $data = $request->only(['agentsId']);
        $rules = [
            'agentsId' => 'required|min:1',
        ];
        $validator = Validator::make($data, $rules);
        if ($validator->fails()) {
            return $this->response(false,$this->validationHandle($validator->messages()));
        }
        try{
                $delegators=Delegator::whereIn('agentId',$request->agentsId)->get();
                return $this->response(true,__('api.success_response'),$delegators);
        }catch (Exception $exception){
            return $this->response(false,$exception->getMessage());
        }
    }

    public function update(Request $request)
    {
        $data = $request->only(['region','name','city', 'district_id','id',
            'mobile', 'minimum_cartons', 'areas',
            'englishSuccessMsg','arabicSuccessMsg']);
        $rules = [
            'id' => 'required|exists:agents,id',
            'name' => 'required',
            'region' => 'required',
            'city' => 'required',
            'district_id' => 'required',
            // 'areas' => 'required',
            'mobile' => 'required',
            // 'status' => 'required',
            // 'fcmToken' => 'required',
            'areas.*.area' => 'required',
            'areas.*.minimum_cartons' => 'required',
        ];
        $validator = Validator::make($data, $rules);
        if ($validator->fails()) {
            return $this->response(false,$this->validationHandle($validator->messages()));
        }
        try {
            DB::beginTransaction();
                $oldRecord = Agent::where('id',$data['id'])->first();
                if($oldRecord  == null)
                {
                    return $this->response(false,'id is not valid');
                }

                $oldRecord = Agent::where('id','<>',$data['id'])->where('mobile',$data['mobile'])->first();
                if($oldRecord  != null)
                {
                    return $this->response(false,'mobile already existed');
                }

            $result = Agent::where('id', $request->id)->update([
                        'englishSuccessMsg' => $request->englishSuccessMsg,
                        'arabicSuccessMsg' => $request->arabicSuccessMsg,
                        'name' => $request->name,
                        'region' => $request->region,
                        'city' => $request->city,
                        'mobile' => $request->mobile,
                        'district_id' => $request->district_id,
                        'status' => $request->status,
                    ]);

            DB::commit();
            return $this->response(true,'success');

        } catch (Exception $e) {
            DB::rollBack();
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
                    $result = Agent::where('id', $request->id)
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
                $agent = Agent::where('mobile',$request->mobile)->first();
                if($agent == null)
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

                $agent->otp =$otp;
                $agent->save();
                return $this->response(true,'success',$agent->id);
            }
        } catch (Exception $e) {

            return $this->response(false,'system error');
        }
    }

    public function checkAgentToResetPassword(Request $request)
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
                $result = Agent::where('mobile',$request->mobile)->first();
                if($result == null)
                {
                    if($request->language !=null && $request->language == 'en')
                    {
                        return $this->response(false,'not valid information');
                    } else {
                        return $this->response(false,'رقم الجوال غير صحيح');
                    }
                }

                // send sms
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

    public function changePassword(Request $request)
    {

        try {
            $data = $request->only(['agentId','otp','password']);
            $rules = [
                'agentId' => 'required|numeric',
                'otp' => 'required',
                'password' => 'required',
            ];
            $validator = Validator::make($data, $rules);
            if ($validator->fails()) {
                return $this->response(false,$this->validationHandle($validator->messages()));
            } else {
                // update user status
                $result = Agent::where('id',$request->agentId)->where('otp',$request->otp)
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
}
