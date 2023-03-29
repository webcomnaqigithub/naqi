<?php

namespace App\Http\Controllers\Api\Customer;

use App\Http\Controllers\Controller;

use App\Http\Resources\Address\AddressResource;
use App\Models\Complain;
use App\Models\Order;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;

class ComplainController extends Controller
{
    public function send(Request $request)
    {
        $data = $request->only(['description','title','complain_type']);
        $rules = [
            'description' => 'required',
            'title' => 'required',
            'complain_type' => 'required|in:complain,suggestion',

        ];

        $validator = Validator::make($data, $rules);
        if ($validator->fails()) {
            return $this->newResponse(false,$this->validationHandle($validator->messages()));
        }
        try {

                $data['used'] = false;


                $user=auth()->user();
                $data['userId'] = $user->id;

//                $newRecord =  new Complain();
//                $newRecord->description=$request->description;
//                $newRecord->title=$request->title;
//                $newRecord->complain_type=$request->complain_type;
//                $newRecord->userId=$user->id;
//                $newRecord->save();

                $newRecord=$user->complaints()->create($data);
                $lastOrder = Order::where('userId',$user->id)->orderBy('created_at','desc')->first();
                if($lastOrder){
                    $newRecord->agentId = $lastOrder->agentId;
                    $newRecord->save();
                }
//                $this->sendNotification($user->fcmToken,'App\Notifications\ComplainCreated',$user->language);

            $complaints= $user->complaints()->orderBy('created_at','desc')->get();
                return $this->newResponse(true,__('api.success_response'),'complaints',$complaints);

        } catch (\Exception $e) {
            \Log::info('send complain request '.$e->getMessage());
            return $this->newResponse(false,__('api.fails_response'));
        }

    }

    public function list(Request $request){
        $user=auth()->user();
        $complaints= $user->complaints()->orderBy('created_at','desc')->get();
        return $this->newResponse(true,__('api.success_response'),'complaints',$complaints);

    }

}
