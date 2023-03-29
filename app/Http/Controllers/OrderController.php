<?php

namespace App\Http\Controllers;

use App\Http\Resources\Order\MostOrderUser;
use App\Http\Resources\Order\OrderResource as OrderOrderResource;
use App\Http\Resources\OrderResource;
use App\Http\Resources\UserCollection;
use App\Models\Address;
use App\Models\Cart;
use App\Models\CartProduct;
use App\Models\Customer;
use App\Models\DeliveryFlatLocation;
use App\Models\OrderProduct;
use App\Models\AgentProduct;
use App\Models\Order;
use App\Models\PostponeOrderRequest;
use App\Models\User;
use App\Models\Agent;
use App\Models\Setting;
use App\Models\FavoriteProduct;
use App\Models\Points;
use App\Models\Delegator;
use App\Models\Coupon;
use Illuminate\Support\Facades\Validator;
use Illuminate\Http\Request;
use DateTime;
use Carbon\Carbon;
use Illuminate\Validation\Rules\RequiredIf;
use Notification;
use App\Notifications\OrderCreated;
use App\Notifications\OrderCancelled;
use App\Notifications\OrderAssigned;
use App\Notifications\OrderCompleted;
use DB;
use Log;
use App\Http\Resources\OrderCollection;
use App\Http\Resources\OrderReview;
use App\Http\Resources\ReviewCollection;
use App\Models\AgentArea;
use App\Models\Product;
use App\Notifications\OrderOnTheWay;
use Exception;
use Illuminate\Support\Facades\DB as FacadesDB;
use phpDocumentor\Reflection\Types\Collection;

class OrderController extends Controller
{
    public function delete($id)
    {
        try {
            $address = Order::find($id);
            if ($address == null) {
                return $this->response(false, 'id is not found');
            }
            if ($address->delete()) {
                return $this->response(true, 'success');
            } else {
                return $this->response(false, 'failed');
            }
        } catch (Exception $e) {
            return $this->response(false, 'system error');
        }
    }

    //list all products of order
    public function listOrderOfUser(Request $request)
    {
        try {
            $orders = Order::join('users', 'users.id', '=', 'orders.userId')->select('orders.*', 'users.name', 'users.mobile')
                ->where('userId', $request->userId)
                ->orderBy('orders.created_at', 'desc')
                ->get();
            foreach ($orders as $order) {

                if ($order->couponDiscount > 0) {
                    $order->discountType = 'coupon';
                    $order->amount = $order->amount + $order->couponDiscount;
                }
                if ($order->pointsDiscount > 0) {
                    $order->discountType = 'points';
                    $order->amount = $order->amount + $order->pointsDiscount;
                }
                if ($order->couponDiscount > 0 && $order->pointsDiscount > 0) {
                    $order->discountType = 'both';
                }

                if ($order->couponDiscount == 0 && $order->pointsDiscount == 0) {
                    $order->discountType = 'none';
                }
                $order = parent::convertOrderToMobile($order);
            }
            return $this->response(true, 'success', $orders);
        } catch (Exception $e) {
            return $this->response(false, 'system error');
        }
    }

    public function listOrderOfIndustry(Request $request)
    {
        try {
            $orders = Order::join('address', 'address.id', '=', 'orders.addressId')
                ->leftjoin('users', 'users.id', '=', 'orders.userId')
                ->leftjoin('delegators', 'delegators.id', '=', 'orders.delegatorId')
                ->leftjoin('agents', 'agents.id', '=', 'orders.agentId')
                ->select('orders.*', 'agents.name as agentName', 'delegators.name as delegatorName', 'delegators.mobile as delegatorMobile', 'address.type', 'address.lat', 'address.lng', 'users.name as clientName', 'users.mobile as clientMobile')
                ->orderBy('orders.created_at', 'desc')
                ->paginate();

            foreach ($orders as $order) {
                $order = parent::convertOrderToMobile($order);
            }
            return $this->response(true, 'success', $orders);
        } catch (Exception $e) {
            return $this->response(false, 'system error');
        }
    }

    public function listOrderOfIndustryPerType(Request $request)
    {
        try {
            $data = $request->only(['status']);
            $rules = [
                'status' => 'required|in:created,completed,cancelled,delay',
            ];
            $validator = Validator::make($data, $rules);
            if ($validator->fails()) {
                return $this->response(false, $this->validationHandle($validator->messages()));
            } else {

                if ($request->status == 'created') {
                    $orders = Order::leftjoin('address', 'address.id', '=', 'orders.addressId')
                        ->leftjoin('users', 'users.id', '=', 'orders.userId')
                        ->leftjoin('delegators', 'delegators.id', '=', 'orders.delegatorId')
                        ->leftjoin('agents', 'agents.id', '=', 'orders.agentId')
                        ->select(
                            'orders.*',
                            'agents.name as agentName',
                            'delegators.name as delegatorName',
                            'delegators.mobile as delegatorMobile',
                            'address.type',
                            'address.lat',
                            'address.lng',
                            'users.name as clientName',
                            'users.mobile as clientMobile'
                        )
                        ->orderBy('orders.created_at', 'desc')
                        ->where('orders.status', $request->status)
                        // ->whereNull('delegatorId')
                        ->paginate();
                }
                if ($request->status == 'completed') {
                    $orders = Order::join('address', 'address.id', '=', 'orders.addressId')
                        ->leftjoin('users', 'users.id', '=', 'orders.userId')
                        ->leftjoin('delegators', 'delegators.id', '=', 'orders.delegatorId')
                        ->leftjoin('agents', 'agents.id', '=', 'orders.agentId')
                        ->select(
                            'orders.*',
                            'agents.name as agentName',
                            'delegators.name as delegatorName',
                            'delegators.mobile as delegatorMobile',
                            'address.type',
                            'address.lat',
                            'address.lng',
                            'users.name as clientName',
                            'users.mobile as clientMobile'
                        )
                        ->orderBy('orders.created_at', 'desc')
                        ->where('orders.status', $request->status)
                        ->paginate();
                }
                if ($request->status == 'cancelled') {
                    $orders = Order::join('address', 'address.id', '=', 'orders.addressId')
                        ->leftjoin('users', 'users.id', '=', 'orders.userId')
                        ->leftjoin('delegators', 'delegators.id', '=', 'orders.delegatorId')
                        ->leftjoin('agents', 'agents.id', '=', 'orders.agentId')
                        ->select(
                            'orders.*',
                            'agents.name as agentName',
                            'delegators.name as delegatorName',
                            'delegators.mobile as delegatorMobile',
                            'address.type',
                            'address.lat',
                            'address.lng',
                            'users.name as clientName',
                            'users.mobile as clientMobile'
                        )
                        ->orderBy('orders.created_at', 'desc')
                        ->whereIn('orders.status', ['cancelledByClient', 'cancelledByApp'])
                        ->paginate();
                }
                if ($request->status == 'delay') {
                    //$order->created_at->addDays(2)> Carbon::now()
                    $orders = Order::join('address', 'address.id', '=', 'orders.addressId')
                        ->leftjoin('users', 'users.id', '=', 'orders.userId')
                        ->leftjoin('delegators', 'delegators.id', '=', 'orders.delegatorId')
                        ->leftjoin('agents', 'agents.id', '=', 'orders.agentId')
                        ->select(
                            'orders.*',
                            'agents.name as agentName',
                            'delegators.name as delegatorName',
                            'delegators.mobile as delegatorMobile',
                            'address.type',
                            'address.lat',
                            'address.lng',
                            'users.name as clientName',
                            'users.mobile as clientMobile'
                        )
                        ->orderBy('orders.created_at', 'desc')
                        ->where('orders.status', 'created')->where('orders.created_at', '<', Carbon::now()->subDays(2))
                        ->paginate();
                }
            }


            foreach ($orders as $order) {
                $order = parent::convertOrderToMobile($order);
            }
            return $this->response(true, 'success', $orders);
        } catch (Exception $e) {
            return $this->response(false, 'system error');
        }
    }

    public function getProductsOfOrder(Request $request)
    {
        \Carbon\Carbon::setLocale('ar');


        try {
            $data = $request->only(['orderId']);
            $rules = [
                'orderId' => 'required|numeric',
            ];
            $validator = Validator::make($data, $rules);
            if ($validator->fails()) {
                return $this->response(false, $this->validationHandle($validator->messages()));
            } else {
                $order = Order::find($request->orderId);
                if ($order == null) {
                    return $this->response(false, 'invalid orderId');
                }
                $products = OrderProduct::select(
                    'orderProducts.id',
                    'orderProducts.productId',
                    'orderProducts.orderId',
                    'products.arabicName',
                    'products.englishName',
                    'products.picture',
                    'products.mosquePrice',
                    'products.otherPrice',
                    'products.homePrice',
                    'products.officialPrice',
                    'orderProducts.amount'
                )
                    ->leftJoin('products', 'products.id', '=', 'orderProducts.productId')
                    ->where('orderId', $request->orderId)->get();
                $favoriteProducts = FavoriteProduct::where('userId', $order->userId)->get(); //->pluck('productId');

                $agentProducts = AgentProduct::where('agentId', $order->agentId)->get();
                $quantity = 0;
                foreach ($products as $product) {
                    $product->picture = url('/') . $product->picture;
                    if ($favoriteProducts->contains('productId', $product->productId)) {
                        $product->isFavorite = 1;
                    } else {
                        $product->isFavorite = 0;
                    }

                    // search in list
                    $temp = $agentProducts->where('productId', $product->productId)->first();                    
                    if ($temp == null) {
                        $product->mosquePrice = 0;
                        $product->otherPrice = 0;
                        $product->homePrice = 0;
                        $product->officialPrice = 0;
                    } else {
                        $product->mosquePrice = $temp->mosquePrice;
                        $product->otherPrice = $temp->otherPrice;
                        $product->homePrice = $temp->homePrice;
                        $product->officialPrice = $temp->officialPrice;
                    }
                    $quantity = $quantity + $product->amount;
                }
                $order->productCount = $quantity;
                $order->totalPrice = $order->amount;

                $order->deliveryWhenDate = Carbon::parse($order->deliveryDate)->diffForHumans();

                $order->city = $order->city->arabicName ?? '-';
                $order->region = $order->region->arabicName ?? '-';
                $order->district = $order->district->arabicName ?? '-';

                $order->products = $products;

                unset($order->region_id);
                unset($order->city_id);
                unset($order->deliveryDateX);
                unset($order->district_id);
                return $this->response(true, 'success', $order);
            }
        } catch (Exception $e) {
            return $this->response(false, $e->getMessage());
        }
    }

    public function listOrdersOfAgentInAgentApp(Request $request)
    {

        try {
            $data = $request->only(['agentId', 'status']);
            $rules = [
                'agentId' => 'required|numeric',
                'status' => 'required|in:created,completed,cancelled,assigned,delay',
            ];
            $validator = Validator::make($data, $rules);
            if ($validator->fails()) {
                return $this->response(false, $this->validationHandle($validator->messages()));
            } else {

                $agent = Agent::find($request->agentId);

                // return $agentProducts ;
                if ($agent == null) {
                    return $this->response(false, 'not valid agent');
                }
                $agentProducts = AgentProduct::where('agentId', $request->agentId)->get();

                if ($request->status == 'created') {
                    $orders = Order::leftjoin('address', 'address.id', '=', 'orders.addressId')
                        ->leftjoin('users', 'users.id', '=', 'orders.userId')
                        ->leftjoin('delegators', 'delegators.id', '=', 'orders.delegatorId')
                        ->select('orders.*', 'delegators.name as delegatorName', 'delegators.mobile as delegatorMobile', 'address.type', 'address.lat', 'address.lng', 'users.name as clientName', 'users.mobile as clientMobile')
                        ->where('orders.agentId', $request->agentId)
                        ->orderBy('orders.created_at', 'desc')
                        ->where('orders.status', $request->status)
                        // ->whereNull('delegatorId')
                        ->paginate();
                }
                if ($request->status == 'completed') {
                    $orders = Order::leftjoin('address', 'address.id', '=', 'orders.addressId')
                        ->leftjoin('users', 'users.id', '=', 'orders.userId')
                        ->leftjoin('delegators', 'delegators.id', '=', 'orders.delegatorId')
                        ->select('orders.*', 'delegators.name as delegatorName', 'delegators.mobile as delegatorMobile', 'address.type', 'address.lat', 'address.lng', 'users.name as clientName', 'users.mobile as clientMobile')
                        ->where('orders.agentId', $request->agentId)
                        ->orderBy('orders.created_at', 'desc')
                        ->where('orders.status', $request->status)
                        ->paginate();
                }
                if ($request->status == 'cancelled') {
                    $orders = Order::leftjoin('address', 'address.id', '=', 'orders.addressId')
                        ->leftjoin('users', 'users.id', '=', 'orders.userId')
                        ->leftjoin('delegators', 'delegators.id', '=', 'orders.delegatorId')
                        ->select('orders.*', 'delegators.name as delegatorName', 'delegators.mobile as delegatorMobile', 'address.type', 'address.lat', 'address.lng', 'users.name as clientName', 'users.mobile as clientMobile')
                        ->where('orders.agentId', $request->agentId)
                        ->orderBy('orders.created_at', 'desc')
                        ->whereIn('orders.status', ['cancelledByClient', 'cancelledByApp'])
                        ->paginate();
                }
                if ($request->status == 'delay') {
                    $orders = Order::leftjoin('address', 'address.id', '=', 'orders.addressId')
                        ->leftjoin('users', 'users.id', '=', 'orders.userId')
                        ->leftjoin('delegators', 'delegators.id', '=', 'orders.delegatorId')
                        ->select('orders.*', 'delegators.name as delegatorName', 'delegators.mobile as delegatorMobile', 'address.type', 'address.lat', 'address.lng', 'users.name as clientName', 'users.mobile as clientMobile')
                        ->where('orders.agentId', $request->agentId)
                        ->orderBy('orders.created_at', 'desc')
                        ->where('orders.status', 'created')
                        ->where('orders.status', 'created')
                        ->where('orders.created_at', '<', Carbon::now()->subDays(2))
                        ->paginate();
                }
                if ($request->status == 'assigned') {

                    $orders = Order::leftjoin('address', 'address.id', '=', 'orders.addressId')
                        ->leftjoin('users', 'users.id', '=', 'orders.userId')
                        ->leftjoin('delegators', 'delegators.id', '=', 'orders.delegatorId')
                        ->select('orders.*', 'delegators.name as delegatorName', 'delegators.mobile as delegatorMobile', 'address.type', 'address.lat', 'address.lng', 'users.name as clientName', 'users.mobile as clientMobile')
                        ->where('orders.agentId', $request->agentId)
                        ->orderBy('orders.created_at', 'desc')
                        ->where('orders.status', 'created')->whereNotNull('delegatorId')
                        ->paginate();
                }
                foreach ($orders as $order) {
                    $order->agentName = $agent->name;
                    $order = parent::convertOrderToMobile($order);
                }

                return $this->response(true, 'success', $orders);
            }
        } catch (Exception $e) {
            return $this->response(false, 'system error');
        }
    }

    public function listOrdersOfAgent(Request $request)
    {
        $data = $request->only(['agentId', 'status']);
        $rules = [
            'agentId' => 'required|numeric',
            'status' => 'required|in:created,completed,cancelled,assigned,delay',
        ];
        $validator = Validator::make($data, $rules);
        if ($validator->fails()) {
            return $this->response(false, $this->validationHandle($validator->messages()));
        }
        try {

            $agent = Agent::find($request->agentId);
            // return $agentProducts ;
            if ($agent == null) {
                return $this->response(false, 'not valid agent');
            }

            if ($request->status == 'created') {
                $orders = Order::leftjoin('address', 'address.id', '=', 'orders.addressId')
                    ->leftjoin('users', 'users.id', '=', 'orders.userId')
                    ->leftjoin('delegators', 'delegators.id', '=', 'orders.delegatorId')
                    ->select('orders.*', 'delegators.name as delegatorName', 'delegators.mobile as delegatorMobile', 'address.type', 'address.lat', 'address.lng', 'users.name as clientName', 'users.mobile as clientMobile')
                    ->where('orders.agentId', $request->agentId)
                    ->orderBy('orders.created_at', 'desc')
                    ->where('orders.status', $request->status)
                    ->whereNull('delegatorId')
                    ->paginate();
            }
            if ($request->status == 'completed') {
                $orders = Order::leftjoin('address', 'address.id', '=', 'orders.addressId')
                    ->leftjoin('users', 'users.id', '=', 'orders.userId')
                    ->leftjoin('delegators', 'delegators.id', '=', 'orders.delegatorId')
                    ->select('orders.*', 'delegators.name as delegatorName', 'delegators.mobile as delegatorMobile', 'address.type', 'address.lat', 'address.lng', 'users.name as clientName', 'users.mobile as clientMobile')
                    ->where('orders.agentId', $request->agentId)
                    ->orderBy('orders.created_at', 'desc')
                    ->where('orders.status', $request->status)
                    ->paginate();
            }
            if ($request->status == 'cancelled') {
                $orders = Order::leftjoin('address', 'address.id', '=', 'orders.addressId')
                    ->leftjoin('users', 'users.id', '=', 'orders.userId')
                    ->leftjoin('delegators', 'delegators.id', '=', 'orders.delegatorId')
                    ->select('orders.*', 'delegators.name as delegatorName', 'delegators.mobile as delegatorMobile', 'address.type', 'address.lat', 'address.lng', 'users.name as clientName', 'users.mobile as clientMobile')
                    ->where('orders.agentId', $request->agentId)
                    ->orderBy('orders.created_at', 'desc')
                    ->whereIn('orders.status', ['cancelledByClient', 'cancelledByApp'])
                    ->paginate();
            }
            if ($request->status == 'delay') {
                $orders = Order::leftjoin('address', 'address.id', '=', 'orders.addressId')
                    ->leftjoin('users', 'users.id', '=', 'orders.userId')
                    ->leftjoin('delegators', 'delegators.id', '=', 'orders.delegatorId')
                    ->select('orders.*', 'delegators.name as delegatorName', 'delegators.mobile as delegatorMobile', 'address.type', 'address.lat', 'address.lng', 'users.name as clientName', 'users.mobile as clientMobile')
                    ->where('orders.agentId', $request->agentId)
                    ->orderBy('orders.created_at', 'desc')
                    ->where('orders.status', 'created')
                    ->where('orders.status', 'created')
                    ->where('orders.created_at', '<', Carbon::now()->subDays(2))
                    ->paginate();
            }
            if ($request->status == 'assigned') {

                $orders = Order::leftjoin('address', 'address.id', '=', 'orders.addressId')
                    ->leftjoin('users', 'users.id', '=', 'orders.userId')
                    ->leftjoin('delegators', 'delegators.id', '=', 'orders.delegatorId')
                    ->select('orders.*', 'delegators.name as delegatorName', 'delegators.mobile as delegatorMobile', 'address.type', 'address.lat', 'address.lng', 'users.name as clientName', 'users.mobile as clientMobile')
                    ->where('orders.agentId', $request->agentId)
                    ->orderBy('orders.created_at', 'desc')
                    ->where('orders.status', 'created')->whereNotNull('delegatorId')
                    ->paginate();
            }
            foreach ($orders as $order) {
                $order->agentName = $agent->name;
                $order = parent::convertOrderToMobile($order);
            }

            return $this->response(true, 'success', $orders);
        } catch (Exception $e) {
            return $this->response(false, 'system error');
        }
    }

    public function postponeOrders(Request $request)
    {
        $data = $request->only([
            'agent_ids',
            'order_id',
            'status',
            'from',
            'to',

        ]);
        $rules = [
            'agent_ids' => 'nullable|min:1',
            'order_id' => 'nullable|numeric|exists:orders,id',
            'status' => 'nullable',
            'from' => 'nullable',
            'to' => 'nullable',
        ];
        $validator = Validator::make($data, $rules);
        if ($validator->fails()) {
            return $this->newResponse(false, $this->validationHandle($validator->messages()));
        }

        try {
            $orders = PostponeOrderRequest::with('reason', 'delegator', 'order');
            if (!empty($request->agent_ids)) {
                $orders = $orders->whereHas('order', function ($q) use ($request) {
                    $q->whereIn('agentId', $request->agent_ids);
                });
            }
            if ($request->order_id) {
                $orders = $orders->where('order_id', 'like', '%' . $request->order_id . '%');
            }

            if ($request->status) {
                $orders = $orders->where('status', $request->status);
            }
            if ($request->from != null && $request->to != null) {
                $orders = $orders->whereBetween('created_at', [$request->from, $request->to]);
            }
            $orders = $orders->simplePaginate(10);
            return $this->newResponse(true, __('api.success_response'), 'orders', $orders);
        } catch (\Exception $e) {
            return $this->newResponse(false, $e->getMessage());
        }
    }

    public function agentPostponeOrderDate(Request $request)
    {
        $data = $request->only(['agent_id', 'date', 'order_id']);
        $rules = [
            'agent_id' => 'required|numeric|exists:agents,id',
            'order_id' => 'required|numeric|exists:orders,id',
            'date' => 'required',
        ];
        $validator = Validator::make($data, $rules);
        if ($validator->fails()) {
            return $this->newResponse(false, $this->validationHandle($validator->messages()));
        }
        try {

            $agent = Agent::find($request->agent_id);
            if ($agent) {
                $order = $agent->orders()->find($request->order_id);
                if ($order) {
                    if ($order->delivery_date == 'immediately') {
                        $order->assignDate = Carbon::parse($request->date)->format('Y-m-d');
                        $order->save();
                    } elseif ($order->delivery_date == 'schedule') {
                        $order->delivery_schedule_date = Carbon::parse($request->date)->format('Y-m-d');
                        $order->save();
                    }
                    return $this->response(true, __('api.success_response'));
                } else {
                    return $this->response(false, __('api.not_exist_order'));
                }
            } else {
                return $this->response(false, __('api.not_exist_order'));
            }
        } catch (\Exception $e) {
            Log::error('agent postpone order  ' . $e->getMessage());
            return $this->response(false, $e->getMessage());
        }
    }

    public function newListOrdersOfAgent(Request $request)
    {
        $data = $request->only(['agentId', 'status']);
        $rules = [
            'agentId' => 'required|numeric|exists:agents,id',
            'status' => 'required|in:created,completed,cancelled,assigned,delay,on_the_way',
            'carton' => 'nullable|in:high,low',
            'order_by' => 'nullable|in:new,old'
        ];
        $validator = Validator::make($data, $rules);
        if ($validator->fails()) {
            return $this->newResponse(false, $this->validationHandle($validator->messages()));
        }
        try {

            $agent = Agent::find($request->agentId);
            if ($agent) {
                $orders = $agent->orders();
                switch ($request->status) {

                    case "cancelled":
                        $orders = $orders->whereIn('status', ['cancelledByClient', 'cancelledByApp']);
                        break;
                    case "delay":
                        $orders = $orders->where(function ($q) {
                            $q->where(function ($qq) {
                                $qq->where('status', 'created')
                                    ->where('delivery_date', 'immediately')
                                    ->where('created_at', '<', Carbon::now()->subDays(2));
                            })->orWhere(function ($qq) {
                                $qq->where('status', 'created')
                                    ->where('delivery_date', 'schedule')
                                    ->where('delivery_schedule_date', '<', Carbon::now()->subDays(2));
                            });
                        });
                        break;
                    case "assigned":
                        $orders = $orders->where(function ($query) {
                            $query->where('status', 'assigned')
                                ->orWhere('status', 'on_the_way');
                        })->whereNotNull('delegatorId');
                        break;
                    default:
                        $orders = $orders->where('status', $request->status);
                }

                if (!empty($request->carton)) {
                    $orders = $orders->withCount(['orderproducts as carton_count' => function ($query) {
                        $query->select(DB::raw('sum(amount)'));
                    }]);

                    if ($request->carton == 'high') {
                        $orders = $orders->orderBy('carton_count', 'desc');
                    }
                    if ($request->carton == 'low') {
                        $orders = $orders->orderBy('carton_count', 'asc');
                    }
                }
                if (!empty($request->order_by)) {
                    if ($request->order_by == 'new') {
                        $orders = $orders->orderBy('id', 'desc');
                    }
                    if ($request->order_by == 'old') {
                        $orders = $orders->orderBy('id', 'asc');
                    }
                }
                $orders = new \App\Http\Resources\Order\OrderCollection(
                    $orders->paginate(10)
                );
                return $this->newResponse(true, __('api.success_response'), '', [], [
                    'data' => $orders,
                ]);
            }
        } catch (Exception $e) {
            return $this->response(false, 'system error');
        }
    }

    public function agentDelegatorReport(Request $request)
    {
        $data = $request->only(['agent_ids', 'delegator_ids', 'from_date', 'to_date']);
        $rules = [
            'agent_ids' => 'required|min:1',
            'delegator_ids' => 'nullable',
            'from_date' => 'nullable',
            'to_date' => 'nullable',
            //            'status' => 'required|in:created,completed,cancelled,assigned,delay',
        ];
        $validator = Validator::make($data, $rules);
        if ($validator->fails()) {
            return $this->newResponse(false, $this->validationHandle($validator->messages()));
        }
        try {

            if ($request->agent_ids) {
                $orders = Order::whereIn('agentId', $request->agent_ids);
                $orders = $orders->where('status', 'completed')->whereNotNull('completionDate');
                if ($request->delegator_ids) {
                    $orders = $orders->whereIn('delegatorId', $request->delegator_ids);
                }
                if ($request->from_date && $request->to_date) {
                    $orders = $orders->whereBetween('completionDate', [$request->from_date, $request->to_date]);
                }

                $orders = new \App\Http\Resources\Order\OrderCollection(
                    $orders->orderByDesc('id', 'desc')->paginate(10)
                );
                return $this->newResponse(true, __('api.success_response'), '', [], [
                    'orders' => $orders,
                ]);
            }

            //            foreach ($orders as $order) {
            //                $order->agentName= $agent->name;
            //                $order = parent::convertOrderToMobile($order);
            //            }

        } catch (Exception $e) {
            return $this->response(false, 'system error');
        }
    }

    public function agentDelegatorAssignedReport(Request $request)
    {
        $data = $request->only(['agent_ids', 'delegator_ids', 'from_date', 'to_date']);
        $rules = [
            'agent_ids' => 'required|min:1',
            'delegator_ids' => 'nullable',
            'from_date' => 'nullable',
            'to_date' => 'nullable',
            //            'status' => 'required|in:created,completed,cancelled,assigned,delay',
        ];
        $validator = Validator::make($data, $rules);
        if ($validator->fails()) {
            return $this->newResponse(false, $this->validationHandle($validator->messages()));
        }
        try {
            //            $agent = Agent::find($request->agent_id);
            if ($request->agent_ids) {
                $orders = Order::whereIn('agentId', $request->agent_ids);;
                $orders = $orders->where('status', 'assigned')->whereNotNull('assignDate')
                    ->whereNull('completionDate');
                if (!empty($request->delegator_ids)) {
                    $orders = $orders->whereIn('delegatorId', $request->delegator_ids);
                }
                if ($request->from_date && $request->to_date) {
                    $orders = $orders->whereBetween('assignDate', [$request->from_date, $request->to_date]);
                }

                $orders = new \App\Http\Resources\Order\OrderCollection(
                    $orders->orderByDesc('id', 'desc')->paginate(10)
                );
                return $this->newResponse(true, __('api.success_response'), '', [], [
                    'orders' => $orders,
                ]);
            }

            //            foreach ($orders as $order) {
            //                $order->agentName= $agent->name;
            //                $order = parent::convertOrderToMobile($order);
            //            }

        } catch (Exception $e) {
            return $this->response(false, 'system error');
        }
    }

    public function listOrdersOfAgentFromPortal(Request $request)
    {

        try {
            $data = $request->only(['agentId']);
            $rules = [
                'agentId' => 'required|numeric',
            ];
            $validator = Validator::make($data, $rules);
            if ($validator->fails()) {
                return $this->response(false, $this->validationHandle($validator->messages()));
            } else {

                $agent = Agent::find($request->agentId);

                // return $agentProducts ;
                if ($agent == null) {
                    return $this->response(false, 'not valid agent');
                }
                $agentProducts = AgentProduct::where('agentId', $request->agentId)->get();

                $orders = Order::join('address', 'address.id', '=', 'orders.addressId')
                    ->leftjoin('users', 'users.id', '=', 'orders.userId')
                    ->leftjoin('delegators', 'delegators.id', '=', 'orders.delegatorId')
                    ->select('orders.*', 'delegators.name as delegatorName', 'delegators.mobile as delegatorMobile', 'address.type', 'address.lat', 'address.lng', 'users.name as clientName', 'users.mobile as clientMobile')
                    ->where('orders.agentId', $request->agentId)
                    ->orderBy('orders.created_at', 'desc')
                    ->paginate(100);

                foreach ($orders as $order) {
                    $order->agentName = $agent->name;
                    $order = parent::convertOrderToMobile($order);
                }

                return $this->response(true, 'success', $orders);
            }
        } catch (Exception $e) {
            return $this->response(false, 'system error');
        }
    }

    public function listOrderOfDelegator(Request $request)
    {

        try {
            $data = $request->only(['delegatorId', 'status']);
            $rules = [
                'delegatorId' => 'required|numeric',
                'status' => 'required|in:completed,cancelled,assigned',

            ];
            $validator = Validator::make($data, $rules);
            if ($validator->fails()) {
                return $this->response(false, $this->validationHandle($validator->messages()));
            } else {

                $delegator = Delegator::find($request->delegatorId);
                if ($delegator == null) {
                    return $this->response(false, 'not valid delegator');
                }
                $agentProducts = AgentProduct::where('agentId', $delegator->agentId)->get();


                if ($request->status == 'cancelled') {
                    $orders = Order::join('address', 'address.id', '=', 'orders.addressId')
                        ->leftjoin('users', 'users.id', '=', 'orders.userId')
                        ->select('orders.*', 'users.name as clientName', 'users.mobile as clientMobile', 'address.type', 'address.lat', 'address.lng')
                        ->where('delegatorId', $request->delegatorId)
                        ->whereIn('orders.status', ['cancelledByClient', 'cancelledByApp'])
                        ->orderBy('orders.created_at', 'desc')
                        ->paginate();
                }

                if ($request->status == 'completed') {
                    $orders = Order::join('address', 'address.id', '=', 'orders.addressId')
                        ->leftjoin('users', 'users.id', '=', 'orders.userId')
                        ->select('orders.*', 'users.name as clientName', 'users.mobile as clientMobile', 'address.type', 'address.lat', 'address.lng')
                        ->where('delegatorId', $request->delegatorId)
                        ->where('orders.status', $request->status)
                        ->orderBy('orders.created_at', 'desc')
                        ->paginate();
                }

                if ($request->status == 'assigned') {
                    $orders = Order::join('address', 'address.id', '=', 'orders.addressId')
                        ->leftjoin('users', 'users.id', '=', 'orders.userId')
                        ->select('orders.*', 'users.name as clientName', 'users.mobile as clientMobile', 'address.type', 'address.lat', 'address.lng')
                        ->where('delegatorId', $request->delegatorId)
                        ->where('orders.status', 'created')->whereNotNull('delegatorId')
                        ->orderBy('orders.created_at', 'desc')
                        ->paginate();
                }
                foreach ($orders as $order) {
                    $order = parent::convertOrderToMobile($order);
                }

                return $this->response(true, 'success', $orders);
            }
        } catch (Exception $e) {
            return $this->response(false, 'system error');
        }
    }

    public function listNewOrderOfDelegator(Request $request)
    {
        $data = $request->only(['delegator_id', 'status']);
        $rules = [
            'delegator_id' => 'required|numeric|exists:delegators,id',
            'status' => 'required|in:completed,cancelled,assigned,on_the_way',

        ];
        $validator = Validator::make($data, $rules);
        if ($validator->fails()) {
            return $this->response(false, $this->validationHandle($validator->messages()));
        }
        try {

            $delegatore = Delegator::find($request->delegator_id);
            if ($delegatore) {
                $orders = $delegatore->orders();
                switch ($request->status) {
                    case "cancelled":
                        $orders = $orders->whereIn('status', ['cancelledByClient', 'cancelledByApp']);
                        break;
                    case "delay":
                        $orders = $orders->where(function ($q) {
                            $q->where(function ($qq) {
                                $qq->where('status', 'created')
                                    ->where('delivery_date', 'immediately')
                                    ->where('created_at', '<', Carbon::now()->subDays(2));
                            })->orWhere(function ($qq) {
                                $qq->where('status', 'created')
                                    ->where('delivery_date', 'schedule')
                                    ->where('delivery_schedule_date', '<', Carbon::now()->subDays(2));
                            });
                        });
                        break;
                    case "assigned":
                        $orders = $orders->where('status', 'assigned');
                        break;
                    default:
                        $orders = $orders->where('status', $request->status);
                }


                $orders = new \App\Http\Resources\Order\OrderCollection(
                    $orders->orderByDesc('id', 'desc')->paginate(10)
                );
                return $this->newResponse(true, __('api.success_response'), 'data', $orders);
            }
            return $this->response(false, __('api.fails_response'));
        } catch (\Exception $e) {
            return $this->response(false, __('api.fails_response'));
        }
    }

    public function listAllOrders(Request $request)
    {

        $orders = new Order();
        $orders_count = $orders->count();
        $orders = new \App\Http\Resources\Order\OrderCollection($orders->orderBy('id', 'desc')->paginate($request->get('perPage', '20')));
        return $this->newResponse(true, __('api.success_response'), '', [], [
            'total_orders' => $orders_count,
            'orders' => $orders,
        ]);
    }

    // create order from mobile app
    public function create(Request $request)
    {

        try {
            $data = $request->only(['userId', 'cartId', 'addressId', 'amount', 'coupon', 'points', 'paymentReference', 'city_id', 'district_id', 'region_id', 'region_id', 'deliveryTime', 'preorder', 'deliveryLocation', 'deliveryTimePeriod', 'deliveryDate']);
            $rules = [
                'userId' => 'required|numeric',
                'cartId' => 'required|numeric',
                'addressId' => 'required|numeric',
                'amount' => 'required|numeric',
                'city_id' => 'required|numeric',
                'district_id' => 'required|numeric',
                'region_id' => 'required|numeric',
                'deliveryTime' => 'required',
                'deliveryDate' => 'required|date',
                'preorder' => 'required|numeric',
                'deliveryLocation' => 'required|in:ground,upstairs',
                'deliveryTimePeriod' => 'required|in:morning,evening,any',
                // 'points' => 'numeric',
                // 'coupon' => 'required',
            ];
            $messages = array(
                'city_id.required' => 'City is required.',
                'district_id.required' => 'district is required.',
                'region_id.required' => 'region is required.',
                'preorder.required' => 'pre-order is required.',
            );
            $validator = Validator::make($data, $rules, $messages);
            if ($validator->fails()) {
                return $this->response(false, $this->validationHandle($validator->messages()));
            } else {
                $sameAgent = true;
                $pointsRecord = new Points();
                // check user id
                $user = User::find($data['userId']);
                if ($user == null) {
                    return $this->response(false, 'not valid user id');
                }

                if ($request->points != null) {


                    $request->points = $this->convert($request->points);
                    $min_points_to_replace = Setting::where('name', 'min_points_to_replace')->first()->value;
                    if ($request->points < $min_points_to_replace) {
                        if ($user->language == 'en') {
                            return $this->response(false, 'you can replace ' . $min_points_to_replace . ' points atleast');
                        } else {
                            return $this->response(false, ' عفوا لا تستطيع استبدال اقل من ' . $min_points_to_replace . ' نقطة ');
                        }
                    }

                    if ($request->points % 10 != 0) {
                        if ($user->language == 'ar') {
                            return $this->response(false, 'عدد النقاط یجب ان یكون من مضاعفات الرقم ١٠');
                        } else {
                            return $this->response(false, 'The number of points must be a multiple of 10');
                        }
                    }
                }

                // check address id
                $address = Address::where('id', $data['addressId'])->where('userId', $data['userId'])->first();
                // return $address;
                if ($address == null) {
                    return $this->response(false, 'not valid address');
                }

                // check if address is supported or no
                $locationAgent = new Agent;
                $locationAgent = $locationAgent->search($address->lat, $address->lng);
                if ($locationAgent == null) {
                    if ($user->language == 'en') {
                        return $this->response(false, 'not supported address, please call 920024484');
                    } else {
                        return $this->response(false, '  الموقع غير مدعوم، الرجاء التواصل مع الرقم الموحد 920024484');
                    }
                }

                DB::beginTransaction();


                $cartId = $data['cartId'];
                $cart = Cart::find($cartId);
                if ($cart == null) {
                    return $this->response(false, 'not valid cart id');
                } else {

                    $agent = Agent::find($cart->agentId);
                    if ($agent == null) {
                        return $this->response(false, 'not valid agent id');
                    } else {

                        $cartCount = CartProduct::where('cartId', $cartId)->sum('amount');
                        // Log::info($cartCount);
                        // Log::info($locationAgent->minimum_cartons);
                        // check minimum cartoons

                        if ($cartCount < $locationAgent->minimum_cartons) {
                            if ($user->language == 'en') {
                                return $this->response(false, 'minimum order cartoons is ' . $locationAgent->minimum_cartons);
                            } else {
                                return $this->response(false, ' الحد الأدنى لعدد المنتجات هو ' . $locationAgent->minimum_cartons . ' كرتون ');
                            }
                        }

                        // check if cart agent is same of address agent
                        if ($agent->id != $locationAgent->id) {
                            // check if agent has all products in cart
                            $cartProducts = CartProduct::where('cartId', $cartId)->pluck('productId');
                            $agentProducts = AgentProduct::where('agentId', $locationAgent->id)->where('status', 1)->whereIn('productId', $cartProducts)->pluck('productId');

                            if (count($agentProducts) != count($cartProducts)) {
                                // some of products are not supported in new agent
                                if ($user->language == 'en') {
                                    return $this->response(false, ' some of products are not available in selected location, please call number 920024484');
                                } else {
                                    return $this->response(false, 'بعض المنتجات غير متوفرة بالعنوان المحدد، الرجاء التواصل مع الرقم الموحد 920024484 ');
                                }
                            }


                            $cart->agentId = $locationAgent->id;
                            $cart->save();
                            $sameAgent = false;
                        }
                    }


                    if ($cart->addressType != $address->type) {
                        // remove cart with addressType = address->type
                        Cart::where('userId', $request->userId)->where('addressType', $address->type)->delete();
                        // replace address type in cart
                        $result = Cart::where('userId', $request->userId)->where('addressType', $cart->addressType)
                            ->update(['addressType' => $address->type]);
                        $cart->addressType = $address->type;
                        // calculate amount and update it in cart
                    }


                    $agentProducts = AgentProduct::where('agentId', $cart->agentId)->get();
                    $products = CartProduct::where('cartId', $cartId)->get();
                    if ($products == null) {
                        return $this->response(false, 'not valid cart id');
                    } else {
                        $get_product_sum_types = collect($products)->sum('type');
                        $count_products = count($products);

                        $all_offers = false;
                        if (($count_products * 2) == $get_product_sum_types)
                            $all_offers = true;

                        $couponDiscount = 0;
                        $pointsDiscount = 0;
                        $discount = 0;
                        // $couponDiscount = 0;
                        // check if Coupon is valid
                        if ($request->coupon != null) {
                            $coupon = Coupon::where('code', $data['coupon'])
                                ->where('status', 1)
                                ->where('notBefore', '<=', Carbon::now())->where('notAfter', '>=', Carbon::now())->first();
                            if ($coupon != null) {

                                if ($coupon->minAmount > $request->amount) {
                                    if ($user->language == 'ar') {
                                        return $this->response(false, ' لاستخدام كود الخصم، يجب أن تكون تكلفة الطلب أكثر من  ' . $coupon->minAmount);
                                    }
                                    return $this->response(false, 'minimum amount to use this coupon code should be ' . $coupon->minAmount);
                                }
                                if ($coupon->type == 'flat') {
                                    $couponDiscount = $coupon->value;
                                } else {
                                    if ($coupon->value <= 1) // value  = percentage
                                        $couponDiscount = $data['amount'] * $coupon->value;
                                }
                            } else {
                                if ($user->language == 'ar')
                                    return $this->response(false, 'كود الخصم غير صحيح');

                                return $this->response(false, 'not valid coupon');
                            }
                        }
                        // check points
                        if ($request->points > 0 && !$all_offers) {
                            // check user point
                            $user->points = $this->getPointsOfUser($user);
                            // Log::info($request->points);
                            // Log::info($user->points);
                            if ($user->points < $request->points) {
                                if ($user->language == 'ar')
                                    return $this->response(false, 'رصيد النقاط غير كافي');

                                return $this->response(false, "You don't have enough points");
                            }
                            $replace_points = Setting::where('name', 'replace_points')->first()->value;
                            $pointsDiscount = ($request->points / $replace_points);
                            $discount = $couponDiscount + $pointsDiscount;
                        } else {
                            $discount = $couponDiscount;
                        }


                        // create new order from cart
                        $order = [
                            'userId' => $data['userId'],
                            'deliveryTime' => $data['deliveryTime'] ?? null,
                            'preorder' => $data['preorder'] ?? null,
                            'deliveryLocation' => $data['deliveryLocation'] ?? null,
                            'deliveryDate' => $data['deliveryDate'] ?? null,
                            'deliveryTimePeriod' => $data['deliveryTimePeriod'] ?? null,
                            'agentId' => $cart->agentId,
                            'addressType' => $cart->addressType,
                            // 'deliveryDate' => Carbon::parse($data['deliveryDate']),
                            'addressId' => $data['addressId'] ?? null,
                            'coupon' => $request->coupon,
                            'points' => $request->points,
                            'city_id' => $request->city_id ?? null,
                            'district_id' => $request->district_id ?? null,
                            'region_id' => $request->region_id ?? null,
                            'couponDiscount' => $couponDiscount,
                            'pointsDiscount' => $pointsDiscount,
                            'amount' => $data['amount'] - $discount
                        ];

                        if ($request->paymentReference != null) {
                            $order['paymentReference'] = $request->paymentReference;
                        }
                        $newOrder = Order::create($order);
                        if ($newOrder != null) {
                            // decreaase points of user
                            if ($request->points > 0 && !$all_offers) {
                                $user->points = $user->points - $request->points;
                                $user->save();
                                // send notification that user use some points
                                $this->sendNotification($user->fcmToken, 'App\Notifications\PointUsed', $user->language);
                            }
                            $pointsRecord->clientId = $newOrder->userId;
                            $pointsRecord->type = 'discount';
                            $pointsRecord->points = $request->points;
                            $pointsRecord->orderId = $newOrder->id;
                            $pointsRecord->agentId = $newOrder->agentId;


                            $newProducts = [];
                            // add products to order
                            $totalCost = 0;
                            // return $this->response(false,$products.'');

                            foreach ($products as $product) {
                                $temp = $agentProducts->where('productId', $product->productId)->first();
                                if ($temp != null) {
                                    $newProducts[] = [
                                        'orderId' => $newOrder->id,
                                        'productId' => $product->productId,
                                        'amount' => $product->amount,
                                        'created_at' => Carbon::now(),
                                    ];
                                    //    return $this->response(false,$product->amount.'');
                                    if ($cart->addressType == 'company') {
                                        $totalCost = $totalCost + ($product->amount * $temp->officialPrice);
                                    }
                                    if ($cart->addressType == 'home') {
                                        $totalCost = $totalCost + ($product->amount * $temp->homePrice);
                                    }
                                    if ($cart->addressType == 'mosque') {
                                        $totalCost = $totalCost + ($product->amount * $temp->mosquePrice);
                                    }
                                }
                            }
                            // return $this->response(false,$totalCost.'');

                            $newOrder->amount = $totalCost - $discount;
                            $newOrder->save();


                            OrderProduct::insert($newProducts);
                            // clear cart
                            //  $products->delete();
                            CartProduct::where('cartId', $cartId)->delete();
                            $cart->delete();

                            // add new notification that order created
                            Notification::send($user, new OrderCreated($newOrder));
                            Notification::send($agent, new OrderCreated($newOrder));


                            // update coupon usage
                            if ($request->coupon != null) {
                                $coupon->used = $coupon->used + 1;
                                $coupon->save();
                            }

                            // save in points table

                            if ($request->points != null && $request->points > 0 && !$all_offers) {
                                $pointsRecord->save();
                            }


                            // $user = User::find($user->id);

                            // points added after completion
                            // $points = Setting::where('name','order_points')->first()->value;
                            // $user->points = $user->points + $points;
                            // $user->save();

                            DB::commit();
                            $this->sendNotification($agent->fcmToken, 'App\Notifications\OrderCreatedAgent', $agent->language);

                            if (!$sameAgent) {
                                $this->sendNotification($user->fcmToken, 'App\Notifications\OrderCreatedDifferentAgent', $user->language);
                            } else {
                                $this->sendNotification($user->fcmToken, 'App\Notifications\OrderCreated', $user->language);
                            }
                            if ($user->language == 'en') {
                                return $this->response(true, $locationAgent->englishSuccessMsg, $newOrder);
                                // return $this->response(true,$agent->englishSuccessMsg,$newOrder);
                            } else {
                                return $this->response(true, $locationAgent->arabicSuccessMsg, $newOrder);
                                // return $this->response(true,$agent->arabicSuccessMsg,$newOrder);
                            }
                        } else {
                            return $this->response(false, 'failed');
                        }
                    }
                }
            }
        } catch (Exception $e) {
            Log::info($exception->getMessage());
            DB::rollBack();

            return $this->response(false, 'system error');
        }
    }

    public function createOrderFromPortal(Request $request)
    {
        try {
            $data = $request->only(['userId', 'addressId', 'products', 'coupon', 'points', 'agentId', 'city_id', 'district_id', 'region_id']);
            $rules = [
                'userId' => 'required|numeric',
                'agentId' => 'required|numeric',
                'addressId' => 'required|numeric',
                'products' => 'required',
                // 'points' => 'numeric',
            ];
            $validator = Validator::make($data, $rules);
            if ($validator->fails()) {
                return $this->response(false, $this->validationHandle($validator->messages()));
            } else {
                // check user id
                $user = User::find($data['userId']);
                if ($user == null) {
                    return $this->response(false, 'not valid user id');
                }
                // check agent id
                $agent = Agent::find($data['agentId']);
                if ($agent == null) {
                    return $this->response(false, 'not valid agent id');
                }
                $couponDiscount = 0;


                if ($request->coupon != null) {
                    $coupon = Coupon::where('code', $data['coupon'])
                        ->where('status', 1)
                        ->where('notBefore', '<=', Carbon::now())->where('notAfter', '>=', Carbon::now())->first();
                    if ($coupon != null) {

                        if ($coupon->minAmount < $request->amount) {
                            if ($user->language == 'ar') {
                                return $this->response(false, ' لاستخدام كود الخصم، يجب أن تكون تكلفة الطلب أكثر من  ' . $coupon->minAmount);
                            }
                            return $this->response(false, 'minimum amount to use this coupon code should be ' . $coupon->minAmount);
                        }
                        if ($coupon->type == 'flat') {
                            $couponDiscount = $coupon->value;
                        } else {
                            if ($coupon->value <= 1) // value  = percentage
                                $couponDiscount = $coupon->value;
                        }
                    } else {
                        if ($user->language == 'ar')
                            return $this->response(false, 'كود الخصم غير صحيح');

                        return $this->response(false, 'not valid coupon');
                    }
                }

                if ($request->points != null) {
                    $request->points = $this->convert($request->points);
                    $min_points_to_replace = Setting::where('name', 'min_points_to_replace')->first()->value;
                    if ($request->points < $min_points_to_replace) {
                        if ($user->language == 'en') {
                            return $this->response(false, 'you can replace ' . $min_points_to_replace . ' points atleast');
                        } else {
                            return $this->response(false, ' لا تستطيع استبدل نقاط أقل من ' . $min_points_to_replace);
                        }
                    }
                    $replace_points = Setting::where('name', 'replace_points')->first()->value;
                    // $couponDiscount = $couponDiscount + ($request->points/$replace_points); no need to use points in coupon
                }


                // check address id
                $address = Address::where('id', $data['addressId'])->where('userId', $data['userId'])->first();
                // return $address;
                if ($address == null) {
                    return $this->response(false, 'not valid address');
                }
                if ($request->coupon == null) {
                    $data['coupon'] = null;
                }
                if ($request->points == null) {
                    $data['points'] = null;
                }

                DB::beginTransaction();
                $order = [
                    'userId' => $data['userId'],
                    'agentId' => $data['agentId'],
                    'addressType' => $address->type,
                    'addressId' => $data['addressId'],
                    'city_id' => $data['city_id'] ?? null,
                    'district_id' => $data['district_id'] ?? null,
                    'region_id' => $data['region_id'] ?? null,
                    'coupon' => $data['coupon'],
                    'points' => $data['points']
                ];
                $newOrder = Order::create($order);
                if ($newOrder != null) {

                    $newProducts = [];
                    // add products to order
                    $totalCost = 0;
                    foreach ($request->products as $product) {
                        // return  $product['id'];
                        $newProducts[] = [
                            'orderId' => $newOrder->id,
                            'productId' => $product['id'],
                            'amount' => $product['quantity'],
                            'created_at' => Carbon::now(),
                        ];

                        if ($address->type == 'company') {
                            $totalCost = $totalCost + ($product['quantity'] * $product['officialPrice']);
                        }
                        if ($address->type == 'home') {
                            $totalCost = $totalCost + ($product['quantity'] * $product['homePrice']);
                        }
                        if ($address->type == 'mosque') {
                            $totalCost = $totalCost + ($product['quantity'] * $product['mosquePrice']);
                        }
                    }
                    if ($request->coupon != null && $coupon->type == 'percentage') {
                        $newOrder->amount = $totalCost * (1 - $couponDiscount);
                    } else {
                        $newOrder->amount = $totalCost - $couponDiscount;
                    }

                    $newOrder->save();
                    OrderProduct::insert($newProducts);
                    // add new notification that order created
                    Notification::send($user, new OrderCreated($newOrder));
                    Notification::send($agent, new OrderCreated($newOrder));

                    // decreaase points of user
                    if ($request->points > 0) {
                        $user->points = $user->points - $request->points;
                        $user->save();
                        // send notification that user use some points
                        $this->sendNotification($user->fcmToken, 'App\Notifications\PointUsed', $user->language);
                    }
                    $this->sendNotification($user->fcmToken, 'App\Notifications\OrderCreated', $user->language);
                    $this->sendNotification($agent->fcmToken, 'App\Notifications\OrderCreatedAgent', $agent->language);
                    DB::commit();
                    return $this->response(true, 'success', $newOrder);
                } else {
                    return $this->response(false, 'failed');
                }
            }
        } catch (Exception $e) {
            DB::rollBack();
            return $this->response(false, 'system error');
        }
    }

    public function createNewOrder(Request $request)
    {

        $data = $request->only(['userId', 'addressId', 'products', 'coupon', 'points', 'agentId', 'city_id', 'district_id', 'region_id']);
        $rules = [
            'userId' => 'required|numeric',
            'agentId' => 'required|numeric',
            'addressId' => 'required|numeric',
            'products' => 'required',
            // 'points' => 'numeric',
        ];
        $validator = Validator::make($data, $rules);
        if ($validator->fails()) {
            return $this->newResponse(false, $this->validationHandle($validator->messages()));
        }

        try {
            // check user id
            $user = User::find($data['userId']);
            if ($user == null) {
                return $this->response(false, 'not valid user id');
            }
            // check agent id
            $agent = Agent::find($data['agentId']);
            if ($agent == null) {
                return $this->response(false, 'not valid agent id');
            }
            $couponDiscount = 0;


            if ($request->coupon != null) {
                $coupon = Coupon::where('code', $data['coupon'])
                    ->where('status', 1)
                    ->where('notBefore', '<=', Carbon::now())->where('notAfter', '>=', Carbon::now())->first();
                if ($coupon != null) {

                    if ($coupon->minAmount < $request->amount) {
                        if ($user->language == 'ar') {
                            return $this->response(false, ' لاستخدام كود الخصم، يجب أن تكون تكلفة الطلب أكثر من  ' . $coupon->minAmount);
                        }
                        return $this->response(false, 'minimum amount to use this coupon code should be ' . $coupon->minAmount);
                    }
                    if ($coupon->type == 'flat') {
                        $couponDiscount = $coupon->value;
                    } else {
                        if ($coupon->value <= 1) // value  = percentage
                            $couponDiscount = $coupon->value;
                    }
                } else {
                    if ($user->language == 'ar')
                        return $this->response(false, 'كود الخصم غير صحيح');

                    return $this->response(false, 'not valid coupon');
                }
            }

            if ($request->points != null) {
                $request->points = $this->convert($request->points);
                $min_points_to_replace = Setting::where('name', 'min_points_to_replace')->first()->value;
                if ($request->points < $min_points_to_replace) {
                    if ($user->language == 'en') {
                        return $this->response(false, 'you can replace ' . $min_points_to_replace . ' points atleast');
                    } else {
                        return $this->response(false, ' لا تستطيع استبدل نقاط أقل من ' . $min_points_to_replace);
                    }
                }
                $replace_points = Setting::where('name', 'replace_points')->first()->value;
                // $couponDiscount = $couponDiscount + ($request->points/$replace_points); no need to use points in coupon
            }


            // check address id
            $address = Address::where('id', $data['addressId'])->where('userId', $data['userId'])->first();
            // return $address;
            if ($address == null) {
                return $this->response(false, 'not valid address');
            }
            if ($request->coupon == null) {
                $data['coupon'] = null;
            }
            if ($request->points == null) {
                $data['points'] = null;
            }

            DB::beginTransaction();
            $order = [
                'userId' => $data['userId'],
                'agentId' => $data['agentId'],
                'addressType' => $address->type,
                'addressId' => $data['addressId'],
                'city_id' => $data['city_id'] ?? null,
                'district_id' => $data['district_id'] ?? null,
                'region_id' => $data['region_id'] ?? null,
                'coupon' => $data['coupon'],
                'points' => $data['points']
            ];
            $newOrder = Order::create($order);
            if ($newOrder != null) {

                $newProducts = [];
                // add products to order
                $totalCost = 0;
                foreach ($request->products as $product) {
                    // return  $product['id'];
                    $newProducts[] = [
                        'orderId' => $newOrder->id,
                        'productId' => $product['id'],
                        'amount' => $product['quantity'],
                        'created_at' => Carbon::now(),
                    ];

                    if ($address->type == 'company') {
                        $totalCost = $totalCost + ($product['quantity'] * $product['officialPrice']);
                    }
                    if ($address->type == 'home') {
                        $totalCost = $totalCost + ($product['quantity'] * $product['homePrice']);
                    }
                    if ($address->type == 'mosque') {
                        $totalCost = $totalCost + ($product['quantity'] * $product['mosquePrice']);
                    }
                }
                if ($request->coupon != null && $coupon->type == 'percentage') {
                    $newOrder->amount = $totalCost * (1 - $couponDiscount);
                } else {
                    $newOrder->amount = $totalCost - $couponDiscount;
                }

                $newOrder->save();
                OrderProduct::insert($newProducts);
                // add new notification that order created
                Notification::send($user, new OrderCreated($newOrder));
                Notification::send($agent, new OrderCreated($newOrder));

                // decreaase points of user
                if ($request->points > 0) {
                    $user->points = $user->points - $request->points;
                    $user->save();
                    // send notification that user use some points
                    $this->sendNotification($user->fcmToken, 'App\Notifications\PointUsed', $user->language);
                }
                $this->sendNotification($user->fcmToken, 'App\Notifications\OrderCreated', $user->language);
                $this->sendNotification($agent->fcmToken, 'App\Notifications\OrderCreatedAgent', $agent->language);
                DB::commit();
                return $this->response(true, 'success', $newOrder);
            } else {
                return $this->response(false, 'failed');
            }
        } catch (Exception $e) {
            DB::rollBack();
            return $this->response(false, 'system error');
        }
    }

    // assign order
    public function assignOrder(Request $request)
    {

        $data = $request->only('orderId', 'delegatorId', 'agentId');
        $rules = [
            'orderId' => 'required|numeric',
            'delegatorId' => 'required|numeric',
            'agentId' => 'required|numeric',
        ];
        $validator = Validator::make($data, $rules);
        if ($validator->fails()) {
            return $this->response(false, $this->validationHandle($validator->messages()));
        }
        try {



            $agent = Agent::find($request->agentId);
            if ($agent == null) {
                return $this->response(false, 'agent is not found');
            }

            $delegator = Delegator::find($request->delegatorId);
            if ($delegator == null) {
                return $this->response(false, 'delegator is not found');
            }
            $order = Order::where('id', $request->orderId)->where('agentId', $request->agentId)->whereNotIn('status', ['completed', 'on_the_way'])->first();
            if ($order == null) {
                return $this->response(false, 'order is not found');
            }


            $order->delegatorId = $request->delegatorId;
            $order->assignDate = Carbon::now();
            $order->status = 'assigned';
            $order->save();
            Notification::send($agent, new OrderAssigned($order));
            Notification::send($delegator, new OrderAssigned($order));

            $this->sendNotification($agent->fcmToken, 'App\Notifications\OrderAssigned', $agent->language);
            $this->sendNotification($delegator->fcmToken, 'App\Notifications\OrderAssignedDelegator', $delegator->language);


            return $this->response(true, 'success', $order);
        } catch (Exception $e) {
            return $this->response(false, 'system error');
        }
    }


    // cancel order by client
    public function cancelOrderByclient(Request $request)
    {


        try {
            $data = $request->only('orderId', 'userId');
            $rules = [
                'orderId' => 'required|numeric',
                'userId' => 'required|numeric',
            ];
            $validator = Validator::make($data, $rules);
            if ($validator->fails()) {
                return $this->response(false, $this->validationHandle($validator->messages()));
            } else {
                $user = User::find($request->userId);
                if ($user == null) {
                    return $this->response(false, 'user is not found');
                }
                $order = Order::where('id', $request->orderId)->where('userId', $request->userId)->where('status', 'created')->first();
                if ($order == null) {
                    return $this->response(false, 'order is not found');
                }

                DB::beginTransaction();
                $order->status = 'cancelledByClient';
                $order->cancelDate = Carbon::now();
                $order->save();

                // remove points if order is cancelled
                $pointsRecord = Points::where('orderId', $request->orderId)->first();
                if ($pointsRecord != null) {
                    $pointsRecord->delete();
                }
                DB::commit();
                Notification::send($user, new OrderCancelled($order));

                $this->sendNotification($user->fcmToken, 'App\Notifications\OrderCancelled', $user->language);


                return $this->response(true, 'success', $order);
            }
        } catch (Exception $e) {
            DB::rollBack();
            return $this->response(false, 'system error');
        }
    }

    // cancel order by app
    public function cancelOrderByApp(Request $request)
    {

        try {
            $data = $request->only('orderId', 'rejectionReason', 'agentId');
            $rules = [
                'orderId' => 'required|numeric',
                'agentId' => 'required|numeric',
                'rejectionReason' => 'required',
            ];
            $validator = Validator::make($data, $rules);
            if ($validator->fails()) {
                return $this->response(false, $this->validationHandle($validator->messages()));
            } else {
                $order = Order::where('id', $request->orderId)->where('agentId', $request->agentId)->where('status', 'created')->first();
                if ($order == null) {
                    return $this->response(false, 'order is not found');
                }

                DB::beginTransaction();
                $order->rejectionReason = $request->rejectionReason;
                $order->rejectionDate = Carbon::now();
                $order->status = 'cancelledByApp';
                $order->save();

                $user = User::find($order->userId);

                // remove points if order is cancelled
                $pointsRecord = Points::where('orderId', $request->orderId)->first();
                if ($pointsRecord != null) {
                    $pointsRecord->delete();
                }
                DB::commit();

                //TODO it needs to send reason of rejection
                if ($user->fcmToken)
                    $this->sendNotification($user->fcmToken, 'App\Notifications\OrderCancelled', $user->language);

                return $this->response(true, 'success', $order);
            }
        } catch (Exception $e) {
            DB::rollBack();
            return $this->response(false, 'system error');
        }
    }

    // update reason of canceled order by app
    public function UpdateCancelOrderByApp(Request $request)
    {
        try {
            $data = $request->only('orderId', 'rejectionReason', 'agentId');
            $rules = [
                'orderId' => 'required|numeric',
                'agentId' => 'required|numeric',
                'rejectionReason' => 'required',
            ];
            $validator = Validator::make($data, $rules);
            if ($validator->fails()) {
                return $this->response(false, $this->validationHandle($validator->messages()));
            } else {
                $order = Order::where('id', $request->orderId)->where('agentId', $request->agentId)->first();
                if ($order == null) {
                    return $this->response(false, 'order is not found');
                }

                DB::beginTransaction();
                $order->rejectionReason = $request->rejectionReason;
                $order->rejectionDate = Carbon::now();
                $order->status = 'cancelledByApp';
                $order->save();

                $user = User::find($order->userId);

                // remove points if order is cancelled
                $pointsRecord = Points::where('orderId', $request->orderId)->first();
                if ($pointsRecord != null) {
                    $pointsRecord->delete();
                }
                DB::commit();
                return $this->response(true, 'success', $order);
            }
        } catch (Exception $e) {
            DB::rollBack();
            return $this->response(false, 'system error');
        }
    }

    public function completeOrder(Request $request)
    {
        $data = $request->only('orderId', 'delegatorId');
        $rules = [
            'orderId' => 'required|numeric|exists:orders,id',
            'delegatorId' => 'required|numeric|exists:delegators,id',
            // 'comment' => 'required',
        ];
        $validator = Validator::make($data, $rules);
        if ($validator->fails()) {
            return $this->response(false, $this->validationHandle($validator->messages()));
        }
        try {
            DB::beginTransaction();
            $delegator = Delegator::find($request->delegatorId);
            $order = $delegator->orders()->where('status', 'created')->find($request->orderId);
            if ($order) {
                $order->update([
                    'status' => 'completed',
                    'completionDate' => Carbon::now()
                ]);
                if (($order->type != "offer") && (!$order->use_points && $order->points == null)) {
                    // add points to user
                    $user = User::find($order->userId);
                    // setting points
                    // number of cartons per order
                    //                    $cartoons = OrderProduct::where('orderProducts.orderId',$order->id)->sum('amount');
                    //                    $points = $points * $cartoons;
                    //                        $user->points = $user->points + $points;
                    //                        $user->save();

                    $points = Setting::where('name', 'order_points')->first()->value;


                    // register points of user in the points table
                    $pointsRecord = new Points();
                    $pointsRecord->clientId = $order->userId;
                    $pointsRecord->type = 'bonus';
                    $pointsRecord->points = $points;
                    $pointsRecord->delegatorId = $delegator->id;
                    $pointsRecord->orderId = $order->id;
                    $pointsRecord->agentId = $order->agentId;
                    $pointsRecord->save();
                }
                DB::commit();
                Notification::send($user, new OrderCompleted($order));
                Notification::send($delegator, new OrderCompleted($order));


                $this->sendNotification($delegator->fcmToken, 'App\Notifications\OrderCompleted', $delegator->language, $order);
                $this->sendNotification($user->fcmToken, 'App\Notifications\OrderCompletedClient', $user->language, $order);

                return $this->response(true, 'success', $order);
            } else {
                return $this->response(false, 'order is not found');
            }
        } catch (\Exception $e) {
            DB::rollback();
            return $this->response(false, 'system error');
        }
    }

    public function newCompleteOrder(Request $request)
    {
        $data = $request->only('orderId', 'delegatorId');
        $rules = [
            'orderId' => 'required|numeric|exists:orders,id',
            'delegatorId' => 'required|numeric|exists:delegators,id',
            // 'comment' => 'required',
        ];
        $validator = Validator::make($data, $rules);
        if ($validator->fails()) {
            return $this->response(false, $this->validationHandle($validator->messages()));
        }
        try {
            DB::beginTransaction();
            $delegator = Delegator::find($request->delegatorId);
            $order = $delegator->orders()->where('status', 'on_the_way')->find($request->orderId);
            if ($order) {
                $order->update([
                    'status' => 'completed',
                    'completionDate' => Carbon::now()
                ]);
                if (($order->type != "offer") && (!$order->use_points && $order->points == null)) {
                    // add points to user
                    $user = User::find($order->userId);
                    // setting points
                    // number of cartons per order
                    //                    $cartoons = OrderProduct::where('orderProducts.orderId',$order->id)->sum('amount');
                    //                    $points = $points * $cartoons;
                    //                        $user->points = $user->points + $points;
                    //                        $user->save();

                    $points = Setting::where('name', 'order_points')->first()->value;


                    // register points of user in the points table
                    $pointsRecord = new Points();
                    $pointsRecord->clientId = $order->userId;
                    $pointsRecord->type = 'bonus';
                    $pointsRecord->points = $points;
                    $pointsRecord->delegatorId = $delegator->id;
                    $pointsRecord->orderId = $order->id;
                    $pointsRecord->agentId = $order->agentId;
                    $pointsRecord->save();
                }
                DB::commit();
                Notification::send($user, new OrderCompleted($order));
                Notification::send($delegator, new OrderCompleted($order));


                $this->sendNotification($delegator->fcmToken, 'App\Notifications\OrderCompleted', $delegator->language, $order);
                $this->sendNotification($user->fcmToken, 'App\Notifications\OrderCompletedClient', $user->language, $order);

                return $this->response(true, 'success', $order);
            } else {
                return $this->response(false, 'order is not found');
            }
        } catch (\Exception $e) {
            DB::rollback();
            return $this->response(false, 'system error');
        }
    }

    public function makeOrderOnTheWay(Request $request)
    {
        $data = $request->only('orderId', 'delegatorId');
        $rules = [
            'orderId' => 'required|numeric|exists:orders,id',
            'delegatorId' => 'required|numeric|exists:delegators,id',
            // 'comment' => 'required',
        ];
        $validator = Validator::make($data, $rules);
        if ($validator->fails()) {
            return $this->response(false, $this->validationHandle($validator->messages()));
        }
        try {
            DB::beginTransaction();
            $delegator = Delegator::find($request->delegatorId);
            $order = $delegator->orders()->where('status', 'assigned')->find($request->orderId);
            if ($order) {
                $order->update([
                    'status' => 'on_the_way',
                ]);
                if (($order->type != "offer") && (!$order->use_points && $order->points == null)) {
                    // add points to user
                    $user = User::find($order->userId);
                    // setting points
                    // number of cartons per order
                    //                    $cartoons = OrderProduct::where('orderProducts.orderId',$order->id)->sum('amount');
                    //                    $points = $points * $cartoons;
                    //                        $user->points = $user->points + $points;
                    //                        $user->save();

                    $points = Setting::where('name', 'order_points')->first()->value;


                    // register points of user in the points table
                    $pointsRecord = new Points();
                    $pointsRecord->clientId = $order->userId;
                    $pointsRecord->type = 'bonus';
                    $pointsRecord->points = $points;
                    $pointsRecord->delegatorId = $delegator->id;
                    $pointsRecord->orderId = $order->id;
                    $pointsRecord->agentId = $order->agentId;
                    $pointsRecord->save();
                }
                DB::commit();
                Notification::send($user, new OrderOnTheWay($order));
                Notification::send($delegator, new OrderOnTheWay($order));


                $this->sendNotification($delegator->fcmToken, 'App\Notifications\OrderOnTheWay', $delegator->language, $order);
                $this->sendNotification($user->fcmToken, 'App\Notifications\OrderOnTheWay', $user->language, $order);

                return $this->response(true, 'success', $order);
            } else {
                return $this->response(false, 'order is not found');
            }
        } catch (\Exception $e) {
            DB::rollback();
            return $this->response(false, 'system error');
        }
    }

    // add reviews by client
    public function review(Request $request)
    {
        try {
            $data = $request->only('orderId', 'userId', 'serviceReview', 'delegatorReview', 'reviewText', 'productsReview');
            $rules = [
                'orderId' => 'required|numeric',
                'userId' => 'required|numeric',
                'serviceReview' => 'required|numeric',
                'delegatorReview' => 'required|numeric',
                'productsReview' => 'required|numeric',
                // 'reviewText' => 'required',
            ];
            $validator = Validator::make($data, $rules);
            if ($validator->fails()) {
                return $this->response(false, $this->validationHandle($validator->messages()));
            } else {
                $user = User::find($request->userId);
                if ($user == null) {
                    return $this->response(false, 'user is not found');
                }
                if ($request->reviewText == null) {
                    if ($user->language == 'ar') {
                        return $this->response(false, 'الرجاء إضافة ملاحظاتك');
                    }
                    return $this->response(false, 'please add your comment');
                }
                $order = Order::where('id', $request->orderId)->where('userId', $request->userId)->where('status', 'completed')->first();
                if ($order == null) {
                    return $this->response(false, 'order is not found');
                }

                $order->updated_at = Carbon::now();
                $order->reviewText = $request->reviewText;
                $order->productsReview = $request->productsReview;
                $order->delegatorReview = $request->delegatorReview;
                $order->serviceReview = $request->serviceReview;
                $order->save();
                // send notification to user that we received the review
                $this->sendNotification($user->fcmToken, 'App\Notifications\OrderReviewed', $user->language);
                return $this->response(true, 'success', $order);
            }
        } catch (Exception $e) {
            return $this->response(false, 'system error');
        }
    }

    // add reviews by delegator
    public function reviewByDelegator(Request $request)
    {
        try {
            $data = $request->only('orderId', 'delegatorId', 'delegatorReviewText', 'clientEvaluation');
            $rules = [
                'orderId' => 'required|numeric',
                'delegatorId' => 'required|numeric',
                // 'delegatorReviewText' => 'required',
                'clientEvaluation' => 'required|numeric',
            ];
            $validator = Validator::make($data, $rules);
            if ($validator->fails()) {
                return $this->response(false, $this->validationHandle($validator->messages()));
            } else {
                $order = Order::where('id', $request->orderId)->where('delegatorId', $request->delegatorId)->where('status', 'completed')->first();
                if ($order == null)
                    return $this->response(false, 'order is not found');
                $order->updated_at = Carbon::now();
                if ($request->delegatorReviewText != null) {
                    $order->delegatorReviewText = $request->delegatorReviewText;
                }

                $order->clientEvaluation = $request->clientEvaluation;
                $order->save();
                return $this->response(true, 'success', $order);
            }
        } catch (Exception $e) {
            return $this->response(false, 'system error');
        }
    }

    // re-order
    public function reorder(Request $request)
    {
        try {
            // $data = $request->only('orderId', 'userId','serviceReview','delegatorReview','reviewText','productsReview','');
            $data = $request->only('orderId', 'userId');
            $rules = [
                'orderId' => 'required|numeric',
                'userId' => 'required|numeric',

            ];
            $validator = Validator::make($data, $rules);
            if ($validator->fails()) {
                return $this->response(false, $this->validationHandle($validator->messages()));
            } else {

                $user = User::find($request->userId);

                if ($user == null) {
                    return $this->response(false, 'invalid user id');
                }

                $order = Order::where('id', $request->orderId)->first();
                if ($order == null) {
                    return $this->response(false, 'invalid order id');
                }
                // return $order;
                $products = OrderProduct::where('orderId', $request->orderId)->get();
                // return $products;
                if ($products == null || count($products) == 0) {
                    if ($user->language == 'ar') {
                        return $this->response(false, 'لا يمكنك إعادة الطلب');
                    }
                    return $this->response(false, 'no products in this order');
                }

                // add validation to reject inavailable products with this agent
                $agent = Agent::find($order->agentId);
                if ($agent == null) {
                    if ($user->language == 'ar') {
                        return $this->response(false, 'لا يمكنك إعادة الطلب');
                    }
                    return $this->response(false, 'no products in this order');
                }

                $productCount = count($products);
                $productIds = $products->pluck('productId');
                $enabledProductsFromOrder = AgentProduct::where('agentId', $order->agentId)->where('status', 1)->whereIn('productId', $productIds)->count();

                if ($enabledProductsFromOrder != $productCount) {
                    if ($user->language == 'ar') {
                        return $this->response(false, 'بعض المنتجات غير متوفرة حالياً');
                    }
                    return $this->response(false, 'some of items are not available now, please try later');
                }
                // remove all current carts, before creating new one
                Cart::where('userId', $request->userId)->delete();
                $cart = [];
                foreach ($products as $product) {
                    $cart = parent::addToCart($request->userId, $order->agentId, $order->addressType, $product->amount, $product->productId);
                }

                // return $newProducts;
                return $this->response(true, 'success', $cart);
            }
        } catch (Exception $e) {
            return $this->response(false, 'system error');
        }
    }

    // orders by type
    public function getOrdersCount(Request $request)
    {
        try {
            $data = $request->only('startDate', 'endDate', 'type', 'agentId');
            $rules = [
                'type' => 'required|in:status,agent,address,region',
                'startDate' => 'required',
                'endDate' => 'required',
            ];
            $validator = Validator::make($data, $rules);
            if ($validator->fails()) {
                return $this->response(false, $this->validationHandle($validator->messages()));
            } else {
                $from = date($request->startDate);
                $to = date($request->endDate);
                switch ($request->type) {
                    case "status":
                        $orders = Order::select('status as criteria', FacadesDB::raw('count(*) as value'))
                            ->whereBetween('created_at', [$from, $to]);
                        if ($request->agentId != null) {
                            $orders = $orders->where('agentId', $request->agentId);
                        }
                        $orders = $orders->groupBy('status')
                            ->get();
                        foreach ($orders as $order) {
                            if ($order->criteria == 'created') {
                                $order->criteria = 'New';
                            }
                            if ($order->criteria == 'cancelledByClient') {
                                $order->criteria = 'Cancelled By Client';
                            }
                            if ($order->criteria == 'cancelledByApp') {
                                $order->criteria = 'Rejected';
                            }
                            if ($order->criteria == 'completed') {
                                $order->criteria = 'Completed';
                            }
                        }
                        return $this->response(true, 'success', $orders);
                    case "agent":
                        $orders = Order::select('agents.name as criteria', DB::raw('count(*) as value'))
                            ->join('agents', 'agents.id', '=', 'orders.agentId')
                            ->where('orders.status', 'completed')
                            ->whereBetween('orders.created_at', [$from, $to]);
                        if ($request->agentId != null) {
                            $orders = $orders->where('agentId', $request->agentId);
                        }
                        $orders = $orders->groupBy('agents.name')
                            ->get();

                        return $this->response(true, 'success', $orders);
                    case "region":
                        $orders = Order::select('regions.englishName as criteria', DB::raw('count(*) as value'))
                            ->join('agents', 'agents.id', '=', 'orders.agentId')
                            ->join('regions', 'regions.id', '=', 'agents.region')
                            ->where('orders.status', 'completed')
                            ->whereBetween('orders.created_at', [$from, $to]);
                        if ($request->agentId != null) {
                            $orders = $orders->where('agentId', $request->agentId);
                        }
                        $orders = $orders->groupBy('regions.englishName')
                            ->get();

                        return $this->response(true, 'success', $orders);
                    case "address":
                        $orders = Order::select('addressType as criteria', DB::raw('count(*) as value'))
                            ->where('orders.status', 'completed')
                            ->whereBetween('created_at', [$from, $to]);
                        if ($request->agentId != null) {
                            $orders = $orders->where('agentId', $request->agentId);
                        }
                        $orders = $orders->groupBy('addressType')
                            ->get();
                        return $this->response(true, 'success', $orders);
                    default:
                        return $this->response(false, 'failed');
                }
            }
        } catch (Exception $e) {
            return $this->response(false, 'system error');
        }
    }

    // orders by type
    public function getOrdersRevenue(Request $request)
    {
        try {
            $data = $request->only('startDate', 'endDate', 'type', 'agentId');
            $rules = [
                'type' => 'required|in:status,agent,address,region',
                'startDate' => 'required',
                'endDate' => 'required',
            ];
            $validator = Validator::make($data, $rules);
            if ($validator->fails()) {
                return $this->response(false, $this->validationHandle($validator->messages()));
            } else {
                $from = date($request->startDate);
                $to = date($request->endDate);
                switch ($request->type) {
                    case "status":
                        $orders = Order::select('status as criteria', DB::raw('sum(amount) as value'))
                            ->whereBetween('created_at', [$from, $to]);
                        if ($request->agentId != null) {
                            $orders = $orders->where('agentId', $request->agentId);
                        }
                        $orders = $orders->groupBy('status')->get();
                        foreach ($orders as $order) {
                            if ($order->criteria == 'created') {
                                $order->criteria = 'New';
                            }
                            if ($order->criteria == 'cancelledByClient') {
                                $order->criteria = 'Cancelled By Client';
                            }
                            if ($order->criteria == 'cancelledByApp') {
                                $order->criteria = 'Rejected';
                            }
                            if ($order->criteria == 'completed') {
                                $order->criteria = 'Completed';
                            }
                        }
                        return $this->response(true, 'success', $orders);
                    case "agent":
                        $orders = Order::select('agents.name as criteria', DB::raw('sum(amount) as value'))
                            ->join('agents', 'agents.id', '=', 'orders.agentId')
                            ->where('orders.status', 'completed')
                            ->whereBetween('orders.created_at', [$from, $to]);

                        if ($request->agentId != null) {
                            $orders = $orders->where('agentId', $request->agentId);
                        }
                        $orders = $orders->groupBy('agents.name')->get();

                        return $this->response(true, 'success', $orders);
                    case "region":
                        $orders = Order::select('regions.englishName as criteria', DB::raw('sum(amount) as value'))
                            ->join('agents', 'agents.id', '=', 'orders.agentId')
                            ->join('regions', 'regions.id', '=', 'agents.region')
                            ->where('orders.status', 'completed')
                            ->whereBetween('orders.created_at', [$from, $to]);
                        if ($request->agentId != null) {
                            $orders = $orders->where('agentId', $request->agentId);
                        }
                        $orders = $orders->groupBy('regions.englishName')->get();

                        return $this->response(true, 'success', $orders);
                    case "address":
                        $orders = Order::select('addressType as criteria', DB::raw('sum(amount) as value'))
                            ->where('orders.status', 'completed')
                            ->whereBetween('created_at', [$from, $to]);
                        if ($request->agentId != null) {
                            $orders = $orders->where('agentId', $request->agentId);
                        }
                        $orders = $orders->groupBy('addressType')->get();
                        return $this->response(true, 'success', $orders);
                    default:
                        return $this->response(false, 'failed');
                }
            }
        } catch (Exception $e) {
            return $this->response(false, 'system error');
        }
    }


    public function update(Request $request)
    {
        try {

            $data = $request->only(['delegatorId', 'id', 'agentId', 'userId', 'status', 'city_id', 'district_id', 'region_id']);
            $rules = [
                'id' => 'required',
                'agentId' => 'required',
                // 'delegatorId' => 'required',
                'userId' => 'required',
                'status' => 'required',
            ];

            $validator = Validator::make($data, $rules);
            if ($validator->fails()) {
                return $this->response(false, $this->validationHandle($validator->messages()));
            } else {

                $order = Order::where('id', $request->id)->where('agentId', $request->agentId)->first();
                if ($order == null) {
                    return $this->response(false, 'not valid id');
                } else {


                    $user = User::find($request->userId);
                    $agent = Agent::find($request->agentId);
                    // return $user;
                    $delegator = Delegator::find($request->delegatorId);

                    if ($order->status != $request->status || $order->status == 'created') {
                        switch ($request->status) {

                            case 'created':
                                // return $delegator;
                                $this->sendNotification($delegator->fcmToken, 'App\Notifications\OrderAssignedDelegator', $delegator->language);

                                if ($order->delegatorId == $request->delegatorId) {
                                    // $this->sendNotification($user->fcmToken,'App\Notifications\OrderCreatedAgent',$user->language);
                                    // $this->sendNotification($agent->fcmToken,'App\Notifications\OrderCreatedAgent',$agent->language);
                                } else {
                                    if ($delegator != null) {

                                        $order->assignDate = Carbon::now();
                                        $this->sendNotification($delegator->fcmToken, 'App\Notifications\OrderAssignedDelegator', $delegator->language);
                                    }
                                }
                                break;
                            case 'cancelledByClient':
                                // send notification to client
                                $this->sendNotification($user->fcmToken, 'App\Notifications\OrderCancelled', $user->language);
                                $this->sendNotification($agent->fcmToken, 'App\Notifications\OrderCancelled', $agent->language);
                                if ($delegator != null) {
                                    $this->sendNotification($delegator->fcmToken, 'App\Notifications\OrderCancelled', $agent->delegator);
                                }
                                $order->cancelDate = Carbon::now();
                                break;
                            case 'cancelledByApp':
                                // send notification
                                $order->rejectionDate = Carbon::now();
                                $this->sendNotification($user->fcmToken, 'App\Notifications\OrderCancelled', $user->language);
                                break;
                            case 'completed':
                                $user->points = $user->points + 10;
                                $user->save();
                                $this->sendNotification($user->fcmToken, 'App\Notifications\OrderCompleted', $user->language);
                                break;
                        }
                    }
                    $order->delegatorId = $request->delegatorId;
                    $order->status = $request->status;
                    $order->save();
                    return $this->response(true, 'success');
                }
            }
        } catch (Exception $e) {
            return $this->response(false, 'system error');
        }
    }

    public function getAgentReport(Request $request)
    {
        try {

            $data = $request->only(['agentId', 'from', 'to']);
            $rules = [
                'agentId' => 'required|numeric',
                'from' => 'required',
                'to' => 'required',
            ];

            $validator = Validator::make($data, $rules);
            if ($validator->fails()) {
                return $this->response(false, $this->validationHandle($validator->messages()));
            } else {
                $request->to = $this->transformArabicNumbers($request->to);
                $request->from = $this->transformArabicNumbers($request->from);
                $from = $request->from;
                $to = $request->to . ' 23:59:59'; // include to in the report
                $report = Order::whereBetween('created_at', array($from, $to))
                    // ->where('status','completed')
                    ->where('agentId', $request->agentId)
                    ->where('status', 'completed')
                    ->select(DB::raw('count(*) as count, sum(amount) as sum'))
                    ->first();
                if ($report->sum == null) {
                    $report->sum = 0;
                }
                return $this->response(true, 'success', $report);
            }
        } catch (Exception $e) {
            return $this->response(false, 'system error');
        }
    }

    public function sumReport(Request $request)
    {
        try {

            $data = $request->only(['agentId', 'from', 'to', 'type']);
            $rules = [
                // 'agentId' => 'required|numeric',
                'from' => 'required',
                'to' => 'required',
            ];

            $validator = Validator::make($data, $rules);
            if ($validator->fails()) {
                return $this->response(false, $this->validationHandle($validator->messages()));
            } else {

                $from = $request->from;
                $to = $request->to;

                $report = Order::whereBetween('created_at', array($from, $to));

                if ($request->agentId != null) {
                    $report = $report->where('agentId', $request->agentId);
                }
                if ($request->type != null && $request->type == 'online') {
                    $report = $report->whereNotNull('paymentReference');
                }
                if ($request->type != null && $request->type == 'cash') {
                    $report = $report->whereNull('paymentReference');
                }

                $report = $report->where('status', 'completed');
                // $report=$report->select(DB::raw('count(*) as count, sum(amount) as sum' ))->get();
                $report = $report->select(DB::raw('agentId, count(*) as count, sum(amount) as sum'))
                    ->groupBy('agentId')->get();
                if ($report->sum == null) {
                    $report->sum = 0;
                }
                return $this->response(true, 'success', $report);
            }
        } catch (Exception $e) {
            return $this->response(false, 'system error');
        }
    }

    public function deliveryReport(Request $request)
    {
        try {

            $data = $request->only(['agentId', 'from', 'to']);
            $rules = [
                // 'agentId' => 'required|numeric',
                'from' => 'required',
                'to' => 'required',
            ];

            $validator = Validator::make($data, $rules);
            if ($validator->fails()) {
                return $this->response(false, $this->validationHandle($validator->messages()));
            } else {

                $from = $request->from;
                $to = $request->to;

                $report = Order::whereBetween('created_at', array($from, $to));

                if ($request->agentId != null) {
                    $report = $report->where('agentId', $request->agentId);
                }
                $report = $report->where('status', 'completed');
                // $report=$report->select(DB::raw('count(*) as count, sum(amount) as sum' ))->get();
                $report = $report->select(DB::raw('agentId,  AVG(HOUR( TIMEDIFF(created_at, completionDate) ) ) as hours'))
                    ->groupBy('agentId')->get();

                foreach ($report as $value) {
                    $value->hours = $value->hours + 1;
                    # code...
                }
                return $this->response(true, 'success', $report);
            }
        } catch (Exception $e) {
            return $this->response(false, 'system error');
        }
    }

    public function listAllReviewedOrders(Request $request)
    {
        try {
            $data = $request->only(['id', 'from', 'to', 'mobile']);
            $rules = [
                // 'agentId' => 'required|numeric',
                // 'from' => 'required',
                // 'to' => 'required',
            ];

            $validator = Validator::make($data, $rules);
            if ($validator->fails()) {
                return $this->response(false, $this->validationHandle($validator->messages()));
            } else {
                $paginationParams = [];

                $orders = Order::leftJoin('users', 'users.id', '=', 'orders.userId')
                    ->leftJoin('agents', 'agents.id', '=', 'orders.agentId')
                    ->leftJoin('delegators', 'delegators.id', '=', 'orders.delegatorId')
                    ->select(
                        'orders.id',
                        'orders.created_at',
                        'reviewText',
                        'clientEvaluation',
                        'productsReview',
                        'delegatorReview',
                        'serviceReview',
                        'delegatorReviewText',
                        'users.name as clientName',
                        'agents.name as agentName',
                        'delegators.name as delegatorName'
                    );

                if ($request->agentId != null) {
                    $orders = $orders->where('orders.agentId', $request->agentId);
                    $paginationParams['agentId'] = $request->agentId;
                }
                if ($request->from != null && $request->to != null) {
                    $orders = $orders->whereBetween('orders.created_at', [$request->from, $request->to]);
                    $paginationParams['from'] = $request->from;
                    $paginationParams['to'] = $request->to;
                }

                $orders = $orders->where('orders.status', 'completed')
                    ->where(function ($query) {
                        $query->whereNotNull('reviewText')
                            ->orWhereNotNull('clientEvaluation');
                    })
                    ->orderBy('orders.created_at', 'desc')->paginate($request->get('perPage', '20'));
                if (!empty($paginationParams)) {
                    $orders->appends($paginationParams);
                }
            }


            return new ReviewCollection($orders);
            // return $this->response(true,'success',$orders);
        } catch (Exception $e) {
            return $this->response(false, 'system error');
        }
    }

    public function newSearch(Request $request)
    {
        $orders = new Order();
        $orders_count = $orders->count();
        $paginationParams = [];

        if ($request->id) {
            $orders = $orders->where('id', $request->id);
            $paginationParams['id'] = $request->id;
        }
        if ($request->mobile) {
            $orders = $orders->whereHas('customer', function ($qq) use ($request) {
                $qq->where('mobile', $request->mobile);
            });
            $paginationParams['mobile'] = $request->mobile;
        }
        if ($request->agentId) {

            $orders = $orders->whereIn('agentId', $request->agentId);
            $paginationParams['agent_id'] = $request->agentId;
            $paginationParams['agentId'] = $request->agentId;
        }

        if ($request->from != null && $request->to != null) {
            $orders = $orders->whereBetween('created_at', [$request->from, $request->to]);
            $paginationParams['from'] = $request->from;
            $paginationParams['to'] = $request->to;
        }
        if ($request->status != null) {
            $orders = $orders->where('status', $request->status);
        }

        $orders = new \App\Http\Resources\Order\OrderCollection($orders->orderBy('id', 'desc')->paginate($request->get('perPage', '20')));
        return $this->newResponse(true, __('api.success_response'), '', [], [
            'total_orders' => $orders_count,
            'orders' => $orders,
        ]);
        //        $data = $request->only(['id', 'from', 'to', 'mobile', 'status', 'agentId']);
        //        $orders = new Order();
        //        $paginationParams = [];
        //
        //        if ($request->id) {
        //            $orders = $orders->where('id', $request->id);
        //            $paginationParams['id'] = $request->id;
        //        }
        //        if ($request->mobile) {
        //            $orders = $orders->whereHas('customer', function ($qq) use ($request) {
        //                $qq->where('mobile', $request->mobile);
        //            });
        //            $paginationParams['mobile'] = $request->mobile;
        //
        //        }
        //        if ($request->agentId) {
        //
        //            $orders = $orders->whereIn('agentId', $request->agentId);
        //            $paginationParams['agent_id'] = $request->agentId;
        //            $paginationParams['agentId'] = $request->agentId;
        //        }
        //
        //        if ($request->from != null && $request->to != null) {
        //            $orders = $orders->whereBetween('created_at', [$request->from, $request->to]);
        //            $paginationParams['from'] = $request->from;
        //            $paginationParams['to'] = $request->to;
        //        }
        //        if ($request->status != null) {
        //            $orders = $orders->where('status', $request->status);
        //        }

        //        $orders = $orders->with(['customer.orders' => function ($q) use ($request) {
        //        $q->count();
        //        $q->withCount(['orders as total_orders'=>function($qq) use ($request){
        //                $qq->whereBetween('created_at', [$request->from,$request->to]);
        //            }]);
        //        }]);

        //        $orders_count = $orders->count();
        //        $orders=$orders->withCount('customer',function($query){
        //            $query->orders()->count();
        //       });


        //        return $orders->paginate(5);
        //        $orders = new \App\Http\Resources\Order\OrderCollection($orders->orderBy('id', 'desc')->paginate($request->get('perPage', '20')));
        //        return $this->newResponse(true, __('api.success_response'), '', [], [
        //            'total_orders' => $orders_count,
        //            'orders' => $orders,
        //        ]);

    }

    public function search(Request $request)
    {

        try {
            $data = $request->only(['id', 'from', 'to', 'mobile', 'deleyType', 'status']);
            $rules = [
                // 'agentId' => 'required|numeric',
                // 'from' => 'required',
                // 'to' => 'required',
            ];

            $validator = Validator::make($data, $rules);
            if ($validator->fails()) {
                return $this->response(false, $this->validationHandle($validator->messages()));
            } else {
                if ($request->id == null && $request->mobile == null && $request->from == null && $request->to == null) {


                    $orders = Order::leftJoin('users', 'users.id', '=', 'orders.userId')
                        ->leftJoin('agents', 'agents.id', '=', 'orders.agentId')
                        ->leftJoin('delegators', 'delegators.id', '=', 'orders.delegatorId')
                        ->leftJoin('address', 'address.id', '=', 'orders.addressId')
                        ->select(
                            'orders.*',
                            'users.name as clientName',
                            'users.mobile as clientMobile',
                            'address.lat',
                            'address.lng',
                            'agents.name as agentName',
                            'agents.mobile as agentMobile',
                            'delegators.name as delegatorName',
                            'delegators.mobile as delegatorMobile'
                        )
                        ->orderBy('created_at', 'desc');
                    if ($request->deleyType != null && $request->deleyType == 'created') {
                        $orders = $orders->where('orders.created_at', '<', Carbon::now()->subDays(2));
                    }
                    if ($request->deleyType != null && $request->deleyType == 'completed') {
                        // $orders = $orders->where('orders.created_at','<',Carbon::now()->subDays(2));
                        $orders = $orders->whereRaw('DATEDIFF(orders.completionDate,orders.created_at) > 2');
                    }
                    if ($request->deleyType != null && $request->deleyType == 'cancelledByClient') {
                        $orders = $orders->whereRaw('DATEDIFF(orders.cancelDate,orders.created_at) > 2');
                    }
                    $orders = $orders->paginate(200)->items();
                    foreach ($orders as $order) {
                        $order->amountBeforeDiscount = $order->amount + $order->pointsDiscount + $order->couponDiscount;
                    }
                    return $this->response(true, 'success', $orders);
                }
                $orders = Order::leftJoin('users', 'users.id', '=', 'orders.userId')
                    ->leftJoin('agents', 'agents.id', '=', 'orders.agentId')
                    ->leftJoin('delegators', 'delegators.id', '=', 'orders.delegatorId')
                    ->leftJoin('address', 'address.id', '=', 'orders.addressId')
                    ->select(
                        'orders.*',
                        'users.name as clientName',
                        'users.mobile as clientMobile',
                        'address.lat',
                        'address.lng',
                        'agents.name as agentName',
                        'agents.mobile as agentMobile',
                        'delegators.name as delegatorName',
                        'delegators.mobile as delegatorMobile'
                    );

                if ($request->id != null) {
                    $orders = $orders->where('orders.id', $request->id);
                }
                if ($request->status != null) {
                    $orders = $orders->where('orders.status', $request->status);
                }
                if ($request->deleyType != null) {
                    if ($request->deleyType == 'created') {
                        $orders = $orders->where('orders.created_at', '<', Carbon::now()->subDays(2));
                    }
                    if ($request->deleyType == 'completed') {
                        // $orders = $orders->where('orders.created_at','<',Carbon::now()->subDays(2));
                        $orders = $orders->whereRaw('DATEDIFF(orders.completionDate,orders.created_at) > 2');
                    }
                    if ($request->deleyType == 'cancelledByClient') {
                        $orders = $orders->whereRaw('DATEDIFF(orders.cancelDate,orders.created_at) > 2');
                    }
                }
                if ($request->agentId != null) {
                    $orders = $orders->where('orders.agentId', $request->agentId);
                }
                if ($request->mobile != null) {
                    $orders = $orders->where('users.mobile', $request->mobile);
                }
                if ($request->from != null && $request->to != null) {
                    $orders = $orders->whereBetween('orders.created_at', [$request->from, $request->to]);
                }

                $orders = $orders->orderBy('created_at', 'desc')
                    ->get();
                foreach ($orders as $order) {
                    $order->amountBeforeDiscount = $order->amount + $order->pointsDiscount + $order->couponDiscount;
                }

                // ->paginate(200)->items() ;
                return $this->response(true, 'success', $orders);
            }
        } catch (Exception $e) {
            return $this->response(false, 'system error');
        }
    }

    public function topProducts(Request $request)
    {
        try {

            $data = $request->only(['agentId']);
            $rules = [
                // 'agentId' => 'required|numeric',
                // 'from' => 'required',
                // 'to' => 'required',
            ];

            $validator = Validator::make($data, $rules);
            if ($validator->fails()) {
                return $this->response(false, $this->validationHandle($validator->messages()));
            } else {

                if ($request->agentId == null) {
                    $results = OrderProduct::select(DB::raw('productId, sum(orderProducts.amount) as count'), 'products.picture', 'products.englishName', 'products.arabicName')
                        ->leftJoin('orders', 'orders.id', '=', 'orderProducts.orderId')
                        ->leftJoin('products', 'products.id', '=', 'orderProducts.productId')
                        ->where('orders.status', 'completed')
                        ->groupby('productId')
                        ->orderby('count', 'desc')
                        ->get();
                    foreach ($results as $value) {
                        $value->picture = url('') . $value->picture;
                    }
                    return $this->response(true, 'success', $results);
                } else {
                    $results = OrderProduct::select(DB::raw('productId, sum(orderProducts.amount) as count'), 'products.picture', 'products.englishName', 'products.arabicName')
                        ->leftJoin('orders', 'orders.id', '=', 'orderProducts.orderId')
                        ->leftJoin('products', 'products.id', '=', 'orderProducts.productId')
                        ->where('orders.agentId', $request->agentId)
                        ->where('orders.status', 'completed')
                        ->groupby('productId')
                        ->orderby('count', 'desc')
                        ->get();
                    foreach ($results as $value) {
                        $value->picture = url('') . $value->picture;
                    }
                    return $this->response(true, 'success', $results);
                }
            }
        } catch (Exception $e) {
            return $this->response(false, 'system error');
        }
    }

    public function cartonsReport(Request $request)
    {
        try {

            $data = $request->only(['agentId', 'from', 'to', 'minCartons', 'maxCartons']);
            $rules = [
                // 'agentId' => 'required|numeric',
                // 'from' => 'required',
                // 'to' => 'required',
            ];

            $validator = Validator::make($data, $rules);
            if ($validator->fails()) {
                return $this->response(false, $this->validationHandle($validator->messages()));
            } else {

                if ($request->agentId == null) {
                    $results = OrderProduct::select(DB::raw('productId, sum(orderProducts.amount) as count'), 'products.picture', 'products.englishName', 'products.arabicName')
                        ->leftJoin('orders', 'orders.id', '=', 'orderProducts.orderId')
                        ->leftJoin('products', 'products.id', '=', 'orderProducts.productId')
                        ->where('orders.status', 'completed')
                        ->groupby('productId')
                        ->orderby('count', 'desc')
                        ->get();
                    foreach ($results as $value) {
                        $value->picture = url('') . $value->picture;
                    }
                    return $this->response(true, 'success', $results);
                } else {
                    $results = OrderProduct::select(DB::raw('productId, sum(orderProducts.amount) as count'), 'products.picture', 'products.englishName', 'products.arabicName')
                        ->leftJoin('orders', 'orders.id', '=', 'orderProducts.orderId')
                        ->leftJoin('products', 'products.id', '=', 'orderProducts.productId')
                        ->where('orders.agentId', $request->agentId)
                        ->where('orders.status', 'completed')
                        ->groupby('productId')
                        ->orderby('count', 'desc')
                        ->get();
                    foreach ($results as $value) {
                        $value->picture = url('') . $value->picture;
                    }
                    return $this->response(true, 'success', $results);
                }
            }
        } catch (Exception $e) {
            return $this->response(false, 'system error');
        }
    }


    public function quantityReport(Request $request)
    {
        $data = $request->only(['min', 'max', 'agentIds']);
        $rules = [
            // 'agentId' => 'required',
            'agentIds' => 'required|min:1',
            'min' => 'required',
            'max' => 'required',
        ];

        $validator = Validator::make($data, $rules);
        if ($validator->fails()) {
            return $this->response(false, $this->validationHandle($validator->messages()));
        }


        try {
            $orders = Order::withCount(['orderproducts as carton_count' => function ($query) {
                $query->select(DB::raw('sum(amount)'));
            }])->havingRaw('carton_count >= ' . $request->min)
                ->havingRaw('carton_count <= ' . $request->max)
                ->where('status', 'completed');
            if (!empty($request->agentIds)) {
                $orders = $orders->whereIn('agentId', $request->agentIds);
            }
            if ($request->from != null && $request->to != null) {
                $orders = $orders->whereBetween('created_at', [$request->from, $request->to]);
            }
            $orders = $orders->simplePaginate(10);
            return response()->json(compact('orders'));
        } catch (Exception $e) {
            return $this->response(false, 'system error');
        }
    }

    public function countUsersOfAgent(Request $request)
    {
        try {

            $data = $request->only(['userId', 'agentId']);
            $rules = [
                'agentId' => 'required',
                // 'userId' => 'required',
            ];

            $validator = Validator::make($data, $rules);
            if ($validator->fails()) {
                return $this->response(false, $this->validationHandle($validator->messages()));
            } else {

                $count = Order::where('agentId', $request->agentId)->distinct('userId');
                if ($request->userId != null) {
                    $count = $count->where('userId', $request->userId);
                }
                $count = $count->count();
                return $this->response(true, 'success', $count);
            }
        } catch (Exception $e) {
            return $this->response(false, 'system error');
        }
    }


    public function changeStatus(Request $request)
    {
        $data = $request->only(['id', 'status', 'reason']);
        $rules = [
            'id' => 'required',
            'status' => 'required|in:created,cancelledByClient,cancelledByApp,completed,on_the_way',
            'reason' => 'nullable'
        ];
        $validator = Validator::make($data, $rules);
        if ($validator->fails()) {
            return $this->response(false, $this->validationHandle($validator->messages()));
        }
        try {
            $order = Order::find($request->id);
            if ($order == null) {
                return $this->response(false, 'not valid id');
            } else {
                $user = @$order->customer;
                $agent = @$order->agent;
                $delegator = @$order->delegator;
                switch ($request->status) {
                    case 'on_the_way':
                        if ($user && $user->fcmToken) {
                            $this->sendNotification($user->fcmToken, 'App\Notifications\OrderOnTheWay', $user->language);
                            $this->sendNotification($agent->fcmToken, 'App\Notifications\OrderOnTheWay', $agent->language);
                        }
                        if ($delegator && $delegator->fcmToken) {
                            $this->sendNotification($delegator->fcmToken, 'App\Notifications\OrderOnTheWay', $agent->delegator);
                        }
                        break;
                    case 'completed':
                        if ($user && $user->fcmToken) {
                            $this->sendNotification($user->fcmToken, 'App\Notifications\OrderCompleted', $user->language);
                        }
                        if ($agent && $agent->fcmToken) {
                            $this->sendNotification($agent->fcmToken, 'App\Notifications\OrderCompleted', $agent->language);
                        }
                        if ($delegator && $delegator->fcmToken) {
                            $this->sendNotification($delegator->fcmToken, 'App\Notifications\OrderCompleted', $agent->delegator);
                        }
                        break;
                    case 'created':
                        // return $delegator;
                        if ($delegator && $delegator->fcmToken) {
                            $this->sendNotification($delegator->fcmToken, 'App\Notifications\OrderAssignedDelegator', $delegator->language);
                        }
                        if ($order->delegatorId != $request->delegatorId) {
                            if ($delegator) {
                                $order->assignDate = Carbon::now();
                                if ($delegator->fcmToken) {
                                    $this->sendNotification($delegator->fcmToken, 'App\Notifications\OrderAssignedDelegator', $delegator->language);
                                }
                            }
                        }
                        break;
                    case 'cancelledByClient':
                        // send notification to client
                        if ($user && $user->fcmToken) {
                            $this->sendNotification($user->fcmToken, 'App\Notifications\OrderCancelled', $user->language);
                        }
                        if ($agent && $agent->fcmToken) {
                            $this->sendNotification($agent->fcmToken, 'App\Notifications\OrderCancelled', $agent->language);
                        }
                        if ($delegator) {
                            if ($delegator->fcmToken) {
                                $this->sendNotification($delegator->fcmToken, 'App\Notifications\OrderCancelled', $agent->delegator);
                            }
                        }
                        $order->cancelDate = Carbon::now();
                        $order->rejectionReason = $request->reason;
                        // remove points if order is cancelled
                        $pointsRecord = Points::where('orderId', $order->id)->first();
                        if ($pointsRecord != null)
                            $pointsRecord->delete();
                        break;
                    case 'cancelledByApp':
                        // send notification
                        $order->rejectionDate = Carbon::now();
                        if ($user && $user->fcmToken) {
                            $this->sendNotification($user->fcmToken, 'App\Notifications\OrderCancelled', $user->language);
                        }
                        // remove points if order is cancelled
                        $pointsRecord = Points::where('orderId', $order->id)->first();
                        if ($pointsRecord != null)
                            $pointsRecord->delete();
                        break;
                }
                // $order->delegatorId = $request->delegatorId;
                $order->status = $request->status;
                $order->save();
                return $this->response(true, 'success');
            }
        } catch (Exception $e) {
            return $this->response(false, $e->getMessage());
        }
    }

    public function getCouponDiscount($couponId, $amount)
    {
        $discount_value = 0;
        $coupon = Coupon::where('status', 1)->where('notBefore', '<', Carbon::today())->where('notAfter', '>', Carbon::today())->where('id', $couponId)->first();

        if ($coupon) {
            if ($coupon->minAmount > $amount) {

                return $this->newResponse(false, __('api.coupon_exceed_min_amount', ['amount' => $coupon->minAmount]));
            } else {
                if ($coupon->type == 'percentage') {
                    $discount_value = ((float)$amount * (float)$coupon->value);
                } else {
                    $discount_value = (float)$coupon->value;
                }
            }
        } else {
            return -1;
        }
        return $discount_value;
    }

    public function place(Request $request)
    {
        $data = $request->only([
            'coupon_id',
            'time_slot_id',
            'flat_location_id',
            // 'payment_type_id',
            'type',
            'delivery_schedule_date',
            'is_paid',
            'payment_transaction_id',
            'delivery_date',
            'points',
            'user_id',
            'products',
            'address_id',
            'agent_id',
            'coupon_code'
        ]);
        $rules = [

            'user_id' => 'required|numeric|exists:users,id',
            'address_id' => 'required|numeric|exists:address,id',
            'agent_id' => 'required|numeric|exists:agents,id',
            //            'coupon_id' => 'nullable|numeric|exists:coupons,id',
            'time_slot_id' => 'required|numeric|exists:time_slots,id',
            //            'schedule_slot_id' => 'required|numeric|exists:order_schedule_slots,id',
            'flat_location_id' => 'required|numeric|exists:delivery_flat_locations,id',
            //            'payment_type_id' => 'required|numeric|exists:payment_types,id',
            'type' => 'required|in:normal,offer',
            'delivery_date' => 'required|in:immediately,schedule',
            'delivery_schedule_date' => new RequiredIf($request->delivery_date == 'schedule'),
            'is_paid' => 'nullable',
            //            'payment_transaction_id' => 'nullable',
            //            'points' =>  new RequiredIf($request->use_points ==true ||$request->use_points ==1 ),
            //            'use_points' => 'required',
            'products.*.id' => 'required',
            'products.*.qty' => 'required',
            'products.*.price' => 'required',
            'coupon_code' => 'nullable',
        ];
        $validator = Validator::make($data, $rules);
        if ($validator->fails()) {
            return $this->newResponse(false, $this->validationHandle($validator->messages()));
        }
        try {
            $user = Customer::find($request->user_id);
            //            $cart=Cart::find($request->cart_id);
            $delivery_cost = 0;
            $coupon_discount = 0;
            $point_discount_value = 0;
            $use_customer_points = false;
            // coupon discount
            $use_points = false;
            $total_amount = 0;
            $total_qty = 0;
            foreach ($request->products as $product) {
                $total_amount += floatval($product['qty'] * $product['price']);
                $total_qty += $product['qty'];
            }


            // end coupon discount

            // delivery cost
            $delivery_flat_location_model = DeliveryFlatLocation::find($request->flat_location_id);
            if ($delivery_flat_location_model) {
                $delivery_cost_per_carton = $delivery_flat_location_model->delivery_cost;
                $delivery_cost = floatval(($total_qty * $delivery_cost_per_carton));
            }
            // end delivery cost



            // -----------------------new ahmed---------------------
            $address = Address::find($request->address_id);
            $agent_areas = new AgentArea();
            $agent_area = $agent_areas->search($address->lat, $address->lng);
            if($agent_area->minimum_cartons > $total_qty){
                return $this->response(false, 'يجب أن يكون عدد الكراتين أكبر من ' . $agent_area->minimum_cartons);
            }
            $coupon = null;
            if ($request->coupon_code != null) {
                $coupon = Coupon::where('code', $request->coupon_code)
                    ->where('status', 1)
                    ->where('notBefore', '<=', Carbon::now())->where('notAfter', '>=', Carbon::now())->first();
                if ($coupon != null) {

                    if ($coupon->minAmount > $total_amount) {
                        if ($user->language == 'ar') {
                            return $this->response(false, ' لاستخدام كود الخصم، يجب أن تكون تكلفة الطلب أكثر من  ' . $coupon->minAmount);
                        }
                        return $this->response(false, 'minimum amount to use this coupon code should be ' . $coupon->minAmount);
                    }
                    if ($coupon->type == 'flat') {
                        $coupon_discount = $coupon->value;
                    } else {
                        if ($coupon->value <= 1) // value  = percentage
                            $coupon_discount = $coupon->value * $total_amount;
                    }
                } else {
                    if ($user->language == 'ar')
                        return $this->response(false, 'كود الخصم غير صحيح');

                    return $this->response(false, 'not valid coupon');
                }
            }
            // -----------------------new ahmed---------------------

            $amount_after_coupon_discount = floatval(($total_amount - $coupon_discount));
            //  tax calculation
            $tax_ratio = Setting::valueOf('tax_ratio', 0);
            $tax = (($tax_ratio / 100) * ($amount_after_coupon_discount + $delivery_cost));
            //  end calculation

            $net_total_amount_before_points = $amount_after_coupon_discount + $tax + $delivery_cost;

            
            // ----------------------start point discount-----------
            if ($request->points > 0) {
                $use_points = true;
                if ($user->points >= $request->points) {
                    $request->points = $this->convertEnglishNumber($request->points);
                    $min_points_to_replace = Setting::valueOf('min_points_to_replace');
                    if ($request->points < $min_points_to_replace) {
                        return $this->newResponse(false, __('api.system_min_point_to_use', ['points_num' => $min_points_to_replace]));
                    } else {
                        $points_per_1_sar = Setting::valueOf('replace_points');
                        $points_money = ($request->points / $points_per_1_sar);
                        if ($points_money <= $net_total_amount_before_points) {
                            $point_discount_value = $points_money;
                            $use_customer_points = true;
                        } else {
                            return $this->newResponse(false, __('api.use_less_points_in_order'));
                        }
                    }
                } else {
                    return $this->newResponse(false, __('api.not_enough_points'));
                }
            }
            // ---------------------end point discount--------------------
            
            $total_discount = $coupon_discount + $point_discount_value;
            $total_order_amount = floatval($net_total_amount_before_points - $point_discount_value);


            // ---------
            $order_data['userId'] = $user->id;
            $order_data['assignDate'] = Carbon::now();
            $order_data['addressId'] = $request->address_id;
            $order_data['agentId'] = $request->agent_id;
            $order_data['coupon_id'] = $coupon != null ? $coupon->id : null;
            $order_data['coupon'] = $coupon != null ? $coupon->code : null;
            $order_data['time_slot_id'] = $request->time_slot_id;
            $order_data['schedule_slot_id'] = 1;
            $order_data['flat_location_id'] = $request->flat_location_id;
            $order_data['payment_type_id'] = 1;
            $order_data['type'] = $request->type;
            $order_data['sub_total'] = $total_amount;
            $order_data['total_discount'] = $total_discount;
            $order_data['sub_total_2'] = $amount_after_coupon_discount;
            $order_data['tax_ratio'] = $tax_ratio;
            $order_data['tax'] = $tax;
            $order_data['amount'] = $total_order_amount;
            $order_data['delivery_cost'] = $delivery_cost;
            $order_data['use_points'] = $use_points;
            $order_data['points'] = $request->points;
            $order_data['pointsDiscount'] = $point_discount_value;
            $order_data['couponDiscount'] = $coupon_discount;
            $order_data['delivery_schedule_date'] = $request->delivery_schedule_date ? Carbon::parse($request->delivery_schedule_date)->format('Y-m-d') : null;
            $order_data['is_paid'] = $request->is_paid;
            $order_data['creatable_type'] = $request->order_created_by;
            //            $order_data['payment_transaction_id']=$request->payment_transaction_id;
            //            $order_data['paymentReference']=$request->payment_transaction_id;

            $order = Order::create($order_data);

            if ($order) {
                // $order->creatable()->associate($user)->save();
                if ($request->use_points && $use_customer_points) {
                    $pointsRecord = new Points();
                    $pointsRecord->clientId = $order->userId;
                    $pointsRecord->type = 'discount';
                    $pointsRecord->points = $request->points;
                    $pointsRecord->orderId = $order->id;
                    $pointsRecord->agentId = $order->agentId;
                    $pointsRecord->save();
                    $user_points_after_discount_it = intval($user->points - $request->points);
                    $user->update(['points' => $user_points_after_discount_it]);
                }
                $orderProducts = [];
                // add products to order
                foreach ($request->products as $cartProduct) {
                    $orderProducts[] = [
                        'orderId' => $order->id,
                        'productId' => $cartProduct['id'],
                        'amount' => $cartProduct['qty'],
                        'price' => $cartProduct['price'],
                        'total' => floatval($cartProduct['qty'] * $cartProduct['price']),
                        'created_at' => Carbon::now(),
                    ];
                }
                OrderProduct::insert($orderProducts);
                // clear cart


                // add new notification that order created
                Notification::send($user, new OrderCreated($order));
                if ($user->fcmToken) {
                    $this->sendNotification($user->fcmToken, 'App\Notifications\OrderCreated', app()->getLocale());
                }
                $agent = Agent::find($request->agent_id);

                if ($agent) {

                    Notification::send($agent, new OrderCreated($order));
                    $this->sendNotification($agent->fcmToken, 'App\Notifications\OrderCreatedAgent', $agent->language);
                }
                if ($user->agent_id != $request->agent_id) {
                    $user->agent_id = $request->agent_id;
                    $user->save();
                }
            } else {
                return $this->newResponse(false, __('api.fails_response'));
            }
        } catch (\Exception $e) {
            \Log::info('Create new normal order exception mobile : ' . $e->getMessage());
            return $this->newResponse(false, $e->getMessage()); //_('api.failed_place_order')
        }
        return $this->newResponse(true, __('api.order_has_been_sent_successfully'));
    }

    public function UpdateTimeSlot(Request $request)
    {
        try {
            $data = $request->only('orderId', 'agentId', 'time_slot_id');
            $rules = [
                'orderId' => 'required|numeric',
                'agentId' => 'required|numeric',
                'time_slot_id' => 'required|numeric',
            ];
            $validator = Validator::make($data, $rules);
            if ($validator->fails()) {
                return $this->response(false, $this->validationHandle($validator->messages()));
            } else {
                $order = Order::where('id', $request->orderId)->where('agentId', $request->agentId)->first();
                if ($order == null) {
                    return $this->response(false, 'order is not found');
                }

                DB::beginTransaction();
                $order->time_slot_id = $request->time_slot_id;
                $order->save();
                DB::commit();
                return $this->response(true, 'success', $order);
            }
        } catch (Exception $e) {
            DB::rollBack();
            return $this->response(false, 'system error');
        }
    }

    public function MostOrderUsers()
    {
        $orders['most_purchased_carton'] = Order::with('customer')->addSelect(DB::raw('SUM(amount) as purchased_carton, userId'))
                ->groupBy('userId')->take(20)
                ->orderBy('amount', 'DESC')->get();

        $orders['most_purchased_orders'] = Order::with('customer')->addSelect(DB::raw('count(*) as orders_count, userId'))
                ->groupBy('userId')->take(20)
                ->orderBy('orders_count', 'DESC')->get();

        // $data = new MostOrderUser($orders);
        return $this->response(true, 'success',  $orders);
    }

    public function UpdateCartonAmount(Request $request)
    {
        $data = $request->only(['orderId', 'productId', 'amount']);
        $rules = [
            'orderId' => 'required|numeric',
            'productId' => 'required|numeric',
            'amount' => 'required|numeric',
        ];
        try {
            $validator = Validator::make($data, $rules);
            if ($validator->fails()) {
                return $this->response(false, $this->validationHandle($validator->messages()));
            } else {
                $order = Order::where('id', $request->orderId)->where('payment_type_id', 1)->where('status', 'created')->first();
                if($order){

                    //update the amount and total in order product table
                    $order_product = OrderProduct::where('orderId', $request->orderId)->where('productId', $request->productId)->first(); 
                    // $points_which_deducts_from_total = $order_product->amount * 10;
                    $total_per_product = $order_product->total;
                    $order_product->update([
                        'amount' => $request->amount,
                        'total' => (double)$request->amount * (double)$order_product->price
                    ]);

                    //update the amount in the orders table
                    $amount = (int)$order->amount - (int)$total_per_product;
                    $order->update([
                        'amount' =>  $amount + (int)$order_product->total,
                    ]);

                    //update order points
                    // $order_points = Points::where('orderId', $request->orderId)->first();
                    // if($order_points){
                    //     $points = (int)$order_points->points - (int)((int)$points_which_deducts_from_total) + (int)$request->amount * 10;
                    //     $order_points->update([
                    //         'points' => $points
                    //     ]); 
                    // }

                    return $this->response(true, 'success', $order_product);
                }else{
                    return $this->response(false, 'Order not found or not match conditions');
                }
            }
        } catch (\Throwable $th) {
            return $this->response(false, $th->getMessage());
        }
    }
}
