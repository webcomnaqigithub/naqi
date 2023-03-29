<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Complain;
use App\Models\User;
use App\Models\Order;
use Illuminate\Support\Facades\Validator;
use App\Http\Resources\ComplainCollection;

class ComplainController extends Controller
{
    //list all
    public function list(Request $request)
    {

        try {
            $records= Complain::leftJoin('users','users.id','=','complains.userId')->select('complains.*','users.name','users.mobile')
            ->orderBy('created_at', 'desc')->paginate($request->get('perPage','20'));

            return new ComplainCollection($records);
            // return $this->response(true,'success',$records);
        } catch (Exception $e) {
            return $this->response(false,'system error');
        }

    }

    //details
    public function details($id)
    {

        try {
            $record = Complain::find($id);
            if($record == null){
                return $this->response(false,'id is not found');
            }
            return $this->response(true,'success',$record);
        } catch (Exception $e) {
            return $this->response(false,'system error');
        }

    }

    //delete
    public function delete($id)
    {
        try {
            $record = Complain::find($id);
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
            $data = $request->only(['description','title','userId']);
            $rules = [
                'description' => 'required',
                'title' => 'required',
                'userId' => 'required',
            ];

            $validator = Validator::make($data, $rules);
            if ($validator->fails()) {
                return $this->response(false,$this->validationHandle($validator->messages()));
            } else {
                $data['used'] = false;


                $user = User::find($request->userId);
                if($user == null){
                    return $this->response(false,'Failed to send');
                }
                $newRecord =  Complain::create($data);
                $lastOrder = Order::where('userId',$request->userId)->orderBy('created_at','desc')->first();
                if($lastOrder != null){
                    $newRecord->agentId = $lastOrder->agentId;
                    $newRecord->save();
                }
                $this->sendNotification($user->fcmToken,'App\Notifications\ComplainCreated',$user->language);


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
                    'status' => 'required',
                ];

                $validator = Validator::make($data, $rules);
                if ($validator->fails()) {
                    return $this->response(false,$this->validationHandle($validator->messages()));
                } else {
                    $result = Complain::whereIn('id', $request->ids)
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
            $data = $request->only(['id','title','description','userId']);
            $rules = [
                'id' => 'required|numeric',
                'title' => 'required',
                'description' => 'required',
                'userId' => 'required|numeric',
            ];

            $validator = Validator::make($data, $rules);
            if ($validator->fails()) {
                return $this->response(false,$this->validationHandle($validator->messages()));
            } else {
                $result = Complain::where('id', $request->id)
                ->update(
                    [
                    'title' => $request->title,
                    'description' => $request->description,
                    'userId' => $request->userId,
                    'status' => $request->status
                    ]);
                return $this->response(true,'success');
            }
        } catch (Exception $e) {
            return $this->response(false,'system error');
        }

    }


    public function search(Request $request)
    {
        try {
//            $data = $request->only(['from','to','agentId','status','type']);
//            $rules = [
//                // 'agentId' => 'required|numeric',
//                // 'from' => 'required',
//                // 'to' => 'required',
//            ];

//            $validator = Validator::make($data, $rules);
//            if ($validator->fails()) {
//                return $this->response(false,$this->validationHandle($validator->messages()));
//            } else {
                $records= Complain::leftJoin('users','users.id','=','complains.userId')->select('complains.*','users.name','users.mobile');
                if($request->from != null && $request->to != null ){
                    $records = $records->whereBetween('complains.created_at', [$request->from,$request->to]) ;
                }

                if($request->agentId != null){
                    $records = $records->where('agentId', $request->agentId);
                }
                if($request->status != null){
                    $records = $records->where('complains.status', $request->status);
                }
                if($request->complain_type != null){
                    $records = $records->where('complains.complain_type', $request->complain_type);
                }

                $records= $records->orderBy('created_at', 'desc')->get();
                return $this->response(true,'success',$records);
//            }


        } catch (Exception $e) {
            return $this->response(false,'system error');
        }

    }


}
