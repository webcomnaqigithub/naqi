<?php

namespace App\Http\Controllers;

use App\Http\Resources\Customer\CustomerWebResource;
use App\Http\Resources\CustomerResource;
use App\Models\Customer;
use Carbon\Carbon;
use Illuminate\Http\Request;

class CustomerController extends Controller
{
    public function index(Request $request){
            $customers=new Customer();
            if($request->get('agent_id')){
                $customers=$customers->where('agent_id',$request->agent_id);
            }
            if($request->get('from_date') && $request->get('to_date')){
                $customers=$customers->where('created_at','>=',Carbon::parse($request->from_date)->startOfDay())
                ->where('created_at','<=',Carbon::parse($request->to_date)->endOfDay());
            }
        $total_customer=$customers->count();
        $customers=$customers->paginate(10);
        $customers=new CustomerWebResource($customers);
//        return $customers;
        return $this->newResponse(true,__('api.success_response'),'',[],[
            'total_customers'=>$total_customer,
            'customers'=>$customers,
        ]);
    }
}
