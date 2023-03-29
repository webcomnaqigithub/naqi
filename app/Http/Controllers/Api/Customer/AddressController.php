<?php

namespace App\Http\Controllers\Api\Customer;

use App\Http\Controllers\Controller;
use App\Http\Resources\Address\AddressResource;
use App\Models\Address;
use App\Models\AgentArea;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;

class AddressController extends Controller
{
    public function all(Request $request){
        $customer=$request->user('customer');
        $addresses =AddressResource::collection($customer->addresses()->get());
        return $this->newResponse(true,__('api.success_response'),'addresses',$addresses);

    }
    public function create(Request $request)
    {
        $data = $request->only(['name','lat','lng','type','default','region_id','city_id','district_id']);
        $rules = [
//            'userId' => 'required|numeric',
            'name' => 'required',
            'lat' => 'required',
            'lng' => 'required',
            'type' => 'required|in:mosque,home,company',
            'default' => 'required|boolean',
            'region_id' => 'required|numeric',
            'city_id' => 'required|numeric',
            'district_id' => 'nullable|numeric',
        ];

        $validator = Validator::make($data, $rules);
        if ($validator->fails()) {
            return $this->newResponse(false,$this->validationHandle($validator->messages()));
        }
        try {
            $customer=$request->user('customer');
            if($customer){
                // --------------
                $agent_area = new AgentArea();
                $agent_area = $agent_area->search($request->lat, $request->lng);
                if($agent_area !== null){
                    $data['agent_id'] = $agent_area->agent_id;
                }
                // --------------
                $newRecord = $customer->addresses()->create($data);
                // check if address is default, update other user addresses
                if($request->default == 1)
                {
                    $customer->addresses()->where('id','<>', $newRecord->id)->update(['default' => 0]);
//                    $result = Address::where('id','<>', $newRecord->id)->where('userId',$request->userId)->update(['default' => 0]);
                }

               $addresses =AddressResource::collection($customer->addresses()->get());
                return $this->newResponse(true,__('api.success_response'),'addresses',$addresses);
            }else{
                return $this->newResponse(false,__('api.fails_response'));
            }

        } catch (Exception $e) {
            \Log::info('create customer address error: '.$e->getMessage());
            return $this->newResponse(false,__('api.fails_response'));
        }

    }

    public function update(Request $request)
    {
        $data = $request->only(['id','name','lat','lng','type','default','region_id','city_id','district_id']);
        $rules = [
            'id' => 'required|numeric',
            'name' => 'required',
            'lat' => 'required',
            'lng' => 'required',
            'type' => 'required|in:mosque,home,company',
            'default' => 'required|boolean',
            'region_id' => 'required|numeric',
            'city_id' => 'required|numeric',
            'district_id' => 'nullable|numeric',
        ];

        $validator = Validator::make($data, $rules);
        if ($validator->fails()) {
            return $this->newResponse(false,$this->validationHandle($validator->messages()));
        }
        try {
            $customer=$request->user('customer');
            if($customer){
                $newRecord = $customer->addresses()->where('id',$request->id)->update($data);

                // check if address is default, update other user addresses
                if($request->default == 1)
                {
                    $customer->addresses()->where('id','<>', $request->id)->update(['default' => 0]);
                }

                $addresses =AddressResource::collection($customer->addresses()->get());
                return $this->newResponse(true,__('api.success_response'),'addresses',$addresses);
            }else{
                return $this->newResponse(false,__('api.fails_response'));
            }

        } catch (Exception $e) {
            \Log::info('create customer address error: '.$e->getMessage());
            return $this->newResponse(false,__('api.fails_response'));
        }

    }
    public function destroy($id)
    {

        try {
            $address = Address::find($id);
            if($address){
                if($address->delete()){
                return $this->newResponse(true,__('api.success_response'));
                }
            }
            return $this->newResponse(false,__('api.fails_response'));

        } catch (\Exception $e) {
            \Log::error('delete customer address '.$e->getMessage());
            return $this->newResponse(false,__('api.fails_response'));
        }

    }


    public function changeDefaultAddress(Request $request)
    {
        $data = $request->only(['id']);
        $rules = [
            'id' => 'required|numeric',
        ];

        $validator = Validator::make($data, $rules);
        if ($validator->fails()) {
            return $this->newResponse(false,$this->validationHandle($validator->messages()));
        }
        try {
            $customer=$request->user('customer');
            if($customer){
                $newRecord = $customer->addresses()->where('id',$request->id)->update(['default' => 1]);
                    if($newRecord){
                        $customer->addresses()->where('id','<>', $request->id)->update(['default' => 0]);
                        $addresses =AddressResource::collection($customer->addresses()->get());
                        return $this->newResponse(true,__('api.success_response'),'addresses',$addresses);

                    }
            }

          return $this->newResponse(false,__('api.fails_response'));


        } catch (\Exception $e) {
            return $this->newResponse(false,__('api.fails_response'));
        }

    }

}
