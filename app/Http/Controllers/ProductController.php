<?php

namespace App\Http\Controllers;
use App\Models\Product;
use App\Models\AgentProduct;
use Illuminate\Support\Facades\Validator;
use Illuminate\Http\Request;
use App\Models\Banner;
use App\Models\Agent;

class ProductController extends Controller
{
     //update status
     public function changeStatus(Request $request)
     {
        try {
            
         $data = $request->only(['ids','status']);
         $rules = [
             'ids' => 'required',
             'status' => 'required|numeric',
         ];
 
         $validator = Validator::make($data, $rules);
         if ($validator->fails()) {
             return $this->response(false,$this->validationHandle($validator->messages()));
         } else {
             $result = Product::whereIn('id', $request->ids)
             ->update(
                 ['status' => $request->status]);
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

    //list all
    public function list(Request $request)
    {
        try {
            $products = Product::all();
            foreach($products as $product){
                $product->picture = url('/').$product->picture;
            }
            return $this->response(true,'success',$products);
        } catch (Exception $e) {
            return $this->response(false,'system error');
        }

        
    }

    //search by location
    public function searchByLocation(Request $request)
    {
        
        try {
            $data = $request->only(['lng','lat','addressType','userId']);
            $rules = [
                'userId' => 'required',
                'lng' => 'required',
                'lat' => 'required',
                'addressType' => 'required|in:home,mosque,company',
            ];
            $validator = Validator::make($data, $rules);
            if ($validator->fails()) {
                return $this->response(false,$this->validationHandle($validator->messages()));
            } else {
                return $this->response(true,'success',$this->getProductsInMainScreen($request,$request->userId,$request->addressType));
            }
        } catch (Exception $e) {
            return $this->response(false,'system error');
        }
    }

    

    //list agent products
    public function listAgentProduct($userId)
    {
        
        try {
            return $this->response(true,'success',Product::all()->where('agentId',$userId));

        } catch (Exception $e) {
            return $this->response(false,'system error');
        }

    }
    //details
    public function details($id)
    {
        
        try {
            $record = Product::find($id);
            if($record == null){
                return $this->response(false,'id is not found');
            }
            return $this->response(true,'success',$record);
        } catch (Exception $e) {
            return $this->response(false,'system error');
        }

        
    }
    //delete
    public function delete($id)
    {      
       
        try {
            $record = Product::find($id);
            if($record == null){
                return $this->response(false,'id is not found');
            }
            if($record->delete())
            {
                return $this->response(true,'success');

            }else {
                return $this->response(false,'failed');
            }
        } catch (Exception $e) {
            return $this->response(false,'system error');
        }

        
    }
    //create
    public function create(Request $request)
    {
        
        try {
            $data = $request->only(['arabicName','englishName','picture','homePrice','mosquePrice','officialPrice']);
            $rules = [
                'arabicName' => 'required',
                'englishName' => 'required',
                'picture' => 'required|image|mimes:jpeg,png,jpg,gif,svg|max:2048',
                'homePrice' => 'required|numeric|min:0|not_in:0',
                'mosquePrice' => 'required|numeric|min:0|not_in:0',
                // 'otherPrice' => 'required|numeric',
                'officialPrice' => 'required|numeric|min:0|not_in:0',
            ];
            $validator = Validator::make($data, $rules);
            if ($validator->fails()) {
                return $this->response(false,$this->validationHandle($validator->messages()));
            } else {
                if ($request->file('picture') !== null) {
                    $imageName = time().'.'.$request->picture->extension();  
                    $request->picture->move(public_path('products'), $imageName);
                    $data['picture'] = '/products/'.$imageName;


            
                    $newRecord =  Product::create($data);
                    $newRecord->picture = url('/').$newRecord->picture;
                    return $this->response(true,'success',$newRecord);
                }
    
               
            }
            
        } catch (Exception $e) {
            return $this->response(false,'system error');
        }

        
    }
    // use URL of image not image file
    public function createWithImageUrl(Request $request)
    {
        
        try {
            $data = $request->only(['arabicName','englishName','picture','homePrice','mosquePrice','officialPrice']);
            $rules = [
                'arabicName' => 'required',
                'englishName' => 'required',
                'picture' => 'required',
                'homePrice' => 'required|numeric|min:0|not_in:0',
                'mosquePrice' => 'required|numeric|min:0|not_in:0',
                // 'otherPrice' => 'required|numeric',
                'officialPrice' => 'required|numeric|min:0|not_in:0',
            ];
            $validator = Validator::make($data, $rules);
            if ($validator->fails()) {
                return $this->response(false,$this->validationHandle($validator->messages()));
            } else {
                $newRecord =  Product::create($data);
                return $this->response(true,'success',$newRecord);
            }
        } catch (Exception $e) {
            return $this->response(false,'system error');
        }
    }

    //update
    public function update(Request $request)
    {
       
        try {
            $data = $request->only(['id','arabicName','englishName','picture','homePrice','mosquePrice','officialPrice']);
            $rules = [
                'id' => 'required|numeric',
                'arabicName' => 'required',
                'englishName' => 'required',
                'picture' => 'image|mimes:jpeg,png,jpg,gif,svg|max:2048',
                'homePrice' => 'required|numeric|min:0|not_in:0',
                'mosquePrice' => 'required|numeric|min:0|not_in:0',
                // 'otherPrice' => 'required|numeric',
                'officialPrice' => 'required|numeric|min:0|not_in:0',
            ];

            $validator = Validator::make($data, $rules);
            if ($validator->fails()) {
                return $this->response(false,$this->validationHandle($validator->messages()));
            } else {

                if ($request->file('picture') !== null) {
                    $result = Product::find($request->id);
                    if($result == null)
                    {
                        return $this->response(false,'not valid id');
                    }
                    $imageName = time().'.'.$request->picture->extension();  
                    $request->picture->move(public_path('products'), $imageName);
                    $result->update(
                        ['arabicName' => $request->arabicName,
                        'englishName' => $request->englishName,
                        'homePrice' => $request->homePrice,
                        'mosquePrice' => $request->mosquePrice,
                        'officialPrice' => $request->officialPrice,
                        'picture' => '/products/'.$imageName,
                        'otherPrice' => $request->otherPrice]);
                    return $this->response(true,'success');
                }
                else {
                    $result = Product::where('id', $request->id)
                    ->update(
                        ['arabicName' => $request->arabicName,
                        'englishName' => $request->englishName,
                        'homePrice' => $request->homePrice,
                        'mosquePrice' => $request->mosquePrice,
                        'officialPrice' => $request->officialPrice,
                        'otherPrice' => $request->otherPrice]);
                    if($result == 0) // no update
                    {
                        return $this->response(false,'not valid id');
                    }
                    return $this->response(true,'success');
                }
                
            }
        } catch (Exception $e) {
            return $this->response(false,'system error');
        }


        
    }
    //update with image URL
    public function updateWithImageUrl(Request $request)
    {
        
        try {
            $data = $request->only(['id','arabicName','englishName','picture','homePrice','mosquePrice','officialPrice','status']);
            $rules = [
                'id' => 'required|numeric',
                'arabicName' => 'required',
                'englishName' => 'required',
                'picture' => 'required',
                'homePrice' => 'required|numeric|min:0|not_in:0',
                'mosquePrice' => 'required|numeric|min:0|not_in:0',
                // 'otherPrice' => 'required|numeric',
                'officialPrice' => 'required|numeric',
                'status' => 'required|numeric',
            ];

            $validator = Validator::make($data, $rules);
            if ($validator->fails()) {
                return $this->response(false,$this->validationHandle($validator->messages()));
            } else {

                $result = Product::where('id', $request->id)
                    ->update(
                        ['arabicName' => $request->arabicName,
                        'englishName' => $request->englishName,
                        'homePrice' => $request->homePrice,
                        'picture' => $request->picture,
                        'mosquePrice' => $request->mosquePrice,
                        'officialPrice' => $request->officialPrice,
                        'status' => $request->status,
                        'otherPrice' => $request->otherPrice]);
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
    //search
    public function search(Request $request,Product $search)
    {
        
        try {
            $search = $search->newQuery();
            if ($request->has('arabicName')) {
                $search->where('arabicName','LIKE', '%'.$request->input('arabicName').'%');
            }
            if ($request->has('englishName')) {
                $search->where('englishName','LIKE', '%'.$request->input('englishName').'%');
            }
        return $this->response(true,'success',$search->paginate($this->pageSize));
        } catch (Exception $e) {
            return $this->response(false,'system error');
        }


        
    }
}
