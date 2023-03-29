<?php

namespace App\Http\Middleware;

use Illuminate\Auth\Middleware\Authenticate as Middleware;

use Illuminate\Http\Exceptions\HttpResponseException;
class Authenticate extends Middleware
{
    /**
     * Get the path the user should be redirected to when they are not authenticated.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return string|null
     */
    protected function redirectTo($request)
    {

        if($request->is('api/*'))
        {
            $array=['status'=>false,'message'=>__('api.unauthorized_request')];
            throw new HttpResponseException(response()->json($array,401));

//          return  throw new HttpResponseException(response()->error(['failure_reason'=>'Fresh Access Token Required'], 'Unauthorized Request', 401));
        }

        if (!$request->expectsJson()) {
            session()->flash('message', 'Exceeded an inactivity period of over 15 mins. Kindly re-login to continue');
            return route('login');
        }



//        if (!$request->expectsJson()) {
////            dd($request->expectsJson());
//            return route('login');
//        } else{
//            $response['status'] = false;
//            $response['message'] = 'Unauthenticated.';
//
//            return response($response, 401)->header('Content-Type', 'application/json');
//        }
//        $response['status'] = false;
//        $response['message'] = 'Unauthenticated.';
//
//        return response($response, 401)->header('Content-Type', 'application/json');
    }
}
