<?php

namespace App\Http\Controllers;

use App\Models\City;
use App\Models\DeliveryFlatAgents;
use App\Models\DeliveryFlatLocation;
use App\Models\TimeSlot;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;

class DeliveryFlatLocationController extends Controller
{
    public function deliveryFlatLocationList(Request $request)
    {
        return $this->response(true, __('api.success_response'), DeliveryFlatLocation::with('agents')->get());
    }
    public function deliveryFlatLocationsStatus(Request $request)
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
                $result = DeliveryFlatLocation::whereIn('id', $request->ids)
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
    public function store(Request $request)
    {
        $data = $request->only(['title_ar', 'title_en', 'delivery_cost', 'default_cost']);
        $rules = [
            'title_ar' => 'required',
            'title_en' => 'required',
            'delivery_cost' => 'required',
            'default_cost' => 'required',
            'agents' => 'nullable|array',
            //            'is_active' => 'required',
        ];

        $validator = Validator::make($data, $rules);
        if ($validator->fails()) {
            return $this->newResponse(false, $this->validationHandle($validator->messages()));
        }

        try {
            //            $deliveryFlatLocation=DeliveryFlatLocation::find($request->id);
            $deliveryFlatLocation = DeliveryFlatLocation::create([
                'delivery_cost' => $request->delivery_cost,
                'title_ar' => $request->title_ar,
                'title_en' => $request->title_en,
                'default_cost' => $request->default_cost,
            ]);
            if ($deliveryFlatLocation) {
                if (!empty($request->agents)) {
                    $deliveryFlatLocation->agents()->attach($request->agents);
                }

                return $this->newResponse(true, __('api.success_response'));
            } else {
                return $this->newResponse(false, __('api.fails_response'));
            }
        } catch (\Exception $e) {
            return $e->getMessage();
            //            \Log::info('create customer address error: '.$e->getMessage());
            return $this->newResponse(false, __('api.fails_response'));
        }
    }
    public function update(Request $request)
    {
        $data = $request->only(['id', 'title_ar', 'title_en', 'delivery_cost', 'default_cost']);
        $rules = [
            'id' => 'required',
            'title_ar' => 'required',
            'title_en' => 'required',
            'delivery_cost' => 'required',
            'default_cost' => 'required',
            //            'is_active' => 'required',
            'agents' => 'nullable|array',

        ];

        $validator = Validator::make($data, $rules);
        if ($validator->fails()) {
            return $this->newResponse(false, $this->validationHandle($validator->messages()));
        }

        try {

            $deliveryFlatLocation = DeliveryFlatLocation::find($request->id);
            $deliveryFlatLocation_edited = $deliveryFlatLocation->update([
                'delivery_cost' => $request->delivery_cost,
                'title_ar' => $request->title_ar,
                'title_en' => $request->title_en,
                'default_cost' => $request->default_cost,
            ]);
            if ($deliveryFlatLocation_edited) {
                if (!empty($request->agents)) {
                    $deliveryFlatLocation->agents()->sync($request->agents);
                }
                return $this->newResponse(true, __('api.success_response'));
            } else {
                return $this->newResponse(false, __('api.fails_response'));
            }
        } catch (\Exception $e) {
            //            \Log::info('create customer address error: '.$e->getMessage());
            return $this->newResponse(false, __('api.fails_response'));
        }
    }
    public function delete($id)
    {
        try {
            $deliveryLocation = DeliveryFlatLocation::find($id);

            if ($deliveryLocation->delete()) {
                return $this->response(true, __('api.success_response'));
            }
        } catch (Exception $e) {
            return $this->response(false, __('api.fails_response'));
        }
    }
}
