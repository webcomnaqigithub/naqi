<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;

class FileController extends Controller
{
    //
    public function uploadBanner(Request $request)
    {

        try {
            $data = $request->only(['picture']);
            $rules = [
                'picture' => 'required|image|mimes:jpeg,png,jpg,gif,svg|max:2048',
            ];
            $validator = Validator::make($data, $rules);
            if ($validator->fails()) {
                return $this->response(false,$this->validationHandle($validator->messages()));
            } else {
                if ($request->file('picture') !== null) {
                    $imageName = time().'.'.$request->picture->extension();  
                    $request->picture->move(public_path('banners'), $imageName);
                    return $this->response(true,'success','/banners/'.$imageName);
                }
            }
        } catch (Exception $e) {
            return $this->response(false,'system error');
        }
        
    }

    public function uploadProductImage(Request $request)
    {
        try {
            $data = $request->only(['picture']);
            $rules = [
                'picture' => 'required|image|mimes:jpeg,png,jpg,gif,svg|max:2048',
            ];
            $validator = Validator::make($data, $rules);
            if ($validator->fails()) {
                return $this->response(false,$this->validationHandle($validator->messages()));
            } else {
                if ($request->file('picture') !== null) {
                    $imageName = time().'.'.$request->picture->extension();  
                    $request->picture->move(public_path('products'), $imageName);
                    return $this->response(true,'success','/products/'.$imageName);
                }
            }   
        } catch (Exception $e) {
            return $this->response(false,'system error');
        }
            
    }
    
    public function uploadOfferImage(Request $request)
    {
        try {
            $data = $request->only(['picture']);
            $rules = [
                'picture' => 'required|image|mimes:jpeg,png,jpg,gif,svg|max:2048',
            ];
            $validator = Validator::make($data, $rules);
            if ($validator->fails()) {
                return $this->response(false,$this->validationHandle($validator->messages()));
            } else {
                if ($request->file('picture') !== null) {
                    $imageName = time().'.'.$request->picture->extension();  
                    $request->picture->move(public_path('products'), $imageName);
                    return $this->response(true,'success','/products/'.$imageName);
                }
            }   
        } catch (Exception $e) {
            return $this->response(false,'system error');
        }
            
    }
    
}
