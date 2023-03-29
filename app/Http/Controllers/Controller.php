<?php

namespace App\Http\Controllers;
use App\Http\Resources\BannarImageResource;
use App\Http\Resources\ProductResource;
use App\Models\CartProduct;
use App\Jobs\FirebaseTopicJoin;
use App\Jobs\SendGoogleNotification;
use App\Jobs\SendSms;
use App\Jobs\FirebaseSendToTopic;
use App\Models\Cart;
use App\Models\Banner;
use App\Models\Agent;
use App\Models\Customer;
use App\Models\FavoriteProduct;
use App\Models\Product;
use App\Models\AgentProduct;
use App\Models\Sms;
use Illuminate\Foundation\Auth\Access\AuthorizesRequests;
use Illuminate\Foundation\Bus\DispatchesJobs;
use Illuminate\Foundation\Validation\ValidatesRequests;
use App\Models\Points;
use DB;
use Grimzy\LaravelMysqlSpatial\Types\Polygon;
use Grimzy\LaravelMysqlSpatial\Types\LineString;
use Grimzy\LaravelMysqlSpatial\Types\Point;
use Illuminate\Routing\Controller as BaseController;
use DateTime;
use Exception;
use Illuminate\Support\Str;
use Carbon\Carbon;
use Log;


class Controller extends BaseController
{
    use AuthorizesRequests, DispatchesJobs, ValidatesRequests;

    protected $pageSize = 1;
    // protected $base_url = 'http://165.22.96.231';

    protected function sendOTP(Customer $user){
        $otp= rand(1000, 9999);
        // $otp= '0000';

        $this->sendSms('Your OTP is '.$otp,$user->mobile);
        $data = ['userId' => $user->id,'otp' => $otp,'status' => 3];
        Sms::create($data);
        return $otp;
    }
    protected function createUserToken(Customer $user){
        $user->tokens()->delete();
        $token= $user->createToken('mobile_token')->plainTextToken;
        return $token;
    }
    function response($status,$message,$data = ''){

        $response['status'] = $status;
        $response['message'] = $message;
        if($data == null){
            $response['data'] =  new Exception;
        } else {
            $response['data'] = $data;

        }

        return response($response, 200)->header('Content-Type', 'application/json');
    }

    function newResponse($status,$message,$dataKey='',$data = '',$extraData=[],$code=200){

        $response['status'] = $status;
        $response['message'] = $message;
      if($dataKey){
            $response[$dataKey] = $data;

        }
        foreach($extraData as $key=>$dataa){
            $response[$key]=$dataa;
        }
        return response($response, $code)->header('Content-Type', 'application/json');
    }

    function responseArray($status,$message){

        $response['status'] = $status;
        $response['message'] = $message;
        $response['data'] =  [];
        return response($response, 200)->header('Content-Type', 'application/json');
    }
    function validationHandle($validation){
        foreach ($validation->getMessages() as $field_name => $messages){
            if(!isset($firstError)){
                $firstError        =$messages[0];
                $error[$field_name]=$messages[0];
            }
        }
        return $firstError;
    }

    protected function getArabicStatus($status){
        switch ($status) {
            case 'created':
                return 'تم الإنشاء';
                    break;
            case 'completed':
                return 'مكتمل';
                    break;
            case 'cancelledByApp':
                return 'تم رفض الطلب';
                    break;
            case 'cancelledByClient':
                return 'تم إلغاء الطلب';
                    break;
            default:
               return '';
        }
    }

    protected function getEnglishStatus($status){
        switch ($status) {
            case 'created':
                return 'Created';
                    break;
            case 'completed':
                return 'Completed';
                    break;
            case 'cancelledByApp':
                return 'Reject by application';
                    break;
            case 'cancelledByClient':
                return 'Cancelled by client';
                    break;
            default:
               return '';
        }
    }

    protected function getEnglishNotificationTitle($type){
        switch ($type) {
            case 'App\Notifications\PointUsed':
                return 'Use points';
                    break;
            case 'App\Notifications\OrderCreatedDifferentAgent':
                return 'New order from different region';
                    break;
            case 'App\Notifications\OrderReviewed':
                return 'Review Order';
                    break;
            case 'App\Notifications\OrderCreated':
                return 'New Order';
                    break;
            case 'App\Notifications\OrderCreatedAgent':
                return 'New Order';
                    break;
            case 'App\Notifications\OrderCancelled':
                return 'Cancel Order';
                    break;
            case 'App\Notifications\OrderAssigned':
                return 'Assign New Order';
                    break;
            case 'App\Notifications\OrderCompleted':
                return 'Order Completed';
            case 'App\Notifications\OrderOnTheWay':
                return 'Your order on the way ';
                    break;
            case 'App\Notifications\ComplainCreated':
                return 'New Complain';
                    break;
            case 'App\Notifications\PasswordChanged':
                return 'Reset Password';
                    break;
                    case 'App\Notifications\OrderOnTheWay':
                return 'Delivery in progress';
                    break;
            default:
               return '';
        }
    }

    protected function getArabicNotificationTitle($type){
        switch ($type) {
            case 'App\Notifications\OrderOnTheWay':
                return 'جاري توصيل الطلب';
                break;
            case 'App\Notifications\PointUsed':
                return 'استخدام نقاط الولاء';
                    break;
            case 'App\Notifications\OrderCreatedDifferentAgent':
                return 'طلب جديد من منطقة جغرافية مختلفة';
                    break;
            case 'App\Notifications\OrderReviewed':
                return 'تقييم الطلب';
                    break;
            case 'App\Notifications\OrderCreated':
                return 'طلب جديد';
                    break;
            case 'App\Notifications\OrderCreatedAgent':
                    return 'طلب جديد';
                        break;
            case 'App\Notifications\OrderCancelled':
                return 'إلغاء طلب';
                    break;
            case 'App\Notifications\OrderAssigned':
                return 'إسناد طلب جديد';
                    break;
            case 'App\Notifications\OrderCompleted':
                return 'اكتمل الطلب';
            case 'App\Notifications\OrderOnTheWay':
                return 'جاري توصيل طلبك ';
                    break;
            case 'App\Notifications\ComplainCreated':
                return 'شكوى جديدة';
                    break;
            case 'App\Notifications\PasswordChanged':
                return 'تغيير كلمة المرور';
                    break;
            default:
               return '';
        }
    }
    protected function getEnglishNotificationDescription($type){
        switch ($type) {
            case 'App\Notifications\OrderOnTheWay':
                return 'Your order delivery in progress';
                break;
            case 'App\Notifications\PointUsed':
                return 'You have used some points';
                    break;
            case 'App\Notifications\OrderCreatedDifferentAgent':
                return 'Order has been assigend successfully, but Prices maybe different in selected region';
                    break;
            case 'App\Notifications\OrderReviewed':
                return 'ُThanks for your review';
                    break;
            case 'App\Notifications\OrderCreated':
                return 'New Order has been created successfully';
                    break;
            case 'App\Notifications\OrderCreatedAgent':
                return 'ٌYou have new order';
                    break;
            case 'App\Notifications\OrderCancelled':
                return 'Order has been cancelled successfully';
                    break;
            case 'App\Notifications\OrderAssigned':
                return 'Order has been assigend successfully';
                    break;
            case 'App\Notifications\OrderAssignedDelegator':
                return 'Order has been assigend to you successfully';
                    break;
            case 'App\Notifications\OrderCompleted':
                return 'Order has been completed successfully';

            case 'App\Notifications\OrderOnTheWay':
                return 'Your order on the way';

                    break;
            case 'App\Notifications\OrderCompletedClient':
                return 'Order has been completed successfully, Please rate the service';
                    break;
            case 'App\Notifications\ComplainCreated':
                return 'Complain is created successfully';
                    break;
            case 'App\Notifications\PasswordChanged':
                return 'Your password has been changed successfully';
                    break;
            default:
               return '';
        }
    }

    protected function getArabicNotificationDescription($type){
        switch ($type) {
            case 'App\Notifications\OrderOnTheWay':
                return 'جاري توصيل الطلب';
                break;
            case 'App\Notifications\PointUsed':
                return 'لقد تم استخدام نقاط من رصيدك';
                    break;
            case 'App\Notifications\OrderCreatedDifferentAgent':
                return 'تم إنشاء الطلب بنجاح ولكن ستختلف الاسعار بحسب المنطقة الجغرافية';
                    break;
            case 'App\Notifications\OrderReviewed':
                return 'تم استقبال التقييم، شكراً';
                    break;
            case 'App\Notifications\OrderCreated':
                return 'تم تسجیل طلبك، سوف تتم خدمتك بأسرع وقت ممكن';
                    break;
            case 'App\Notifications\OrderCreatedAgent':
                return 'ٌلديك طلب جديد';
                    break;
            case 'App\Notifications\OrderCancelled':
                return 'لم یكتمل طلبك ،تواصل معنا';
                    break;
            case 'App\Notifications\OrderAssigned':
                return 'تم إسناد الطلب للموزع بنجاح';
                    break;
            case 'App\Notifications\OrderAssignedDelegator':
                return 'تم إسناد الطلب لك بنجاح';
                    break;
            case 'App\Notifications\OrderCompleted':
                return 'تم توصيل الطلب بنجاح';

            case 'App\Notifications\OrderOnTheWay':
                return 'جاري توصيل طلبك';

            case 'App\Notifications\OrderCompleted':
                return 'تم توصيل الطلب بنجاح';
                    break;
            case 'App\Notifications\OrderCompletedClient':
                return 'تم توصيل الطلب بنجاح، الرجاء تقييم الخدمة';
                    break;
            case 'App\Notifications\ComplainCreated':
                return 'تم إرسال الشكوى بنجاح';
                    break;
            case 'App\Notifications\PasswordChanged':
                return 'تم تغيير كلمة المرور بنجاح';
                    break;
            default:
               return '';
        }
    }


    protected function convertOrderToMobile($order){
            $assignDate = new DateTime($order->assignDate);
            $date = new DateTime($order->created_at);
            $completionDate = new DateTime($order->completionDate);
            $order->date=$date->format('d-m-Y');

            switch ($order->status) {
                case 'created':
                    // if($order->assignDate != null){
                    //     $created_at = new DateTime($order->created_at);
                    //     // $order->assignDate = $created_at->format('d-m-Y H:i:s');
                    //     $assignDate = new DateTime($order->created_at);
                    //     $order->assignDate=$assignDate->format('Y-m-d H:i:s');
                    // }
                    if($order->created_at->addDays(2)> Carbon::now())
                    {
                        $order->isDelayed = false;
                    } else {
                        $order->isDelayed = true;
                    }
                    $order->date=$date->format('d-m-Y');

                    break;
                case 'completed':
                    // $order->date=$completionDate->format('d-m-Y'); break;
                case 'cancelledByApp':
                    // $rejectionDate = new DateTime($order->rejectionDate);
                    // $order->date=$rejectionDate->format('d-m-Y'); break;
                case 'cancelledByClient':
                    // $cancelDate = new DateTime($order->cancelDate);
                    // $order->date=$cancelDate->format('d-m-Y'); break;
                default:
                    // $order->date=$date->format('d-m-Y');
            }
            if($order->paymentReference != null)
            {
                $order->paymentType = 'online';
            }
            else {
                $order->paymentType = 'cash';
            }

            // CONTROL MESSAGES OF STATUS
            $order->arabicStatusMessage=$this->getArabicStatus($order->status);
            $order->englishStatusMessage=$this->getEnglishStatus($order->status);
    }

    protected function time_elapsed_string($datetime, $full = false) {
        $now = new DateTime;
        $ago = new DateTime($datetime);
        $diff = $now->diff($ago);

        $diff->w = floor($diff->d / 7);
        $diff->d -= $diff->w * 7;

        $string = array(
            'y' => 'year',
            'm' => 'month',
            'w' => 'week',
            'd' => 'day',
            'h' => 'hour',
            'i' => 'minute',
            's' => 'second',
        );
        foreach ($string as $k => &$v) {
            if ($diff->$k) {
                $v = $diff->$k . ' ' . $v . ($diff->$k > 1 ? 's' : '');
            } else {
                unset($string[$k]);
            }
        }

        if (!$full) $string = array_slice($string, 0, 1);
        return $string ? implode(', ', $string) . ' ago' : 'just now';
    }

    protected function time_elapsed_stringArabic($datetime, $full = false) {
        $now = new DateTime;
        $ago = new DateTime($datetime);
        $diff = $now->diff($ago);

        $diff->w = floor($diff->d / 7);
        $diff->d -= $diff->w * 7;

        $string = array(
            'y' => 'سنة',
            'm' => 'شهر',
            'w' => 'اسبوع',
            'd' => 'يوم',
            'h' => 'ساعة',
            'i' => 'دقيقة',
            's' => 'ثانية',
        );
        foreach ($string as $k => &$v) {
            if ($diff->$k) {
                if($diff->$k > 1){
                    $v = $diff->$k . ' ' . $v ;

                    if (Str::contains($v, 'سنة')){
                        $v = Str::replaceLast("سنة", "سنوات", $v);
                    }
                    if (Str::contains($v, 'شهر')){
                        $v = Str::replaceLast("شهر", "أشهر", $v);
                    }
                    if (Str::contains($v, 'اسبوع')){
                        $v = Str::replaceLast("اسبوع", "آسابيع", $v);
                    }
                    if (Str::contains($v, 'ساعة')){
                        $v = Str::replaceLast("ساعة", "ساعات", $v);
                    }
                    if (Str::contains($v, 'دقيقة')){
                        $v = Str::replaceLast("دقيقة", "دقائق", $v);
                    }
                    if (Str::contains($v, 'ثانية')){
                        $v = Str::replaceLast("ثانية", "ثواني", $v);
                    }
                }
            } else {
                unset($string[$k]);
            }
        }

        if (!$full) $string = array_slice($string, 0, 1);
        return $string ?  ' منذ '.implode(', ', $string)  : 'الآن';
    }

    protected function sendNotification($token,$type,$language,$order=null){

        $title = '';
        $body = '';
        if($language == 'ar'){
            $title = $this->getArabicNotificationTitle($type);
            $body = $this->getArabicNotificationDescription($type);
        } else {
            $title = $this->getEnglishNotificationTitle($type);
            $body = $this->getEnglishNotificationDescription($type);
        }
        if($token){

        SendGoogleNotification::dispatch($token,$title,$body,$order);
        }
    }

    protected function respondWithToken($token)
    {
        return response()->json([
            'access_token' => $token,
            'token_type'   => 'bearer',
            'expires_in' => auth('agent')->factory()->getTTL() * 60
            // 'expires_in'   => auth()->factory()->getTTL() * 60
        ]);
    }
    protected function addProductToCart($userId,$agentId,$address,$amount,$productId){


        try{
            $cart = Cart::where('userId',$userId)->first();

            if($cart){
                CartProduct::updateOrCreate(['cartId' => $cart->id, 'productId' =>$productId], ['amount' => $amount]);
                $cart->address_id=$address->id;
                $cart->agentId=$agentId;
                $cart->addressType=$address->type;
                if($cart->save()){
                    return $cart;
                }


//            return $this->response(true,'success');
            } else {
                // create new cart
                $cartData = ['userId' => $userId,
                             'agentId' => $agentId,
                             'addressType' => $address->type,
                             'address_id'=>$address->id
                            ];
                $newCart =  Cart::create($cartData)->id;
                // return $newCart;
                if($newCart)
                {
                    CartProduct::updateOrCreate(
                        ['cartId' => $newCart, 'productId' =>$productId],
                        ['amount' => $amount]);
                    // return all products of cart
                    return Cart::find($newCart);

                } else {
                    return null;
                }
            }
        }catch (\Exception $e){
            Log::info('unable to add product to cart ::::'.$e->getMessage());
        }

    }

    protected function addToCart($userId,$agentId,$addressType,$amount,$productId){

        // remove other cart in other agent if there
        Cart::where('userId',$userId)->where('agentId','<>',$agentId)
        ->where('addressType',$addressType)->delete();
        // end remove other cart
        $cart = Cart::where('userId',$userId)->where('agentId',$agentId)
            ->where('addressType',$addressType)->first();
            if($cart == null){
                // create new cart
                $cartData = ['userId' => $userId,'agentId' => $agentId,'addressType' => $addressType];
                $newCart =  Cart::create($cartData);
                // return $newCart;
                if($newCart != null)
                {
                    // add product to new cart
                    $record = CartProduct::updateOrCreate(
                        ['cartId' => $newCart->id,
                         'productId' =>$productId],
                        ['amount' => $amount]);
                    // return all products of cart
                    $newCart->products = $record->getProductsOfCart($newCart->id);
                    return $newCart;
                    // return $this->response(true,'success',$newCart);
                } else {
                    // return $this->response(false,'failed to create new cart');
                    return null;
                }
                return $this->response(true,'success');
            } else {
                $record = CartProduct::updateOrCreate(
                    ['cartId' => $cart->id,
                    'productId' =>$productId],
                    ['amount' => $amount]
                );
                $cart->products = $record->getProductsOfCart($cart->id);
                return $cart;
            }
    }

    protected function convertPolygon($agent){

        // $agent->area2 = ;
        $points = array();

        foreach($agent->area[0] as $point) {
            $lat = $point->getLat();
            $lng = $point->getLng();
            $point  = array();
            $point[] = $lat;
            $point[] = $lng;
            $points[] = $point;
          }
        $agent->area = $points;
        return $agent;
    }
    protected function getProductsInMainScreen2($request,$id,$addressType){
        $agent = new Agent();
        // if  user is testing user, return default agent, it is used to test with apple
        if($id == 1163){
            $agent = $agent->getDefault();
        }else{
            $agent = $agent->search($request->lat,$request->lng);
        }

        if($agent == null)
        {
            // return $request->lat;
            $results = new Agent;
            $banners = Banner::where('status',1)->get();

            foreach($banners as $banner){
                $banner->picture = url('/').$banner->picture;
            }
            $banners = $banners->pluck('picture');
            $results->banners = $banners;
            return $results;
        }
        // return $agent;
        $results = new Agent;
        $banners = Banner::where('status',1)->get();
        foreach($banners as $banner){
            $banner->picture = url('/').$banner->picture;
        }
        $banners = $banners->pluck('picture');
        $results->banners = $banners;

        // add agent id
        $results->agentId = $agent->id;

        $products = Product::leftJoin('agentProducts','agentProducts.productId','=','products.id')
            ->where('agentProducts.agentId',$agent->id)
            ->where('products.type', 1)
            // ->where('agentProducts.status',1)
            ->select('products.id as id','arabicName','englishName','picture','agentProducts.mosquePrice','agentProducts.homePrice','agentProducts.officialPrice','agentProducts.otherPrice','agentProducts.status')
            ->orderBy('agentProducts.status')
            ->get();

        foreach($products as $product){
            $product->picture = url('/').$product->picture;
        }
        if($id == null)
        {
            foreach($products as $product){
                $product->isFavorite = 0;
            }
        } else{
            $favoriteProducts = FavoriteProduct::where('userId',$id)->get();//->pluck('productId');

            $cartProducts = CartProduct::join('cart','cart.id','=','cart_products.cartId')
                ->where('cart.agentId',$agent->id)
                ->where('cart.addressType',$addressType)
                ->where('cart.userId',$id)
                ->select('cart_products.productId','cart_products.amount')
                ->get();//->pluck('productId');
            // return  $cartProducts;
            foreach ($products as $product) {
                if($favoriteProducts->contains('productId', $product->id)) {
                    $product->isFavorite = 1;
                }else{
                    $product->isFavorite = 0;
                }

                // search in list
                $temp  = $cartProducts->where('productId', $product->id)->first();
                // return $temp;
                if( $temp == null){
                    $product->amountInCart = 0;
                } else {
                    $product->amountInCart = $temp->amount;
                }

            }
        }
        $results->products = $products;

        return $results;
    }
    protected function getProductsInMainScreen($request,$id,$addressType){
        $agent = new Agent();
        // if  user is testing user, return default agent, it is used to test with apple
        if($id == 1163){
            $agent = $agent->getDefault();
        }else{
            $agent = $agent->search($request->lat,$request->lng);
        }

        if($agent == null)
        {
            // return $request->lat;
//            $results = new Agent;
            $banners = Banner::where('status',1)->get();
//            $results->banners = $banners;
            return BannarImageResource::collection($banners);
        }
        // return $agent;
        $results = new Agent;
        $banners = Banner::where('status',1)->get();
        $banners = BannarImageResource::collection($banners);
        $results->banners = $banners;

        // add agent id
        $results->agentId = $agent->id;

        $products = Product::leftJoin('agentProducts','agentProducts.productId','=','products.id')
            ->where('agentProducts.agentId',$agent->id)
            ->where('products.type', 1)
            // ->where('agentProducts.status',1)
            ->select('products.id as id','arabicName','englishName','picture','agentProducts.mosquePrice','agentProducts.homePrice','agentProducts.officialPrice','agentProducts.otherPrice','agentProducts.status')
            ->orderBy('agentProducts.status')
            ->paginate(10);



        $results->products = ProductResource::collection($products);
        $results->has_more_pages =$products->hasMorePages();

        return $results;
    }

    protected function getProductsInMainScreen1($request,$id,$addressType){
        $agent = new Agent();
//        // if  user is testing user, return default agent, it is used to test with apple
//        if($id == 1163){
//            $agent = $agent->getDefault();
//        }else{
//            $agent = $agent->search($request->lat,$request->lng);
//        }

        if($agent == null)
        {
            // return $request->lat;
            $results = new Agent;
            $banners = Banner::where('status',1)->get();

            $results->banners = BannarImageResource::collection($banners);
            return $results;
        }
        // return $agent;
        $results = new Agent;
        $banners = Banner::where('status',1)->get();
        $banners = BannarImageResource::collection($banners);
        $results->banners = $banners;

        // add agent id
        $results->agentId = $agent->id;

        $products = Product::leftJoin('agentProducts','agentProducts.productId','=','products.id')
        ->where('agentProducts.agentId',$agent->id)
        ->where('products.type', 1)
        // ->where('agentProducts.status',1)
        ->select('products.id as id','arabicName','englishName','picture','agentProducts.mosquePrice','agentProducts.homePrice','agentProducts.officialPrice','agentProducts.otherPrice','agentProducts.status')
        ->orderBy('agentProducts.status')
       ->get();

        foreach($products as $product){
            $product->picture = url('/').$product->picture;
        }
        if($id == null)
        {
            foreach($products as $product){
                $product->isFavorite = 0;
            }
        } else{
            $favoriteProducts = FavoriteProduct::where('userId',$id)->get();//->pluck('productId');

            $cartProducts = CartProduct::join('cart','cart.id','=','cart_products.cartId')
            ->where('cart.agentId',$agent->id)
            ->where('cart.addressType',$addressType)
            ->where('cart.userId',$id)
            ->select('cart_products.productId','cart_products.amount')
            ->get();//->pluck('productId');
            // return  $cartProducts;
            foreach ($products as $product) {
                if($favoriteProducts->contains('productId', $product->id)) {
                    $product->isFavorite = 1;
                }else{
                    $product->isFavorite = 0;
                }

                // search in list
                $temp  = $cartProducts->where('productId', $product->id)->first();
                // return $temp;
                if( $temp == null){
                    $product->amountInCart = 0;
                } else {
                    $product->amountInCart = $temp->amount;
                }

            }
        }
        $results->products = $products;

        return $results;
    }

    protected function getOffersInMainScreen($request,$id,$addressType){
        $agent = new Agent;
        // if  user is testing user, return default agent, it is used to test with apple
        if($id == 1163){
            $agent = $agent->getDefault();
        }else{
            $agent = $agent->search($request->lat,$request->lng);
        }

        if($agent == null)
        {
            // return $request->lat;
            $results = new Agent;
            $banners = Banner::where('status',1)->get();

            foreach($banners as $banner){
                $banner->picture = url('/').$banner->picture;
            }
            $banners = $banners->pluck('picture');
            $results->banners = $banners;
            return $results;
        }
        // return $agent;
        $results = new Agent;
        $banners = Banner::where('status',1)->get();
        foreach($banners as $banner){
            $banner->picture = url('/').$banner->picture;
        }
        $banners = $banners->pluck('picture');
        $results->banners = $banners;

        // add agent id
        $results->agentId = $agent->id;

        // get products from agentProducts table
        // $agentProdcuts = AgentProduct::where('agentId',$agent->id)->where('status',1)->pluck('productId');
        // return $agentProdcuts;
        // $products = Product::whereIn('id',$agentProdcuts)->get();
        $products = Product::leftJoin('agentProducts','agentProducts.productId','=','products.id')
        ->where('agentProducts.agentId',$agent->id)
        ->where('products.type', 2)
        // ->where('agentProducts.status',1)
        ->select('products.id as id','arabicName','englishName','picture','agentProducts.mosquePrice','agentProducts.homePrice','agentProducts.officialPrice','agentProducts.otherPrice','agentProducts.status')
        ->orderBy('agentProducts.status')
       ->get();

        foreach($products as $product){
            $product->picture = url('/').$product->picture;
        }
        if($id == null)
        {
            foreach($products as $product){
                $product->isFavorite = 0;
            }
        } else{
            $favoriteProducts = FavoriteProduct::where('userId',$id)->get();//->pluck('productId');

            $cartProducts = CartProduct::join('cart','cart.id','=','cart_products.cartId')
            ->where('cart.agentId',$agent->id)
            ->where('cart.addressType',$addressType)
            ->where('cart.userId',$id)
            ->select('cart_products.productId','cart_products.amount')
            ->get();//->pluck('productId');
            // return  $cartProducts;
            foreach ($products as $product) {
                if($favoriteProducts->contains('productId', $product->id)) {
                    $product->isFavorite = 1;
                }else{
                    $product->isFavorite = 0;
                }

                // search in list
                $temp  = $cartProducts->where('productId', $product->id)->first();
                // return $temp;
                if( $temp == null){
                    $product->amountInCart = 0;
                } else {
                    $product->amountInCart = $temp->amount;
                }

            }
        }
        $results->products = $products;

        return $results;
    }


    function convert($string) {
        $arabic = ['٩', '٨', '٧', '٦', '٥', '٤', '٣', '٢', '١','٠'];
        $num = range(0, 9);
        $englishNumbersOnly = str_replace($arabic, $num, $string);
        return $englishNumbersOnly;
    }
    function convertEnglishNumber($string) {
        $arabic = ['٩', '٨', '٧', '٦', '٥', '٤', '٣', '٢', '١','٠'];
        $num = range(0, 9);
        $englishNumbersOnly = str_replace($arabic, $num, $string);
        return $englishNumbersOnly;
    }

    public function sendToTopic($topicName,$title,$body) {
        FirebaseSendToTopic::dispatch($topicName,$title,$body);
    }
    public function subscribeTopic($token, $topicName) {
        FirebaseTopicJoin::dispatch($token,$topicName);
    }
    public function sendSms($message, $number) {
        SendSms::dispatchNow($message,$number);
    }

    protected function transformArabicNumbers($value)
    {
        // if (in_array($key, $this->include, true)) {
            return strtr($value, array('۰'=>'0', '۱'=>'1', '۲'=>'2', '۳'=>'3', '۴'=>'4', '۵'=>'5', '۶'=>'6', '۷'=>'7', '۸'=>'8', '۹'=>'9',
             '٠'=>'0', '١'=>'1', '٢'=>'2', '٣'=>'3', '٤'=>'4', '٥'=>'5', '٦'=>'6', '٧'=>'7', '٨'=>'8', '٩'=>'9'));
        // }
        return $value;


    }

    protected function getPointsOfUser($user){
        // get points from points table
        $points = Points::where('clientId',$user->id)
        ->select(DB::raw('type, sum(points) as sum' ))
        ->whereDate('created_at', '>', \Carbon\Carbon::now()->subMonth(4))
        ->groupBy('type')->get();
        if(count($points) > 0){
            $user->points = 0;
            foreach ($points as $point) {
                if($point->type == 'bonus'){
                    $user->points = $user->points + $point->sum;
                }
                if($point->type == 'discount'){
                    $user->points = $user->points - $point->sum;
                }
            }
            if($user->points<0){
                $user->points = 0;
            }

        } else{
            $user->points = 0;
        }
       return $user->points ;
    }

}
