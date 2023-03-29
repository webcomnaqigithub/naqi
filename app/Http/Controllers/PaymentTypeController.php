<?php

namespace App\Http\Controllers;

use App\models\Complain;
use App\Models\PaymentType;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;

class PaymentTypeController extends Controller
{
    public function list(Request $request)
    {
        $paymentTypes=PaymentType::all();
        return $this->newResponse(true,__('api.success_response'),'payment_types',$paymentTypes);

    }
    public function changeStatus(Request $request)
    {
        try {
            $data = $request->only(['ids','status']);
            $rules = [
                'ids' => 'required',
                'status' => 'required',
            ];

            $validator = Validator::make($data, $rules);
            if ($validator->fails()) {
                return $this->response(false,$this->validationHandle($validator->messages()));
            } else {
                $result = PaymentType::whereIn('id', $request->ids)
                    ->update(
                        ['is_active' => $request->status]);
                if($result == 0) // no update
                {
                    return $this->response(false,'not valid id');
                }
                return $this->response(true,'success');

            }
        } catch (Exception $e) {
            return $this->response(false,'system error');
        }

    }
}
