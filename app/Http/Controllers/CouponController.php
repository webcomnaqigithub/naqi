<?php

namespace App\Http\Controllers;

use App\Http\Resources\CouponAgentResource;
use App\Models\Agent;
use App\Models\Coupon;
use App\Models\User;
use Illuminate\Support\Facades\Validator;
use Illuminate\Http\Request;
use Carbon\Carbon;

class CouponController extends Controller
{
    //list all
    public function list(Request $request)
    {
        try{
            $coupons=Coupon::orderBy('id','desc')->get();
            $coupons=CouponAgentResource::collection($coupons);
            return $this->newResponse(true,__('api.success_response'),'data',$coupons);

        }catch (\Exception $e){
            return $this->newResponse(false,$e->getMessage());
        }

    }

    //details
    public function details($id)
    {


        try {
            $record = Coupon::find($id);
            if($record == null){
                return $this->response(false,'id is not found');
            }
            return $this->response(true,'success',$record);
        } catch (Exception $e) {
            return $this->response(false,'system error');
        }

    }

    public function check(Request $request)
    {
        try {
            $data = $request->only(['code','amount','userId']);
            $rules = [
                'userId' => 'required',
                'code' => 'required',
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
                $record = Coupon::where('code',$request->code)->where('status',1)
                ->where('notBefore', '<',Carbon::today())
                ->where('notAfter', '>',Carbon::today())->get();

                if($record == null || count($record)==0){
                    if($user->language == 'ar')
                    {
                        return $this->response(false,'كود التفعيل غير صحيح');
                    } else {
                        return $this->response(false,'code not found');
                    }
                }
                $code = $record[0];
                if($code->minAmount > $request->amount)
                {
                    if($user->language == 'ar')
                    {
                        return $this->response(false,' لاستخدام الكود يجب أن يكون إجمالي الطلب على الأقل  '.$code->minAmount);
                    } else {
                        return $this->response(false,'to use this code, minimum mount should be '.$code->minAmount);
                    }
                }
                if($code->type == 'percentage')
                {
                    $code->value = $request->amount * $code->value;
                }
                return $this->response(true,'success',$code);
            }

        } catch (Exception $e) {
            return $this->response(false,'system error');
        }

    }


    //details by code
    public function detailsByCode(Request $request)
    {


        try {
            $data = $request->only(['code']);
            $rules = [
                'code' => 'required',
            ];
            $validator = Validator::make($data, $rules);
            if ($validator->fails()) {
                return $this->response(false,$this->validationHandle($validator->messages()));
            } else {
                $record = Coupon::where('code',$request->code)->where('status',1)->get();
                if($record == null){
                    return $this->response(false,'code is not found');
                }
                return $this->response(true,'success',$record);
            }

        } catch (Exception $e) {
            return $this->response(false,'system error');
        }

    }
    //delete
    public function delete($id)
    {

        try {
            $record = Coupon::find($id);
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
    //create
    public function create(Request $request)
    {
        try {
            $data = $request->only(['code','name','type','value','minAmount','notBefore','notAfter','status','agents','target_agent','is_used_one_time']);
            $rules = [
                'code' => 'required',
                'name' => 'required',
                'type' => 'required|in:percentage,flat',
                'value' => 'required',
                'minAmount' => 'required',
                'notBefore' => 'required',
                'notAfter' => 'required',

                'agents' => 'nullable',
                'target_agent' => 'required|in:all,custom',
                'status' => 'required|in:1,2',
            ];
            $validator = Validator::make($data, $rules);
            if ($validator->fails()) {
                return $this->response(false,$this->validationHandle($validator->messages()));
            } else {
                $data['used'] = false;
                if($data['type'] == 'percentage' && ($data['value'] > 1 || $data['value'] <0)){
                    return $this->response(false,'if type is percentage, value should be less than 1 and not negative');
                }
                $oldRecord =  Coupon::where('code',$data['code'])->first();
                if($oldRecord!=null){
                    return $this->response(false,'code is already used ');
                }
                $newRecord =  Coupon::create($data);
                $newRecord->agents()->attach($request->agents);
                $newRecord=CouponAgentResource::make($newRecord);
//
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
            $data = $request->only(['ids','status']);
            $rules = [
                'ids' => 'required',
                'status' => 'required|numeric',
            ];

            $validator = Validator::make($data, $rules);
            if ($validator->fails()) {
                return $this->response(false,$this->validationHandle($validator->messages()));
            } else {
                $result = Coupon::whereIn('id', $request->ids)
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

        $data = $request->only(['id','code','name','type','value','minAmount','used','notBefore','notAfter','status']);
        $rules = [
            'id' => 'required|numeric',
            'code' => 'required',
            'name' => 'required',
            'type' => 'required|in:percentage,flat',
            'value' => 'required',
            'minAmount' => 'required',
            // 'used' => 'required|in:1,0',
            'notBefore' => 'required',
            'notAfter' => 'required',
            'status' => 'required|in:1,2',
        ];

        $validator = Validator::make($data, $rules);
        if ($validator->fails()) {
            return $this->response(false,$this->validationHandle($validator->messages()));
        } else {
            if($data['type'] == 'percentage' && ($data['value'] > 1 || $data['value'] <0)){
                return $this->response(false,'if type is percentage, value should be less than 1 and not negative');
            }
            $result = Coupon::where('id', $request->id)
            ->update(
                [
                'name' => $request->name,
                'code' => $request->code,
                'value' => $request->value,
                'type' => $request->type,
                // 'used' => $request->used,
                'minAmount' => $request->minAmount,
                'notBefore' => $request->notBefore,
                'notAfter' => $request->notAfter,
                'status' => $request->status
                ]);
            $result->agents()->sync($request->agents);
            return $this->response(true,'success');
        }
        } catch (Exception $e) {
            return $this->response(false,'system error');
        }
    }
}
