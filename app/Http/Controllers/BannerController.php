<?php

namespace App\Http\Controllers;
use App\Models\Banner;
use Illuminate\Support\Facades\Validator;
use Illuminate\Http\Request;

class BannerController extends Controller
{
    //list all
    public function list(Request $request)
    {


        try {
            $banners = Banner::all();
            foreach($banners as $banner){
                $banner->picture = url('/').$banner->picture;
            }
            return $this->response(true,'success',$banners);
        } catch (Exception $e) {
            return $this->response(false,'system error');
        }

    }
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
                $result = Banner::whereIn('id', $request->ids)
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
    //details
    public function details($id)
    {

        try {
            $record = Banner::find($id);
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
            $record = Banner::find($id);
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
            $data = $request->only(['name','picture']);
            $rules = [
                'name' => 'required',
                'picture' => 'required|file|image',
            ];
            $validator = Validator::make($data, $rules);
            if ($validator->fails()) {
                return $this->response(false,$this->validationHandle($validator->messages()));
            } else {
                 if ($request->file('picture')) {
                     $imageName = time().'.'.$request->picture->extension();
                     $path=public_path('banners');
                     if(!File::exists($path)) {
                         File::makeDirectory($path, 0777, true, true);
                         // path does not exist
                     }
                     $request->picture->move($path, $imageName);
                     $data['picture'] = '/banners/'.$imageName;

                 }
                $newRecord =  Banner::create($data);
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
            $data = $request->only(['id','name','picture']);
            $rules = [
                'id' => 'required|numeric',
                'name' => 'required',
                'picture' => 'required',
            ];

            $validator = Validator::make($data, $rules);
            if ($validator->fails()) {
                return $this->response(false,$this->validationHandle($validator->messages()));
            } else {

                $result = Banner::where('id', $request->id)
                    ->update(
                        ['name' => $request->name,
                        'picture' => $request->picture,
                        'status' => $request->status]);
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
