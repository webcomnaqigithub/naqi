<?php

namespace App\Http\Controllers;
use App\Models\ReferralProgram;
use App\Models\User;
use Illuminate\Support\Facades\Validator;
use Illuminate\Http\Request;

class ReferralProgramController extends Controller
{

    //list all 
    public function list(Request $request)
    {
        try {
            $records= ReferralProgram::leftJoin('users', 'users.id', '=', 'referralProgram.fromUser')
            ->leftJoin('users as users2', 'users2.id', '=', 'referralProgram.toUser')
            ->select('referralProgram.*',"users2.name as fromName","users2.mobile as fromMobile","users.name","users.mobile")->get();
            return $this->response(true,'success',$records);
        } catch (Exception $e) {
            return $this->response(false,'system error');
        }
    }

}
