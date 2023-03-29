<?php

namespace App\Http\Controllers\Api\Customer;

use App\Http\Controllers\Controller;
use App\Models\Points;
use App\Models\ReferralProgram;
use App\Models\Region;
use App\Models\Setting;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Validator;

class HomeController extends Controller
{

    public function getRegions(){
        Region::all();
        return $this->newResponse(true,__("api.success_response"),'regions',$regions);
    }
    public function regions(){


            return     \DB::table('regions')->get();
        return $this->newResponse(true,__("api.success_response"),'regions',$regions);
    }
    public function regionsLite(){
        return \DB::table('regions_lite')->get();
        return $this->newResponse(true,__("api.success_response"),'regions',$regions);
    }

    public function getUserPoints(Request $request)
    {
        $user =auth()->user();
        try {

                 $userPoints=$user->points()->orderBy('created_at','desc')->get();
                  $bonusPoints=$user->points()->where('type','bonus')->sum('points');
                  $discountPoints=$user->points()->where('type','discount')->sum('points');
                  $finalPoints= ($bonusPoints-$discountPoints);
//                $user->subscriptionCount =  ReferralProgram::where('fromUser',$user->id)->count();
                    return $this->newResponse(true,__('api.success_response'),'customer_points',$finalPoints,[
                        'point_history'=>$userPoints
                    ]);

        } catch (\Exception $e) {
            return $this->newResponse(false,__('api.fails_response'));
        }

    }
    //list client Notificaiton
    public function listClientNotifications(Request $request)
    {

        try {
            $user = $request->user();
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
            return $this->newResponse(true,__('api.success_response'),'notifications',$list);
        } catch (\Exception $e) {
            return $this->newResponse(false,__('api.fails_response'));
        }

    }

}
