<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Resources\DeliveryFlatLocation;
use App\Http\Resources\DFL_WithoutAgents;
use App\Http\Resources\OrderScheduleSlotResource;
use App\Http\Resources\OrderTimeSlotResource;
use App\Http\Resources\OrderTimeSlotWAgentResource;
use App\Http\Resources\PaymentTypeResource;
use App\Models\Agent;
use App\Models\AgentArea;
use App\Models\OrderScheduleSlot;
use App\Models\Setting;
use App\Models\TimeSlot;
use App\Models\TimeSlotAgents;
use Exception;
use Illuminate\Http\Request;
use Grimzy\LaravelMysqlSpatial\Types\Point;
use Illuminate\Support\Facades\Validator;

class SettingController extends Controller
{
    public function clientAppSettings(Request $request){
        $settings=new Setting();
        $data['settings']  =Setting::pluck('value','name')->toArray();
        $data['order_time_slots']=OrderTimeSlotResource::collection(TimeSlot::with('agents')->where('is_active',true)->get());
        $data['order_schedule_slots']=OrderScheduleSlotResource::collection(OrderScheduleSlot::where('is_active',true)->get());
        $data['delivery_flat_locations']=DeliveryFlatLocation::collection(\App\Models\DeliveryFlatLocation::where('is_active',true)->get());
        $data['order_payments_type']=PaymentTypeResource::collection(\App\Models\PaymentType::where('is_active',true)->get());

        return $this->newResponse(true,__('api.success_response'),'client_app_settings',$data);
    }


    public function clientCartSettings(Request $request){
        $data['settings']  =Setting::pluck('value','name')->toArray();
        $data['order_time_slots']=OrderTimeSlotWAgentResource::collection(TimeSlotAgents::where('agent_id', $request->agent_id)->with(['TimeSlot' =>function($e){$e->where('is_active',true);}])->get()->pluck('TimeSlot')->filter());
        $data['order_schedule_slots']=OrderScheduleSlotResource::collection(OrderScheduleSlot::where('is_active',true)->get());

        $agent = Agent::where('id', $request->agent_id)->with(['DeliveryFlatLocation'=>function($e){
            $e->where('is_active', 1);
        }])->first();

        if($agent){
            $data['agent_flat_location'] = DFL_WithoutAgents::collection($agent->DeliveryFlatLocation);
        }else{
            $data['agent_flat_location'] = null;
        }
        return $this->newResponse(true,__('api.success_response'),'client_app_settings',$data);
    }

    public function getAgentMinimumCartons(Request $request){
        try {
                $data = $request->only('agent_id', 'lat', 'lng');
                $rules = [
                    'agent_id' => 'required|numeric',
                    'lat' => 'required',
                    'lng' => 'required',
                ];
                $validator = Validator::make($data, $rules);
                if ($validator->fails()) {
                    return $this->response(false, $this->validationHandle($validator->messages()));
                } else {
                    $point =  new Point($request->lat, $request->lng);
                    $agent_area = new AgentArea();
                    $agent_areass = $agent_area->where('agent_id', $request->agent_id)->contains('area',$point)->orderBy('id','desc')->first();

                    $result['agent_id'] = $request->agent_id;
                    $result['minimum_cartons'] = $agent_areass->minimum_cartons;
                    return $this->newResponse(true,__('api.success_response'),'agent_minimum_cartons',$result);
                } 
        }  catch (Exception $e) {
            return $this->response(false, $e->getMessage()); //'system error'
        }
    }

}
