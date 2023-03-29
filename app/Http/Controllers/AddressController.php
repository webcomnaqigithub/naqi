<?php

namespace App\Http\Controllers;
use App\Http\Resources\Address\AddressResource;
use App\Http\Resources\Address\ListAddressResource;
use App\Models\Address;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Support\Facades\Validator;

use Illuminate\Http\Request;
use App\Http\Resources\UserResource;

use App\Http\Resources\UserCollection;
use App\Http\Resources\Address\MobileAddressCollection;
use App\Http\Resources\Address\WebAddressCollection;
use App\Models\AgentArea;

use function Clue\StreamFilter\fun;

class AddressController extends Controller
{

    //list all address
    public function list(Request $request)
    {
        $address = Address::with('customer');
        if(!empty($request->get('mobile'))){
            $address=$address->whereHas('customer',function(Builder $q) use ($request){
                $q->where('mobile',$request->get('mobile'));
            });
        }
        // if(!empty($request->get('agent_id'))){
        //     $address=$address->whereHas('customer',function(Builder $q) use ($request){
        //         $q->where('agent_id',$request->get('agent_id'));
        //     });
        // }
        $address=$address->paginate($request->get('perPage','20'));
        return new WebAddressCollection($address);
    }

    // public function getAddress(Request $request)
    // {
    //     $address = Address::with('customer');
    //     if(!empty($request->get('mobile'))){
    //         $address=$address->whereHas('customer',function(Builder $q) use ($request){
    //             $q->where('mobile',$request->get('mobile'));
    //         });
    //     }
    //     $agent_areas = new AgentArea();

    //     foreach($address as $user_address){
    //         $agent_area = $agent_areas->search($user_address->lat, $user_address->lng);
    //         $address['agent_id'] = $agent_area->id;
    //     }
    //     return $address;
    //     $address=$address->paginate($request->get('perPage','20'));
    //     return new WebAddressCollection($address);
    // }

    //list user address
    public function listUserAddress($userId)
    {

        try {
            return $this->response(true,'success',Address::where('userId',$userId)->get());
        } catch (Exception $e) {
            return $this->response(false,'system error');
        }

    }
    //details
    public function details(Address $id)
    {
            if($id)
        return $this->newResponse(true,__('api.success_response'),'data',AddressResource::make($id));
            else
        return $this->newResponse(false,__('api.fails_response'));

        try {
            $address = Address::join('users','users.id','=','address.userId')
            ->select('address.*','users.mobile','users.name  as clientName')->where('address.id',$id)->first();
            if($address == null){
                return $this->response(false,'id is not found');
            }
        return $this->response(true,'success',$address);
        } catch (Exception $e) {
            return $this->response(false,'system error');
        }

    }
    //delete
    public function delete($id)
    {

        try {
            $address = Address::find($id);
            if($address == null){
                return $this->response(false,'id is not found');
            }
            if($address->delete())
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
        $data = $request->only(['userId','name','lat','lng','type','default','region_id','city_id','district_id']);
        $rules = [
            'userId' => 'required|numeric',
            'name' => 'required',
            'lat' => 'required',
            'lng' => 'required',
            'region_id' => 'required',
            'city_id' => 'required',
            'district_id' => 'nullable',
            'type' => 'required|in:mosque,home,company',
            'default' => 'required|boolean',
        ];

        $validator = Validator::make($data, $rules);
        if ($validator->fails()) {
            return $this->newResponse(false,$this->validationHandle($validator->messages()));
        }
        try {
            $agent_area = new AgentArea();
            $agent_area = $agent_area->search($request->lat, $request->lng);
            if($agent_area){
                $data['agent_id'] = $agent_area->agent_id;
            }

            $newRecord =  Address::create($data);
            // check if address is default, update other user addresses
            if($request->default == 1)
            {
                $result = Address::where('id','<>', $newRecord->id)->where('userId',$request->userId)->update(['default' => 0]);
            }
            return $this->response(true,'success',$newRecord);

        } catch (\Exception $e) {
            return $this->response(false,$e->getMessage());
        }

    }

    //update status
    public function changeStatus(Request $request)
    {

        try {
            $data = $request->only(['ids', 'status']);
            $rules = [
                'ids' => 'required',
                'status' => 'required|numeric',
            ];

            $validator = Validator::make($data, $rules);
            if ($validator->fails()) {
                return $this->response(false,$this->validationHandle($validator->messages()));
            } else {
                $result = Address::whereIn('id', $request->ids)
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

    //update status
    public function updateDefaultAddress(Request $request)
    {

        try {
            $data = $request->only(['id', 'userId']);
            $rules = [
                'userId' => 'required',
                'id' => 'required|numeric',
            ];

            $validator = Validator::make($data, $rules);
            if ($validator->fails()) {
                return $this->response(false,$this->validationHandle($validator->messages()));
            } else {
                $result = Address::where('id', $request->id)->where('userId', $request->userId)
                ->update(
                    ['default' => 1]);
                if($result == 0) // no update
                {
                    return $this->response(false,'not valid id');
                } else {
                    Address::where('userId', $request->userId)->where('id', '<>',$request->id)
                    ->update(
                    ['default' => 0]);
                }

                $address =  Address::where('userId', $request->userId)->get();
                return $this->response(true,'success',$address);

            }
        } catch (Exception $e) {
            return $this->response(false,'system error');
        }

    }
    //update
    public function update(Request $request)
    {
        try {
            $data = $request->only(['id', 'userId','name','lat','lng','type','default','status','region_id','city_id','district_id']);
            $rules = [
                'id' => 'required|numeric',
                'userId' => 'required|numeric',
                'name' => 'required',
                'lat' => 'required',
                // 'status' => 'required',
                'region_id' => 'required',
                'city_id' => 'required',

                'lng' => 'required',
                'type' => 'required|in:mosque,home,company',
                'default' => 'required|boolean',
            ];

            $validator = Validator::make($data, $rules);
            if ($validator->fails()) {
                return $this->response(false,$this->validationHandle($validator->messages()));
            } else {
                if($request->status != null) {
                    $result = Address::where('id', $request->id)->where('userId',$request->userId)
                    ->update(
                        ['name' => $request->name,
                        'lat' => $request->lat,
                        'lng' => $request->lng,
                        'type' => $request->type,
                        'region_id' => $request->region_id,
                        'city_id' => $request->city_id,
                        'district_id' => $request->district_id,
                        'status' => $request->status,
                        'default' => $request->default]);
                    if($result == 0) // no update
                    {
                        return $this->response(false,'not valid id');
                    }
                    // check if address is default, update other user addresses
                    if($request->default == 1)
                    {
                        $result = Address::where('id','<>', $request->id)->where('userId',$request->userId)->update(['default' => 0]);
                    }
                } else {
                    $result = Address::where('id', $request->id)->where('userId',$request->userId)
                    ->update(
                        ['name' => $request->name,
                        'lat' => $request->lat,
                        'lng' => $request->lng,
                        'type' => $request->type,
                        'default' => $request->default]);
                    if($result == 0) // no update
                    {
                        return $this->response(false,'not valid id');
                    }
                // check if address is default, update other user addresses
                    if($request->default == 1)
                    {
                        $result = Address::where('id','<>', $request->id)->where('userId',$request->userId)->update(['default' => 0]);
                    }

                }
                return $this->response(true,'success');

            }

        } catch (Exception $e) {
            return $this->response(false,'system error');
        }


    }
}
