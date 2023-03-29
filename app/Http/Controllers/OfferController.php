<?php

namespace App\Http\Controllers;

use App\Http\Resources\OfferResource;
use App\Models\Offer;
use App\Models\AgentOffer;
use Illuminate\Support\Facades\Validator;
use Illuminate\Http\Request;
use App\Models\Banner;
use App\Models\Agent;

class OfferController extends Controller
{
    //update change status
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
                return $this->response(false, $this->validationHandle($validator->messages()));
            } else {
                $result = Offer::whereIn('id', $request->ids)
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

    //list all
    public function list(Request $request)
    {

        try {
            $products = Offer::all();
            //            foreach($products as $product){
            //                $product->picture = url('/').$product->picture;
            //            }
            return $this->response(true, 'success', OfferResource::collection($products));
        } catch (Exception $e) {
            return $this->response(false, 'system error');
        }
    }

    //search by location
    //    public function searchByLocation(Request $request)
    //    {
    //
    //        try {
    //            $data = $request->only(['lng','lat','addressType','userId']);
    //            $rules = [
    //                'userId' => 'required',
    //                'lng' => 'required',
    //                'lat' => 'required',
    //                'addressType' => 'required|in:home,mosque,company',
    //            ];
    //            $validator = Validator::make($data, $rules);
    //            if ($validator->fails()) {
    //                return $this->response(false,$this->validationHandle($validator->messages()));
    //            } else {
    //                return $this->response(true,'success',$this->getOffersInMainScreen($request,$request->userId,$request->addressType));
    //            }
    //        } catch (Exception $e) {
    //            return $this->response(false,'system error');
    //        }
    //    }



    //list agent products
    public function listAgentOffer($userId)
    {

        try {
            return $this->response(true, 'success', Offer::all()->where('agentId', $userId));
        } catch (Exception $e) {
            return $this->response(false, 'system error');
        }
    }
    //details
    public function details($id)
    {

        try {
            $record = Offer::find($id);
            $record = OfferResource::make($record);
            if ($record == null) {
                return $this->response(false, 'id is not found');
            }
            return $this->response(true, 'success', $record);
        } catch (Exception $e) {
            return $this->response(false, 'system error');
        }
    }
    //delete
    public function delete($id)
    {

        try {
            $record = Offer::find($id);
            if ($record == null) {
                return $this->response(false, 'id is not found');
            }
            if ($record->delete()) {
                return $this->response(true, 'success');
            } else {
                return $this->response(false, 'failed');
            }
        } catch (Exception $e) {
            return $this->response(false, 'system error');
        }
    }
    //create
    public function create(Request $request)
    {
        try {
            $data = $request->only([
                'name_ar',
                'name_en',
                'desc_ar',
                'desc_en',
                'picture',
                'old_price',
                'price',
                'start_date',
                'expire_date',
                'is_active',
                'is_banner',
                'product_id',
                'product_qty',
                'gift_product_id',
                'gift_product_qty',
                'agent_id',
                'offer_type',
            ]);
            $rules = [
                'name_ar' => 'required',
                'name_en' => 'required',
                'desc_ar' => 'required',
                'desc_en' => 'required',
                'picture' => 'required|image|mimes:jpeg,png,jpg,gif,svg|max:2048',
                'old_price' => 'nullable',
                'price' => 'required',
                'start_date' => 'required',
                'expire_date' => 'required',
                'is_active' => 'required',
                'is_banner' => 'required',
                'product_id' => 'required',
                'product_qty' => 'required',
                'gift_product_id' => 'nullable',
                'gift_product_qty' => 'nullable',
                'agent_id' => 'required',
                'offer_type' => 'nullable',
            ];
            $validator = Validator::make($data, $rules);
            if ($validator->fails()) {
                return $this->response(false, $this->validationHandle($validator->messages()));
            } else {
                if ($request->file('picture') !== null) {
                    $imageName = time() . '.' . $request->picture->extension();
                    $request->picture->move(public_path('products'), $imageName);
                    $data['picture'] = '/products/' . $imageName;
                    $newRecord =  Offer::create($data);
                    //                    $newRecord =  OfferResource::make($newRecord);
                    return $this->response(true, 'success', $newRecord);
                }
            }
        } catch (\Exception $e) {
            return $this->response(false, 'system error');
        }
    }
    // use URL of image not image file
    public function createWithImageUrl(Request $request)
    {

        try {
            $data = $request->only([
                'name_ar',
                'name_en',
                'desc_ar',
                'desc_en',
                'picture',
                'old_price',
                'price',
                'start_date',
                'expire_date',
                'is_active',
                'is_banner',
                'product_id',
                'product_qty',
                'gift_product_id',
                'gift_product_qty',
                'agent_id',
                'offer_type',

            ]);
            $rules = [
                'name_ar' => 'required',
                'name_en' => 'required',
                'desc_ar' => 'required',
                'desc_en' => 'required',
                'picture' => 'required',
                'old_price' => 'nullable',
                'price' => 'required',
                'start_date' => 'required',
                'expire_date' => 'required',
                'is_active' => 'required',
                'is_banner' => 'required',
                'product_id' => 'required',
                'product_qty' => 'required',
                'gift_product_id' => 'nullable',
                'gift_product_qty' => 'nullable',
                'agent_id' => 'required',
                'offer_type' => 'nullable'
            ];
            $validator = Validator::make($data, $rules);
            if ($validator->fails()) {
                return $this->response(false, $this->validationHandle($validator->messages()));
            } else {
                //                $data['type'] = '2';
                //                $data['old_price']=$request->homePrice;
                //                $data['offer_price']=$request->mosquePrice;
                //                $data['offer_qty']=$request->officialPrice;
                $newRecord =  Offer::create($data);
                return $this->response(true, 'success', $newRecord);
            }
        } catch (Exception $e) {
            return $this->response(false, 'system error');
        }
    }

    //update
    public function update(Request $request)
    {

        try {
            $data = $request->only([
                'id',
                'name_ar',
                'name_en',
                'desc_ar',
                'desc_en',
                'picture',
                'old_price',
                'price',
                'start_date',
                'expire_date',
                'is_active',
                'is_banner',
                'product_id',
                'product_qty',
                'gift_product_id',
                'gift_product_qty',
                'agent_id',
                'offer_type',

            ]);
            $rules = [
                'id' => 'required',
                'name_ar' => 'required',
                'name_en' => 'required',
                'desc_ar' => 'required',
                'desc_en' => 'required',
                'picture' => 'required',
                'old_price' => 'nullable',
                'price' => 'required',
                'start_date' => 'required',
                'expire_date' => 'required',
                'is_active' => 'required',
                'is_banner' => 'required',
                'product_id' => 'required',
                'product_qty' => 'required',
                'gift_product_id' => 'nullable',
                'gift_product_qty' => 'nullable',
                'agent_id' => 'required',
                'offer_type' => 'nullable'
            ];

            $validator = Validator::make($data, $rules);
            if ($validator->fails()) {
                return $this->response(false, $this->validationHandle($validator->messages()));
            } else {

                if ($request->file('picture') !== null) {
                    $result = Offer::find($request->id);
                    if ($result == null) {
                        return $this->response(false, 'not valid id');
                    }
                    $imageName = time() . '.' . $request->picture->extension();
                    //                    $request->picture->move(public_path('products'), $imageName);
                    //                    $request->picture->move(public_path('products'), $imageName);
                    $data['picture'] = '/products/' . $imageName;


                    $result->update($data);
                    return $this->response(true, 'success');
                } else {
                    $result = Offer::where('id', $request->id)
                        ->update($data);
                    if ($result == 0) // no update
                    {
                        return $this->response(false, 'not valid id');
                    }
                    return $this->response(true, 'success');
                }
            }
        } catch (Exception $e) {
            return $this->response(false, 'system error');
        }
    }
    //update with image URL
    public function updateWithImageUrl(Request $request)
    {

        try {
            $data = $request->only([
                'id',
                'name_ar',
                'name_en',
                'desc_ar',
                'desc_en',
                'picture',
                'old_price',
                'price',
                'start_date',
                'expire_date',
                'is_active',
                'is_banner',
                'product_id',
                'product_qty',
                'gift_product_id',
                'gift_product_qty',
                'agent_id',
                'offer_type',

            ]);
            $rules = [
                'id' => 'required',
                'name_ar' => 'required',
                'name_en' => 'required',
                'desc_ar' => 'required',
                'desc_en' => 'required',
                'picture' => 'required',
                'old_price' => 'nullable',
                'price' => 'required',
                'start_date' => 'required',
                'expire_date' => 'required',
                'is_active' => 'required',
                'is_banner' => 'required',
                'product_id' => 'required',
                'product_qty' => 'required',
                'gift_product_id' => 'nullable',
                'gift_product_qty' => 'nullable',
                'agent_id' => 'required',
                'offer_type' => 'nullable',
            ];
            $validator = Validator::make($data, $rules);
            if ($validator->fails()) {
                return $this->response(false, $this->validationHandle($validator->messages()));
            } else {

                $result = Offer::where('id', $request->id)
                    ->update($data);
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
    //search
    public function search(Request $request, Offer $search)
    {

        try {
            $search = $search->newQuery();
            if ($request->has('name_ar')) {
                $search->where('name_ar', 'LIKE', '%' . $request->input('name_ar') . '%');
            }
            if ($request->has('name_en')) {
                $search->where('name_en', 'LIKE', '%' . $request->input('name_en') . '%');
            }
            return $this->response(true, 'success', $search->paginate($this->pageSize));
        } catch (Exception $e) {
            return $this->response(false, 'system error');
        }
    }
}
