<?php

namespace App\Http\Controllers;

use App\Models\PostponeReason;
use App\Models\TimeSlot;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;

class PostponeReasonController extends Controller
{
    public function index(Request $request){
        try {
            return $this->response(true,__('api.success_response'),PostponeReason::all());
        } catch (Exception $e) {
            return $this->response(false,__('api.fails_response'));
        }
    }
    public function edit(Request $request,PostponeReason $id){
        if($id){
            return $this->response(true,__('api.success_response'),$id);
        }
        return $this->newResponse(false,__('api.fails_response'));
    }
    public function update(Request $request){
        $data = $request->only(['id','title_ar','title_en']);
        $rules = [
            'id' => 'required',

            'title_ar' => 'required',
            'title_en' => 'required',
        ];

        $validator = Validator::make($data, $rules);
        if ($validator->fails()) {
            return $this->newResponse(false,$this->validationHandle($validator->messages()));
        }

        try {
            $timeslot=PostponeReason::find($request->id);
            $timeslot= $timeslot->update([

                'title_ar'=>$request->title_ar,
                'title_en'=>$request->title_en,
            ]);
            if($timeslot){
                return $this->newResponse(true,__('api.success_response'));
            }else{
                return $this->newResponse(false,__('api.fails_response'));
            }

        } catch (\Exception $e) {
//            \Log::info('create customer address error: '.$e->getMessage());
            return $this->newResponse(false,__('api.fails_response'));
        }


    }
    public function store(Request $request){
        $data = $request->only(['title_ar','title_en']);
        $rules = [
            'title_ar' => 'required',
            'title_en' => 'required',
        ];

        $validator = Validator::make($data, $rules);
        if ($validator->fails()) {
            return $this->newResponse(false,$this->validationHandle($validator->messages()));
        }

        try {

            $timeslot= PostponeReason::create([
                'title_ar'=>$request->title_ar,
                'title_en'=>$request->title_en,
            ]);
            if($timeslot){
                return $this->newResponse(true,__('api.success_response'));
            }else{
                return $this->newResponse(false,__('api.fails_response'));
            }


        } catch (\Exception $e) {
//            \Log::info('create customer address error: '.$e->getMessage());
            return $this->newResponse(false,__('api.fails_response'));
        }


    }
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
                $result = PostponeReason::whereIn('id', $request->ids)
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

    public function delete(PostponeReason $id)
    {
        try {
            if($id->delete())
            {
                return $this->response(true,__('api.success_response'));
            }
        } catch (Exception $e) {
            return $this->response(false,__('api.fails_response'));
        }

    }
}
