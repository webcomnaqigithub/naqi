<?php

namespace App\Http\Controllers;

use App\Http\Resources\WithholdingAgentPointResource;
use App\Models\DeliveryFlatLocation;
use App\Models\WithholdingAgentPoint;
use Illuminate\Http\Request;
use Illuminate\Support\Arr;
use Illuminate\Support\Facades\Validator;

class WithHoldingAgentPointController extends Controller
{
    public function index(Request $request){

        return $this->response(true,__('api.success_response'),WithholdingAgentPointResource::collection(WithholdingAgentPoint::all()));
    }

    public function store(Request $request){
        $data = $request->only(['agents_ids','from','to']);
        $rules = [
            'agents_ids' => 'required|min:1',
            'from' => 'required|date',
            'to' => 'required|date',
//            'is_active' => 'required',
        ];

        $validator = Validator::make($data, $rules);
        if ($validator->fails()) {
            return $this->newResponse(false,$this->validationHandle($validator->messages()));
        }

        try {
            if(!empty($request->agents_ids)){
                foreach ($request->agents_ids as $agent ){
                    $data=Arr::except($data,'agents_ids');
                    $data['agent_id']=$agent;
                    $withHold= WithholdingAgentPoint::create($data);
                }
            }
                return $this->newResponse(true,__('api.success_response'));
        } catch (\Exception $e) {
//            return $e->getMessage();
//            \Log::info('withhold error: '.$e->getMessage());
            return $this->newResponse(false,__('api.fails_response'));
        }

    }
    public function update(Request $request){
        $data = $request->only(['agent_id','from','to','id']);
        $rules = [
            'id' => 'required|exists:withholding_agent_points,id',
            'agent_id' => 'required|exists:agents,id',
            'from' => 'required|date',
            'to' => 'required|date',
        ];

        $validator = Validator::make($data, $rules);
        if ($validator->fails()) {
            return $this->newResponse(false,$this->validationHandle($validator->messages()));
        }

        try {
            $withHold=WithholdingAgentPoint::find($request->id);
            $withHold= $withHold->update(Arr::except($data, 'id'));
            if($withHold){
                return $this->newResponse(true,__('api.success_response'));
            }else{
                return $this->newResponse(false,__('api.fails_response'));
            }

        } catch (\Exception $e) {
//            \Log::info('create customer address error: '.$e->getMessage());
            return $this->newResponse(false,__('api.fails_response'));
        }


    }
    public function delete($id)
    {
        try {
            $holdAgent = WithholdingAgentPoint::find($id);

            if($holdAgent->delete())
            {
                return $this->response(true,__('api.success_response'));

            }
        } catch (Exception $e) {
            return $this->response(false,__('api.fails_response'));
        }

    }
}
