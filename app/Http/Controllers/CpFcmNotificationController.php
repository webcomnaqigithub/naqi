<?php

namespace App\Http\Controllers;

use App\Models\Agent;
use App\Models\CpFcmNotification;
use App\Models\Notification;
use App\Models\User;
use App\Notifications\AgentUserMessage;
use App\Notifications\FireBaseNotify;
use App\Traits\FireBaseNotify as TraitsFireBaseNotify;
use Exception;
use Illuminate\Support\Facades\Validator;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Notification as FacadesNotification;

class CpFcmNotificationController extends Controller
{
    use TraitsFireBaseNotify;

    public function adminNotificationlist(Request $request)
    {
        try {
            $notifications = CpFcmNotification::with('industry')->where('sender_type', 'admin')->get();
            return $this->response(true,'success',$notifications);
        } catch (Exception $e) {
            return $this->response(false,$e->getMessage());
        }
    }
    
    public function agentNotificationlist(Request $request)
    {
        try {
            $notifications = CpFcmNotification::with('agent', 'industry')->where('sender_type', 'agent')->get();
            return $this->response(true,'success',$notifications);
        } catch (Exception $e) {
            return $this->response(false,'system error');
        }
    }

    public function AdminNotifyAgent(Request $request)
    {
        try{
            $requestdata = $request->only(['title', 'message', 'agent_id', 'admin_id']);
            $rules = [
                'title' => 'required',
                'message' => 'required',
                'agent_id.*' => 'required|numeric',
                'admin_id' => 'required|numeric',
            ];
            $validator = Validator::make($requestdata, $rules);
            if ($validator->fails()){
                return $this->newResponse(false,$this->validationHandle($validator->messages()));
            }

            $notify_data['users_token'] = Agent::whereIn('id', $request->agent_id)->pluck('fcmToken');
            $notify_data['title'] = $request->title;
            $notify_data['message'] = $request->message;
            $agents = Agent::whereIn('id', $request->agent_id)->pluck('name');
            $result = $this->sendFirebasNotification($notify_data);
            DB::beginTransaction();
                CpFcmNotification::create([  
                    'sender_type' => "admin",  
                    'industry_id' => $request->admin_id,
                    'sender_ids' => "['admin']",
                    'title' => $request->title,
                    'body' => $request->message,
                    'receiver_ids' => json_encode($agents,JSON_UNESCAPED_UNICODE),
                    'users_count' => $agents->count(),
                ]); 

            DB::commit();
            return $this->response(true,'success', $result);
        } catch (\Throwable $th) {
            DB::rollback();
            return $this->response(false,$th->getMessage());
        }
    }

    public function AgentNotifyUsers(Request $request)
    {
        try {
            $requestdata = $request->only(['title', 'message', 'agent_id', 'admin_id']);
            $rules = [
                'title' => 'required',
                'message' => 'required',
                'agent_id.*' => 'required|numeric',
                'admin_id' => 'required|numeric'
            ];
            $validator = Validator::make($requestdata, $rules);
            if ($validator->fails()){
                return $this->newResponse(false,$this->validationHandle($validator->messages()));
            }
    
            $users = User::whereIn('agent_id', $request->agent_id);
            $notify_data['users_token'] = $users->pluck('fcmToken');
            $notify_data['title'] = $request->title;
            $notify_data['message'] = $request->message;
            $agents = Agent::whereIn('id', $request->agent_id)->pluck('name');

            $result = $this->sendFirebasNotification($notify_data);

            DB::beginTransaction();
                CpFcmNotification::create([
                    'sender_type' => 'agent',
                    'industry_id' => $request->admin_id,
                    'sender_ids' => json_encode($agents,JSON_UNESCAPED_UNICODE),
                    'title' => $request->title,
                    'body' => $request->message,
                    'receiver_ids' => 'all users of this agent',
                    'users_count' => $users->count(),
                ]);
            DB::commit();
            return $this->response(true,'success', $result);
        } catch (\Throwable $th) {
            DB::rollback();
            return $this->response(false,$th->getMessage());
        }
    }



}
