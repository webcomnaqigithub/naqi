<?php

namespace App\Http\Controllers;

use App\Models\DeliveryFlatLocation;
use App\Models\OrderScheduleSlot;
use App\Models\TimeSlot;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;
use App\Models\Setting;
use App\Models\Agent;

class SettingController extends Controller
{




    public function orderScheduleSlotList(Request $request){
        try {
            return $this->response(true,__('api.success_response'),OrderScheduleSlot::all());
        } catch (Exception $e) {
            return $this->response(false,__('api.fails_response'));
        }
    }

    //list all
    public function list(Request $request)
    {
        try {
            return $this->response(true,'success',Setting::all());
        } catch (Exception $e) {
            return $this->response(false,'system error');
        }
    }

    //details
    public function details($id)
    {
        try {
            $record = Setting::find($id);
            // $record = Setting::get('order_points');
            if($record == null){
                return $this->response(false,'id is not found');
            }
            return $this->response(true,'success',$record);
        } catch (Exception $e) {
            return $this->response(false,'system error');
        }
    }

    public function checkQuantity(Request $request)
    {
        try {

            $data = $request->only(['agentId']);
            $rules = [
            ];

            $validator = Validator::make($data, $rules);
            if ($validator->fails()) {
                return $this->response(false,$this->validationHandle($validator->messages()));
            } else {
                if($request->agentId != null){

                    $agent = Agent::find($request->agentId);
                    if($agent == null){
                        return $this->response(false,'agent not found');
                    }
                    return $this->response(true,'success',$agent->minimum_cartons);
                } else{

                    $setting = Setting::where('name','minimum_amount')->first();
                    if($setting == null)
                    {
                        return $this->response(false,'settig not found');
                    }
                    else{
                        return $this->response(true,'success',$setting->value);
                    }

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
            $record = Setting::find($id);
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
            $data = $request->only(['name','value','description']);
            $rules = [
                'name' => 'required',
                'value' => 'required',
            ];

            $validator = Validator::make($data, $rules);
            if ($validator->fails()) {
                return $this->response(false,$this->validationHandle($validator->messages()));
            } else {
                $oldRecord =  Setting::withTrashed()->where('name',$data['name'])->first();
                if($oldRecord != null && $oldRecord->deleted_at != null){
                    $result = Setting::withTrashed()->where('id', $oldRecord->id)
                    ->update(
                        [
                        'name' => $request->name,
                        'value' => $request->value,
                        'description' => $request->description,
                        'deleted_at' => null
                        ]);

                    $oldRecord->value = $request->value;
                    return $this->response(true,'success',$oldRecord);

                } else {
                    if($oldRecord!=null){
                        return $this->response(false,'name of setting is already used ');
                    }
                    $newRecord =  Setting::create($data);
                    return $this->response(true,'success',$newRecord);
                }

            }
        } catch (Exception $e) {
            return $this->response(false,'system error');
        }

    }


    public function getAboutValue(){
        
        return $this->response(true,__('api.success_response'),[
            'about_en'=>Setting::where('name','about_en')->frist(),
            'about_ar'=>Setting::where('name','about_ar')->frist(),
        ]);
    }

    //update
    public function update(Request $request)
    {
        try {
            $data = $request->only(['id','name','value','description']);
            $rules = [
                'id' => 'required|numeric',
                'name' => 'required',
                'value' => 'required',
            ];

            $validator = Validator::make($data, $rules);
            if ($validator->fails()) {
                return $this->response(false,$this->validationHandle($validator->messages()));
            } else {
                $result = Setting::where('id', $request->id)
                ->update(
                    [
                    'name' => $request->name,
                    'description' => $request->description,
                    'value' => $request->value
                    ]);
                if($result ==0){
                    return $this->response(true,'no records to update');
                }
                return $this->response(true,'success');
            }
        } catch (Exception $e) {
            return $this->response(false,'system error');
        }
    }

     //update policy
     public function updatePointsPolicy(Request $request)
     {
         try {
             $data = $request->only(['arabicPolicy','englishPolicy']);
             $rules = [
                 'arabicPolicy' => 'required',
                 'englishPolicy' => 'required',
             ];

             $validator = Validator::make($data, $rules);
             if ($validator->fails()) {
                 return $this->response(false,$this->validationHandle($validator->messages()));
             } else {
                 $result = Setting::where('name', 'points_policy_en')
                 ->update(
                     [
                     'value' => $request->englishPolicy
                     ]);
                 $result = Setting::where('name', 'points_policy_ar')
                 ->update(
                     [
                     'value' => $request->arabicPolicy
                     ]);

                 return $this->response(true,'success');
             }
         } catch (Exception $e) {
             return $this->response(false,'system error');
         }
     }

     public function getPointsPolicy(Request $request)
     {
         try {
             $data = $request->only([]);
             $rules = [
             ];

             $validator = Validator::make($data, $rules);
             if ($validator->fails()) {
                 return $this->response(false,$this->validationHandle($validator->messages()));
             } else {

                $data = new Setting;

                $data->points_policy_en = Setting::where('name', 'points_policy_en')->first()->value;
                $data->points_policy_ar = Setting::where('name', 'points_policy_ar')->first()->value;

                 return $this->response(true,'success',$data );
             }
         } catch (Exception $e) {
             return $this->response(false,'system error');
         }
     }

    public function orderScheduleSlotsStatus(Request $request)
    {
        try {
            $data = $request->only(['ids','status']);
            $rules = [
                'ids' => 'required',
                'status' => 'required',
            ];

            $validator = Validator::make($data, $rules);
            if ($validator->fails()) {
                return $this->response(false,$this->validationHandle($validator->messages()));
            } else {
                $result = OrderScheduleSlot::whereIn('id', $request->ids)
                    ->update(
                        ['is_active' => $request->status]);
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
    public function orderScheduleSlotUpdate(Request $request){
        $data = $request->only(['id','title_ar','title_en']);
        $rules = [
            'id' => 'required',
            'title_ar' => 'required',
            'title_en' => 'required',
//            'is_active' => 'required',
        ];

        $validator = Validator::make($data, $rules);
        if ($validator->fails()) {
            return $this->newResponse(false,$this->validationHandle($validator->messages()));
        }

        try {
            $orderScheduleSlot=OrderScheduleSlot::find($request->id);
            $orderScheduleSlot= $orderScheduleSlot->update([
                'title_ar'=>$request->title_ar,
                'title_en'=>$request->title_en,
            ]);
            if($orderScheduleSlot){
                return $this->newResponse(true,__('api.success_response'));
            }else{
                return $this->newResponse(false,__('api.fails_response'));
            }

        } catch (\Exception $e) {
//            \Log::info('create customer address error: '.$e->getMessage());
            return $this->newResponse(false,__('api.fails_response'));
        }


    }
}
