<?php

namespace App\Http\Controllers;

use App\Models\Agent;
use App\Models\Notification;
use App\Models\User;
use App\Notifications\AgentUserMessage;
use App\Notifications\FireBaseNotify;
use App\Traits\FireBaseNotify as TraitsFireBaseNotify;
use Illuminate\Support\Facades\Validator;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Notification as FacadesNotification;

class NotificationController extends Controller
{
    use TraitsFireBaseNotify;

    //list all
    public function list(Request $request)
    {
        try {
            return $this->response(true,'success',Notification::all());
        } catch (Exception $e) {
            return $this->response(false,'system error');
        }


    }
    //list client Notificaiton
    public function listClientNotifications()
    {

        try {
            $user = auth()->guard('api')->user();
            $list = $user->notifications;
            $list->each(function ($item, $key) {
                $item->agoArabic=parent::time_elapsed_stringArabic($item->created_at);
                $item->arabicTitle= parent::getArabicNotificationTitle($item->type);
                $item->title= parent::getEnglishNotificationTitle($item->type);
                $item->arabicDescription= parent::getArabicNotificationDescription($item->type);
                $item->description= parent::getEnglishNotificationDescription($item->type);
                $item->ago=parent::time_elapsed_string($item->created_at);
            });
            $user->unreadNotifications->markAsRead();
            return $this->response(true,'success',$list);
        } catch (Exception $e) {
            return $this->response(false,'system error');
        }

    }
    //list industry Notificaiton
    public function listIndustryNotifications()
    {

        try {
            $user = auth()->guard('industry')->user();
            $list = $user->notifications;
            $list->each(function ($item, $key) {
                $item->agoArabic=parent::time_elapsed_stringArabic($item->created_at);
                $item->arabicTitle= parent::getArabicNotificationTitle($item->type);
                $item->title= parent::getEnglishNotificationTitle($item->type);
                $item->arabicDescription= parent::getArabicNotificationDescription($item->type);
                $item->description= parent::getEnglishNotificationDescription($item->type);
                $item->ago=parent::time_elapsed_string($item->created_at);
            });
            $user->unreadNotifications->markAsRead();
            return $this->response(true,'success',$list);

        } catch (Exception $e) {
            return $this->response(false,'system error');
        }

    }
    //list agent Notificaiton
    public function listAgentNotifications()
    {
        try {
            $agent = Auth::user();
            $list = $agent->notifications;
            $list->each(function ($item, $key) {
                $item->agoArabic=parent::time_elapsed_stringArabic($item->created_at);
                $item->arabicTitle= parent::getArabicNotificationTitle($item->type);
                $item->title= parent::getEnglishNotificationTitle($item->type);
                $item->arabicDescription= parent::getArabicNotificationDescription($item->type);
                $item->description= parent::getEnglishNotificationDescription($item->type);
                $item->ago=parent::time_elapsed_string($item->created_at);
            });
            $agent->unreadNotifications->markAsRead();
            return $this->response(true,'success',$list);
        } catch (Exception $e) {
            return $this->response(false,'system error');
        }

    }
    //list delegator Notificaiton
    public function listDelegatorNotifications()
    {


        try {
            $delegator = auth()->guard('delegator')->user();
            $list = $delegator->notifications;
            $list->each(function ($item, $key) {
                $item->agoArabic=parent::time_elapsed_stringArabic($item->created_at);
                $item->arabicTitle= parent::getArabicNotificationTitle($item->type);
                $item->title= parent::getEnglishNotificationTitle($item->type);
                $item->arabicDescription= parent::getArabicNotificationDescription($item->type);
                $item->description= parent::getEnglishNotificationDescription($item->type);
                $item->ago=parent::time_elapsed_string($item->created_at);
            });
            $delegator->unreadNotifications->markAsRead();
            return $this->response(true,'success',$list);
        } catch (Exception $e) {
            return $this->response(false,'system error');
        }

    }

    //create
    public function create(Request $request)
    {
        try {
                $data = $request->all();
            $rules = [
                'type' => 'required',
                'userId' => 'required',
                'userType' => 'required|in:client,industry,agent,delegator',
            ];
            $validator = Validator::make($data, $rules);
            if ($validator->fails()) {
                return $this->response(false,$this->validationHandle($validator->messages()));
            } else {
                $newRecord =  Notification::create($data);
                return $this->response(true,'success',$newRecord);
            }
        } catch (\Exception $e) {
            return $this->response(false,'system error');
        }

    }

    //testSendFCM
    public function testSendFCM(Request $request)
    {
        $type = '';

        switch ($request->type) {
            case 'PointUsed':
                $type = 'App\Notifications\PointUsed';
                    break;
            case 'OrderCreatedDifferentAgent':
                $type = 'App\Notifications\OrderCreatedDifferentAgent';
                    break;
            case 'OrderReviewed':
                $type = 'App\Notifications\OrderReviewed';
                    break;
            case 'OrderCreated':
                $type = 'App\Notifications\OrderCreated';
                    break;
            case 'OrderCreatedAgent':
                $type = 'App\Notifications\OrderCreatedAgent';
                    break;
            case 'OrderCancelled':
                $type = 'App\Notifications\OrderCancelled';
                    break;
            case 'OrderAssigned':
                $type = 'App\Notifications\OrderAssigned';
                    break;
            case 'OrderCompleted':
                $type = 'App\Notifications\OrderCompleted';
                    break;
            case 'ComplainCreated':
                $type = 'App\Notifications\ComplainCreated';
                    break;
            case 'PasswordChanged':
                $type = 'App\Notifications\PasswordChanged';
                    break;
            default:
               $type =  '';

        }


        $sendNotification = $this->sendNotification($request->fcmToken, $type,$request->language);
        return json_encode($sendNotification);

    }

    public function sendTestNotification(Request $request){
        $requestdata = $request->only(['title','message','fcm_token']);
        $rules = [
            'title' => 'required',
            'message' => 'required',
            'fcm_token' => 'required',
        ];
        $validator = Validator::make($requestdata, $rules);
        if ($validator->fails()){
            return $this->newResponse(false,$this->validationHandle($validator->messages()));
        }
        $path_to_firebase_cm = 'https://fcm.googleapis.com/fcm/send';

        $api_server_key = env('FCM_SERVER_KEY','AAAAdmaZn8w:APA91bH1vKSimwo9W7_vjAaU7rywb1313uoJAjJTLU97UNZ4DZDAFKUI7CpxGf0wa4TEtHOERGkUHCA6DeRb3JRuGnYZw6O69KTXV0okJqXkqKbw0_CJPAxoCMkxX1MCLC7awIMZADt6');
        $notification = [
            'title' => $request->title,
            'body' =>$request->message,
            "data" => ['title'=>$request->title,'massage'=>$request->message],
            'icon' => 'myIcon',
            'sound' => 'mySound',
//                "click_action" => "com.webapp.a4_order_station_driver.feture.home.MainActivity",

        ];
        $extraNotificationData = ["message" => $notification, "moredata" => 'dd'];

        $fcmNotification = [
//            'registration_ids' => $this->tokens, //multple token array
            'to' => $request->fcm_token,
            'notification' => $notification,
            'data' => $extraNotificationData
        ];


        $fields = array(
//            'registration_ids' => $token,
            'priority' => 1,
            'notification' => array('title' => 'Naqi', 'body' => $request->message, 'sound' => 'Default'),
        );
        $headers = array(
            'Authorization:key=' . $api_server_key,
            'sender:id=508527484876',
            'Content-Type:application/json'
        );

        // Open connection
        $ch = curl_init();
        // Set the url, number of POST vars, POST data
        curl_setopt($ch, CURLOPT_URL, $path_to_firebase_cm);
        curl_setopt($ch, CURLOPT_POST, true);
        curl_setopt($ch, CURLOPT_HTTPHEADER, $headers);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);
        curl_setopt($ch, CURLOPT_IPRESOLVE, CURL_IPRESOLVE_V4);
        curl_setopt($ch, CURLOPT_POSTFIELDS, json_encode($fcmNotification));
        // Execute post
        $result = curl_exec($ch);
        // Close connection
        curl_close($ch);
        return $result;
    }


}
