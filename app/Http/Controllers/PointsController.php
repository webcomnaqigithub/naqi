<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Points;
use App\Models\Setting;
use App\Models\User;
use Illuminate\Support\Facades\Validator;
use DB;
use Log;
class PointsController extends Controller
{
    public function agentPoints(Request $request)
    {

        try {
            $data = $request->only(['agentId','fromDate','toDate','type']);
            $rules = [
                // 'ids' => 'required',
                // 'agentId' => 'required|numeric',
            ];

            $validator = Validator::make($data, $rules);
            if ($validator->fails()) {
                return $this->response(false,$this->validationHandle($validator->messages()));
            } else {

                $point = Points::leftJoin('agents','agents.id','=','points.agentId');
                if($request->agentId != null){
                    $point =$point->where('points.agentId',$request->agentId);
                }
                if($request->type != null){
                    $point =$point->where('type',$request->type);
                }
                if($request->fromDate != null  && $request->toDate != null){


                    $request->toDate = $this->transformArabicNumbers($request->toDate);
                    $request->fromDate = $this->transformArabicNumbers($request->fromDate);
                    $fromDate= $request->fromDate;
                    $toDate = $request->toDate.' 23:59:59';

                    $point =  $point->whereBetween('created_at', array($fromDate, $toDate));
                }
                $point=$point->select(DB::raw('name, type, sum(points) as sum' ))
                // ->where('type','bonus')
                ->groupBy('name','type')->get();

                if($point->sum == null){
                    $point->sum = 0;
                }


                $records = $point->groupBy('name');

                $results = array();
                foreach ($records as $key => $value) {

                    $bonus = 0;
                    if($value->where('type','bonus')->first() != null){
                        $bonus = $value->where('type','bonus')->first()->sum;
                    }
                    $discount = 0;
                    if($value->where('type','discount')->first() != null){
                        $discount = $value->where('type','discount')->first()->sum;
                    }
                    if($key == ''){
                        $key = 'اشتراكات';
                        $bonus = $value->where('type','subscription')->first()->sum;
                    }
                    $results [] = [
                        'agent' => $key,
                        'bonus' => $bonus,
                        'discount' => $discount,
                        'difference' => $bonus - $discount,
                    ];

                }
                return $this->response(true,'success',$results);

            }
        } catch (Exception $e) {
            return $this->response(false,'system error');
        }
    }
    public function updateClientPoint(Request $request){
        $data = $request->only(['id','points']);
        $rules = [
             'id' => 'required|exists:points,id',
             'points' => 'required|numeric',
        ];

        $validator = Validator::make($data, $rules);
        if ($validator->fails()) {
            return $this->newResponse(false,$this->validationHandle($validator->messages()));
        }
        try{
            $point=Points::find($request->id);
            if($point){
                $point->points=$request->points;
                $point->save();
                return $this->newResponse(true,__('api.success_response'));
            }
            return $this->newResponse(false,__('api.fails_response'));
        }catch (\Exception $e){
            Log::info($e->getMessage());
            return $this->newResponse(false,__('api.fails_response'));
        }
    }
    public function list(Request $request)
    {

        try {
            $data = $request->only(['agentId','fromDate','toDate']);
            $rules = [
                // 'ids' => 'required',
                // 'agentId' => 'required|numeric',
            ];

            $validator = Validator::make($data, $rules);
            if ($validator->fails()) {
                return $this->response(false,$this->validationHandle($validator->messages()));
            } else {

                $point = Points::leftJoin('agents','agents.id','=','points.agentId')
                ->leftJoin('users','users.id','=','points.clientId')
                ->leftJoin('delegators','delegators.id','=','points.delegatorId')
                ->select('agents.name as agentName','users.mobile as clientMobile','users.name as clientName','points.*','delegators.name as delegatorName')
                ->orderBy('points.created_at','desc');

                if($request->agentId != null){
                    $point =$point->where('points.agentId',$request->agentId);
                }

                if($request->fromDate != null  && $request->toDate != null){


                    $request->toDate = $this->transformArabicNumbers($request->toDate);
                    $request->fromDate = $this->transformArabicNumbers($request->fromDate);
                    $fromDate= $request->fromDate;
                    $toDate = $request->toDate.' 23:59:59';

                    $point =  $point->whereBetween('created_at', array($fromDate, $toDate));
                }


                $point =  $point->paginate(1000)->items();

                foreach ($point as $value) {
                    if($value->type == 'discount'){
                        $value->points = -1 * $value->points;
                    }
                    if($value->agentName == null){
                        $value->agentName = 'اشتراكات';
                    }
                }

                return $this->response(true,'success',$point);

            }
        } catch (Exception $e) {
            return $this->response(false,'system error');
        }
    }

    public function apply(Request $request)
    {
        try {
            $data = $request->only(['points','amount','userId']);
            $rules = [
                'userId' => 'required',
                'points' => 'required',
                'amount' => 'required',
            ];
            $validator = Validator::make($data, $rules);
            if ($validator->fails()) {
                return $this->response(false,$this->validationHandle($validator->messages()));
            } else {

                $user= User::find($request->userId);
                if($user == null){

                    return $this->response(false,'user not found');
                }


                if($request->points != null){
                    $request->points = $this->convert($request->points);
                    $min_points_to_replace = Setting::where('name','min_points_to_replace')->first()->value;
                    if($request->points < $min_points_to_replace){
                        if($user->language == 'en'){
                            return $this->response(false,'you can replace '.$min_points_to_replace.' points atleast');
                        } else {
                            return $this->response(false,' عفوا لا تستطيع استبدال اقل من ' .$min_points_to_replace. ' نقطة ' );
                        }
                    }
                    if($request->points % 10 != 0)
                    {
                        if($user->language == 'ar'){
                            return $this->response(false,'عدد النقاط یجب ان یكون من مضاعفات الرقم ١٠');
                        } else {
                            return $this->response(false,'The number of points must be a multiple of 10');
                        }
                    }

                }


                if($request->points > 0){
                    // check user point

                    $user->points =  $this->getPointsOfUser($user);
                    Log::info($request->points);
                    Log::info($user->points);
                    if($user->points < $request->points){
                        if($user->language == 'ar')
                            return $this->response(false,'رصيد النقاط غير كافي');

                        return $this->response(false,"You don't have enough points");
                    }
                    $replace_points	 = Setting::where('name','replace_points')->first()->value;
                    $pointsDiscount = ($request->points/$replace_points);
                    return $this->response(true,'success',$pointsDiscount);
                }else{
                    return $this->response(true,'success',0);
                }
            }

        } catch (Exception $e) {
            return $this->response(false,'system error');
        }

    }


    //delete
    public function delete($id)
    {

        try {
            $record = Points::find($id);
            if($record == null){
                return $this->response(false,'id is not found');
            }
            if($record->delete())
            {
                return $this->response(true,'success');

            }else {
                return $this->response(false,'failed');
            }
        } catch (Exception $e) {
            return $this->response(false,'system error');
        }


    }

    public function show($id)
    {

        try {


            $point = Points::leftJoin('agents','agents.id','=','points.agentId')
            ->leftJoin('users','users.id','=','points.clientId')
            ->leftJoin('delegators','delegators.id','=','points.delegatorId')
            ->select('agents.name as agentName','users.mobile as clientMobile','users.name as clientName','points.*','delegators.name as delegatorName')
            ->orderBy('points.created_at','desc')->where('points.id',$id)->first();


            if($point->type == 'discount'){
                $point->points = -1 * $point->points;
            }
            if($point->agentName == null){
                $point->agentName = 'اشتراكات';
            }

            return $this->response(true,'success',$point);

        } catch (Exception $e) {
            return $this->response(false,'system error');
        }


    }


    public function update(Request $request)
    {

        try {
            $data = $request->only(['points','id']);
            $rules = [
                'id' => 'required',
                'points' => 'required',
            ];
            $validator = Validator::make($data, $rules);
            if ($validator->fails()) {
                return $this->response(false,$this->validationHandle($validator->messages()));
            } else {

                $id = $request->id;

                $record = Points::find($id);
                if($record == null){
                    return $this->response(false,'Points not found');
                }


                if($request->points != null){
                    $request->points = $this->convert($request->points);
                    // dd($request->points);
                    // $min_points_to_replace = Setting::where('name','min_points_to_replace')->first()->value;
                    // if($request->points < $min_points_to_replace){
                    //     if($record->user->language == 'en'){
                    //         return $this->response(false,'you can replace '.$min_points_to_replace.' points atleast');
                    //     } else {
                    //         return $this->response(false,' عفوا لا تستطيع استبدال اقل من ' .$min_points_to_replace. ' نقطة ' );
                    //     }
                    // }
                    if($request->points % 10 != 0)
                    {
                        return $this->response(false,'عدد النقاط یجب ان یكون من مضاعفات الرقم ١٠');

                    }

                    $record->points = $request->points;
                    $record->created_at = date('Y-m-d H:m:s');
                    $record->save();

                    $user= User::find($record->clientId);
                    $user->points =  $this->getPointsOfUser($user);
                    $user->save();

                    return $this->response(true,'success','تم تعديل النقاط');
                }else{
                    return $this->response(true,'success',0);
                }
            }

        } catch (Exception $e) {
            return $this->response(false,'system error');
        }

    }


}
