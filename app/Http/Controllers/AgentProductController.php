<?php

namespace App\Http\Controllers;

use App\Models\Agent;
use App\Models\AgentProduct;
use App\Models\Customer;
use App\Models\Product;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;
use Carbon\Carbon;
use App\Jobs\SendSms;
use Log;

class AgentProductController extends Controller
{


    public function getAgentProduct(Request $request)
    {

        $data = $request->only(['address_id', 'user_id', 'agent_id']);
        $rules = [
            'address_id' => 'required|exists:address,id,deleted_at,NULL',
            'user_id' => 'required|exists:users,id,deleted_at,NULL',
            'agent_id' => 'required|exists:agents,id,deleted_at,NULL',
//            'lng' => 'required',
//            'lat' => 'required',
//            'address_type' => 'nullable|in:home,mosque,company',
        ];
        $validator = Validator::make($data, $rules);
        if ($validator->fails()) {
            return $this->newResponse(false, $this->validationHandle($validator->messages()));
        }
        $user = Customer::find($request->user_id);
        if ($user) {
            $customer_address = $user->addresses()->find($request->address_id);
            if ($customer_address) {
                $target_price = '';
                switch ($customer_address->type) {
                    case 'home':
                        $target_price = 'homePrice';
                        break;
                    case 'mosque':
                        $target_price = 'mosquePrice';
                        break;
                    case 'company':

                        $target_price = 'officialPrice';
                        break;
                }

                $products = Product::leftJoin('agentProducts', 'agentProducts.productId', '=', 'products.id')
                    ->where('agentProducts.agentId', $request->agent_id)
                    ->where('agentProducts.status', 1)
                    ->select('products.id as id', 'arabicName', 'englishName', 'picture', 'agentProducts.' . $target_price . ' as price', 'agentProducts.status',
                        'agentProducts.min_order_qty')
                    ->get();
                return $this->newResponse(true, __('api.success_response'), 'products', $products);

                }
        }

    }

    //list all
    public function list(Request $request)
    {
        try {
            $products = AgentProduct::select('agentProducts.id', 'agentProducts.productId', 'agentProducts.agentId', 'products.arabicName', 'products.englishName', 'products.picture',
                'agentProducts.mosquePrice', 'agentProducts.otherPrice', 'agentProducts.homePrice', 'agentProducts.officialPrice', 'agentProducts.status')
                ->leftJoin('products', 'products.id', '=', 'agentProducts.productId')
                ->get();
            foreach ($products as $product) {
                $product->picture = url('/') . $product->picture;
            }
            return $this->response(true, 'success', $products);

        } catch (Exception $e) {
            return $this->response(false, 'system error');
        }

    }

    //update status
    public function changeStatus(Request $request)
    {
        try {
            $data = $request->only(['ids', 'status']);
            $rules = [
                'ids' => 'required',
                'status' => 'required|numeric|in:1,2',
            ];

            $validator = Validator::make($data, $rules);
            if ($validator->fails()) {
                return $this->response(false, $this->validationHandle($validator->messages()));
            } else {
                $result = AgentProduct::whereIn('id', $request->ids)
                    ->update(
                        ['status' => $request->status]);
                if ($result == 0) // no update
                {
                    return $this->response(false, 'not valid id');
                }

                if ($request->status == 2) {
                    $agents = AgentProduct::whereIn('agentProducts.id', $request->ids)->leftJoin('agents', 'agents.id', '=', 'agentId')->select('agents.name', 'agents.mobile')->get();
                    if (count($agents) > 0) {
                        // send sms if new status = 2
                        $this->sendSms(' تم إيقاف منتجات لدى الوكيل  ' . $agents->first()->name, '0502078581');
                    }
                }


                return $this->response(true, 'success');

            }
        } catch (Exception $e) {
            return $this->response(false, 'system error');
        }

    }

    public function deleteMultiple(Request $request)
    {
        try {
            $data = $request->only(['ids']);
            $rules = [
                'ids' => 'required',
            ];

            $validator = Validator::make($data, $rules);
            if ($validator->fails()) {
                return $this->response(false, $this->validationHandle($validator->messages()));
            } else {
                $result = AgentProduct::whereIn('id', $request->ids)
                    ->delete();
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

    //details
    public function details($id)
    {

        try {
            $record = AgentProduct::find($id);
            if ($record == null) {
                return $this->response(false, 'id is not found');
            }
            return $this->response(true, 'success', $record);
        } catch (Exception $e) {
            return $this->response(false, 'system error');
        }

    }

    //list agent products
    public function listAgentProducts($agentId)
    {

        try {
            $products = AgentProduct::select('agentProducts.status', 'agentProducts.id', 'agentProducts.productId', 'agentProducts.agentId', 'products.arabicName', 'products.englishName', 'products.picture',
                'agentProducts.mosquePrice', 'agentProducts.otherPrice', 'agentProducts.homePrice', 'agentProducts.officialPrice')
                ->leftJoin('products', 'products.id', '=', 'agentProducts.productId')
                ->where('agentId', $agentId)->get();
            foreach ($products as $product) {
                $product->picture = url('/') . $product->picture;
            }
            return $this->response(true, 'success', $products);
        } catch (Exception $e) {
            return $this->response(false, 'system error');
        }


    }

    //create
    public function create(Request $request)
    {
        try {
            $data = $request->only(['agentId', 'productId', 'homePrice', 'mosquePrice', 'officialPrice']);
            $rules = [
                'agentId' => 'required|numeric',
                'productId' => 'required',
                'homePrice' => 'required|numeric',
                'mosquePrice' => 'required|numeric',
                // 'otherPrice' => 'required|numeric',
                'officialPrice' => 'required|numeric',
            ];
            $validator = Validator::make($data, $rules);
            if ($validator->fails()) {
                return $this->response(false, $this->validationHandle($validator->messages()));
            } else {

                $newProducts = [];
                // add products to order
                foreach ($request->productId as $product) {
                    $newProducts[] = [
                        'agentId' => $request->agentId,
                        'productId' => $product,
                        'homePrice' => $request->homePrice,
                        'mosquePrice' => $request->mosquePrice,
                        'officialPrice' => $request->officialPrice,
                        'created_at' => Carbon::now(),
                        'updated_at' => Carbon::now(),
                    ];
                }
                $products = AgentProduct::insert($newProducts);
                if (!$products)
                    return $this->response($products, 'failed to add products to agent');
                return $this->response($products, 'success');
            }
        } catch (Exception $e) {
            return $this->response(false, 'system error');
        }


    }

    //create
    public function copy(Request $request)
    {
        try {
            $data = $request->only(['agentId', 'products']);
            $rules = [
                'agentId' => 'required|numeric',
                'products' => 'required',
            ];
            $validator = Validator::make($data, $rules);
            if ($validator->fails()) {
                return $this->response(false, $this->validationHandle($validator->messages()));
            } else {
                $newProducts = [];
                // add products to order
                foreach ($request->products as $product) {
                    // return $product;
                    if (isset($product['id'])) {
                        //update
                        AgentProduct::where('productId', $product['productId'])
                            ->where('agentId', $product['agentId'])->update(
                                [
                                    'homePrice' => $product['homePrice'],
                                    'mosquePrice' => $product['mosquePrice'],
                                    'officialPrice' => $product['officialPrice'],
                                    'updated_at' => Carbon::now(),
                                ]
                            );
                    } else {
                        // add new
                        $newProducts[] = [
                            'agentId' => $request->agentId,
                            'productId' => $product['productId'],
                            'homePrice' => $product['homePrice'],
                            'mosquePrice' => $product['mosquePrice'],
                            'status' => $product['status'],
                            'officialPrice' => $product['officialPrice'],
                            'created_at' => Carbon::now(),
                            'updated_at' => Carbon::now(),
                        ];
                    }

                }
                $products = AgentProduct::insert($newProducts);
                if (!$products)
                    return $this->response($products, 'failed to add products to agent');
                return $this->response(true, 'success');
            }
        } catch (Exception $e) {
            return $this->response(false, 'system error');
        }

    }

    //update
    public function update(Request $request)
    {
        try {
            $data = $request->only(['id', 'officialPrice', 'homePrice', 'mosquePrice', 'status']);
            $rules = [
                'id' => 'required',
                'homePrice' => 'required',
                'officialPrice' => 'required',
                'mosquePrice' => 'required',
                'status' => 'required',
            ];

            $validator = Validator::make($data, $rules);
            if ($validator->fails()) {
                return $this->response(false, $this->validationHandle($validator->messages()));
            } else {

                $result = AgentProduct::where('id', $request->id)
                    ->update(
                        ['homePrice' => $request->homePrice,
                            'officialPrice' => $request->officialPrice,
                            'status' => $request->status,
                            'mosquePrice' => $request->mosquePrice]);
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

    public function updateValueById(Request $request)
    {
        try {
            $data = $request->only(['id', 'officialPrice', 'homePrice', 'mosquePrice', 'status']);
            $rules = [
                'id' => 'required',
                // 'homePrice' => 'required',
                // 'officialPrice' => 'required',
                // 'mosquePrice' => 'required',
                // 'status' => 'required',
            ];

            $validator = Validator::make($data, $rules);
            if ($validator->fails()) {
                return $this->response(false, $this->validationHandle($validator->messages()));
            } else {
                $oldRecord = AgentProduct::find($request->id);
                if ($oldRecord == null) {
                    return $this->response(false, 'not found product');
                }

                if ($request->homePrice != null) {
                    $oldRecord->homePrice = $request->homePrice;
                }
                if ($request->mosquePrice != null) {
                    $oldRecord->mosquePrice = $request->mosquePrice;
                }
                if ($request->officialPrice != null) {
                    $oldRecord->officialPrice = $request->officialPrice;
                }
                if ($request->status != null) {
                    $oldRecord->status = $request->status;
                }
                $oldRecord->save();
                return $this->response(true, 'success');
            }
        } catch (Exception $e) {
            return $this->response(false, 'system error');
        }

    }

    //update
    public function update2(Request $request, $id)
    {
        try {
            $data = $request->only(['officialPrice', 'homePrice', 'mosquePrice', 'status']);
            $rules = [
                // 'id' => 'required',
                // 'homePrice' => 'required',
                // 'officialPrice' => 'required',
                // 'mosquePrice' => 'required',
                // 'status' => 'required',
            ];

            $validator = Validator::make($data, $rules);
            if ($validator->fails()) {
                return $this->response(false, $this->validationHandle($validator->messages()));
            } else {

                $array = [];

                $result = AgentProduct::where('id', $id)
                    ->update(
                        $data);
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


    public function delete($id)
    {

        try {
            $record = AgentProduct::find($id);
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

    public function search(Request $request)
    {

        try {
            $data = $request->only(['skip', 'take', 'sort', 'searchOperation', 'searchExpr', 'searchValue', 'filter', 'agentId']);
            $rules = [

            ];

            $validator = Validator::make($data, $rules);
            if ($validator->fails()) {
                return $this->response(false, $this->validationHandle($validator->messages()));
            } else {


                $pageSize = 300;

                // $products = AgentProduct::select('agentProducts.id','agentProducts.productId','agentProducts.agentId','products.arabicName','products.englishName','products.picture',
                // 'agentProducts.mosquePrice','agentProducts.otherPrice','agentProducts.homePrice','agentProducts.officialPrice','agentProducts.status')
                // ->leftJoin('products', 'products.id', '=', 'agentProducts.productId')
                // ->get();


                // search
                $products = AgentProduct::select('agentProducts.id', 'agentProducts.productId', 'agentProducts.agentId', 'products.arabicName', 'products.englishName', 'products.picture',
                    'agentProducts.mosquePrice', 'agentProducts.otherPrice', 'agentProducts.homePrice', 'agentProducts.officialPrice', 'agentProducts.status')
                    ->leftJoin('products', 'products.id', '=', 'agentProducts.productId');


                if ($request->has('agentId')) {
                    $products = $products->where('agentProducts.agentId', $request->agentId);
                }
                // if($request->has('status')){
                //     $products = $products->where('agentProducts.status',$request->status);
                // }
                // if($request->has('arabicName')){
                //     $products = $products->where('arabicName','like','%'.$request->arabicName.'%');
                // }
                // if($request->has('englishName')){
                //     $products = $products->where('englishName','like','%'.$request->englishName.'%');
                // }

                // if($request->has('pageSize')){
                //     $pageSize = $request->pageSize;
                // }
                if ($request->skip == null) {
                    $request->skip = 0;
                }
                if ($request->take == null) {
                    $request->take = 500;
                }
                $products = $products->skip($request->skip)->take($request->take);
                // $request->sort = json_decode($request->sort);
                // return gettype($request->sort);
                $results = new AgentProduct;
                if ($request->sort != null && gettype($request->sort) == 'array') {
                    // $results->sort = $request->sort [0];
                    foreach ($request->sort as $sortParam) {
                        // $products=$products->orderBy($sortParam['selector'],'desc');
                        $products = $products->orderBy($sortParam['selector'], ($sortParam['desc']) ? 'asc' : 'desc');
                    }
                }
                $products = $products->get();
                // return $products;
                foreach ($products as $product) {
                    $product->picture = url('/') . $product->picture;
                }

                $results->data = $products;
                $results->totalCount = AgentProduct::count('id');
                $results->summary = [];
                $results->request = $data;
                return $this->response(true, 'success', $results);
            }
        } catch (Exception $e) {
            return $this->response(false, 'system error');
        }

    }
}
