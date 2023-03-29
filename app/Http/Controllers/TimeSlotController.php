<?php

namespace App\Http\Controllers;

use App\Models\TimeSlot;
use App\Models\WithholdingAgentPoint;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;

class TimeSlotController extends Controller
{
    public function timeSlotList(Request $request)
    {
        try {
            return $this->response(true, __('api.success_response'), TimeSlot::with('agents')->get());
        } catch (Exception $e) {
            return $this->response(false, __('api.fails_response'));
        }
    }
    public function edit(Request $request, TimeSlot $id)
    {
        if ($id) {
            return $this->response(true, __('api.success_response'), $id);
        }
        return $this->newResponse(false, __('api.fails_response'));
    }
    public function update(Request $request)
    {
        $data = $request->only(['id', 'title_ar', 'title_en', 'start_at', 'end_at']);
        $rules = [
            'id' => 'required',
            'start_at' => 'required',
            'end_at' => 'required',
            'title_ar' => 'required',
            'title_en' => 'required',
            'agents' => 'nullable|array',
        ];

        $validator = Validator::make($data, $rules);
        if ($validator->fails()) {
            return $this->newResponse(false, $this->validationHandle($validator->messages()));
        }

        try {
            $timeslot = TimeSlot::find($request->id);
            $timeslot_edited = $timeslot->update([
                'start_at' => Carbon::parse($request->start_at)->format('H:m'),
                'end_at' => Carbon::parse($request->end_at)->format('H:m'),
                'title_ar' => $request->title_ar,
                'title_en' => $request->title_en,
            ]);

            if (!empty($request->agents)) {
                $timeslot->agents()->sync($request->agents);
            }
            if ($timeslot_edited) {
                return $this->newResponse(true, __('api.success_response'));
            } else {
                return $this->newResponse(false, __('api.fails_response'));
            }
        } catch (\Exception $e) {
            //            \Log::info('create customer address error: '.$e->getMessage());
            return $this->newResponse(false, __('api.fails_response'));
        }
    }
    public function store(Request $request)
    {
        $data = $request->only(['title_ar', 'title_en', 'start_at', 'end_at']);
        $rules = [
            'start_at' => 'required',
            'end_at' => 'required',
            'title_ar' => 'required',
            'title_en' => 'required',
            'agents' => 'nullable|array',
        ];

        $validator = Validator::make($data, $rules);
        if ($validator->fails()) {
            return $this->newResponse(false, $this->validationHandle($validator->messages()));
        }

        try {

            $timeslot = TimeSlot::create([
                'start_at' => Carbon::parse($request->start_at)->format('H:m'),
                'end_at' => Carbon::parse($request->end_at)->format('H:m'),
                'title_ar' => $request->title_ar,
                'title_en' => $request->title_en,
            ]);
            if (!empty($request->agents)) {
                $timeslot->agents()->attach($request->agents);
            }
            if ($timeslot) {
                return $this->newResponse(true, __('api.success_response'));
            } else {
                return $this->newResponse(false, __('api.fails_response'));
            }
        } catch (\Exception $e) {
            //            \Log::info('create customer address error: '.$e->getMessage());
            return $this->newResponse(false, __('api.fails_response'));
        }
    }
    public function changeStatus(Request $request)
    {
        try {
            $data = $request->only(['ids', 'status']);
            $rules = [
                'ids' => 'required',
                'status' => 'required',
            ];

            $validator = Validator::make($data, $rules);
            if ($validator->fails()) {
                return $this->response(false, $this->validationHandle($validator->messages()));
            } else {
                $result = TimeSlot::whereIn('id', $request->ids)
                    ->update(
                        ['is_active' => $request->status]
                    );
                if ($result == 0) // no update
                {
                    return $this->response(false, 'not valid id');
                }
                return $this->response(true, 'success');
            }
        } catch (Exception $e) {
            return $this->response(false, 'system error');
        }
    }

    public function delete(TimeSlot $id)
    {
        try {
            if ($id->delete()) {
                return $this->response(true, __('api.success_response'));
            }
        } catch (Exception $e) {
            return $this->response(false, __('api.fails_response'));
        }
    }
}
