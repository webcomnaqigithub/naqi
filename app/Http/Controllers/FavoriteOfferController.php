<?php

namespace App\Http\Controllers;
use App\Models\FavoriteOffer;
use App\Models\Offer;
use App\Models\AgentOffer;
use App\Models\Agent;
use App\Models\User;
use Illuminate\Support\Facades\Validator;
use Illuminate\Http\Request;

class FavoriteOfferController extends Controller
{
    //list all
    public function list(Request $request)
    {
        

        try {
            return $this->response(true,'success',FavoriteOffer::all());
            
        } catch (Exception $e) {
            return $this->response(false,'system error');
        }
        
    }
    //list user favorite products
    public function listUserFavoriteOffer($userId)
    {
        
        try {

            $user= User::find($userId);
            if($user == null){
                return $this->response(false,'invalid user');
            }

            // get agent of current location of user
            $agent = new Agent;
            $agent = $agent->search($user->lat,$user->lng);
            $agentOffers = null;
            if($agent != null){
                $agentOffers=AgentOffer::where('agentId',$agent->id)->get();
            }
           

            $products =  FavoriteOffer::select('favoriteOffers.id','favoriteOffers.productId','favoriteOffers.userId','products.arabicName','products.englishName','products.picture',
            'products.mosquePrice','products.otherPrice','products.homePrice','products.officialPrice')
            ->leftJoin('products', 'products.id', '=', 'favoriteOffers.productId')
            ->where('userId',$userId)->get();
            foreach($products as $product){
                $product->picture = url('').$product->picture;
                if( $agentOffers != null){
                    $temp  = $agentOffers->where('productId', $product->productId)->first();
                    if( $temp != null){
                        $product->otherPrice = $temp->otherPrice;
                        $product->mosquePrice = $temp->mosquePrice;
                        $product->officialPrice = $temp->officialPrice;
                        $product->homePrice = $temp->homePrice;
                    } 
                }
                
            }
            return $this->response(true,'success',$products);

        } catch (Exception $e) {
            return $this->response(false,'system error');
        }
        
    }
    //details
    public function details($id)
    {
        
        try {
            $record = FavoriteOffer::find($id);
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
            
            $record = FavoriteOffer::find($id);
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

    public function removeFromFavoriteList(Request $request)
    {   
        try {
            $data = $request->only(['userId','productId']);
            $rules = [
                'userId' => 'required|numeric',
                'productId' => 'required|numeric',
            ];
            $validator = Validator::make($data, $rules);
            if ($validator->fails()) {
                return $this->response(false,$this->validationHandle($validator->messages()));
            } else {
                $records = FavoriteOffer::where('productId', $request->productId)->where('userId', $request->userId);
                if($records->delete())
                {
                    return $this->response(true,'success');

                }else {
                    return $this->response(false,'failed');
                }
            }
            
        } catch (Exception $e) {
            return $this->response(false,'system error');
        }
    }

    //delete
    public function clear($userId)
    {    
        
        
        try {
            $record = FavoriteOffer::where('userId',$userId)->delete();
            if($record > 0)
            {
                return $this->response(true,'success');
            }else {
                return $this->response(true,'no record to delete');
            }
        } catch (Exception $e) {
            return $this->response(false,'system error');
        }
        
    }

    //delete multiple
    public function deleteMultiple(Request $request)
    {    
        
        
        try {
            $data = $request->only(['ids']);
            $rules = [
                'ids' => 'required|numeric',
            ];
            $validator = Validator::make($data, $rules);
            if ($validator->fails()) {
                $record = FavoriteOffer::whereIn('id', $request->ids)->delete();
                if($record == 0){
                    return $this->response(true,'no record to remvoe');
                } else {
                    return $this->response(true,'success');
                }
            }
        } catch (Exception $e) {
            return $this->response(false,'system error');
        }
        
        
    }
    //create
    public function create(Request $request)
    {
        
        try {
            $data = $request->only(['userId','productId']);
            $rules = [
                'userId' => 'required|numeric',
                'productId' => 'required|numeric',
            ];
            $validator = Validator::make($data, $rules);
            if ($validator->fails()) {
                return $this->response(false,$this->validationHandle($validator->messages()));
            } else {
                $oldRecord = FavoriteOffer::where('userId',$data['userId'])->where('productId',$data['productId'])->first();
                if($oldRecord == null)
                {
                    $product = Offer::find($request->productId);
                    if($product  == null )
                    {
                        return $this->response(false,'not valid product id');
                    }
                    $newRecord =  FavoriteOffer::create($data);
                    return $this->response(true,'success',$newRecord);
                }
                return $this->response(true,'success',$oldRecord);
            }
        } catch (Exception $e) {
            return $this->response(false,'system error');
        }
        
    }

    //update
    public function update(Request $request)
    {
        
        try {
            $data = $request->only(['id','userId','productId']);
            $rules = [
                'id' => 'required|numeric',
                'userId' => 'required|numeric',
                'productId' => 'required|numeric',
            ];

            $validator = Validator::make($data, $rules);
            if ($validator->fails()) {
                return $this->response(false,$this->validationHandle($validator->messages()));
            } else {
                $result = Address::where('id', $request->id)
                ->update(
                    ['userId' => $request->userId,
                    'productId' => $request->productId
                ]);
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
