<?php

namespace App\Http\Controllers;

use App\Models\Industry;
use App\Models\Permission;
use App\Models\Setting;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;

class PermissionController extends Controller
{

    public function create(Request $request){
        $data = $request->only(['name','display_name','description']);
        $rules = [
             'name' => 'required',
             'display_name' => 'required',
        ];

        $validator = Validator::make($data, $rules);
        if ($validator->fails()) {
            return $this->response(false,$this->validationHandle($validator->messages()));
        }
        try {
            $permission =new Permission();
            $permission->name=$request->name;
            $permission->display_name=$request->display_name;
            $permission->description=@$request->description;
            $permission->save();

            return $this->response(true,__('api.success_response'));
        }catch (\Exception $exception){
            return $this->response(false,$exception->getMessage());
        }
    }
    public function getPermissionsList(Request $request){
        $permissions=Permission::select(['name','display_name'])->get();
        try {

            return $this->response(true,__('api.success_response'),$permissions);
        }catch (\Exception $exception){
            return $this->response(false,$exception->getMessage());
        }
    }

    public function assign(Request $request){
        $data = $request->only(['admin_id','permissions']);
        $rules = [

            'admin_id' => 'required|exists:industry,id',
            'permissions' => 'required',
        ];

        $validator = Validator::make($data, $rules);
        if ($validator->fails()) {
            return $this->response(false,$this->validationHandle($validator->messages()));
        }

        try {
               $admin= Industry::find($request->admin_id);
               if($admin){

               $admin->syncPermissions($request->permissions);
                   return $this->response(true,__('api.success_response'),$admin->permissions()->select('name','display_name')->get());
               }else{
                   return $this->response(false,__('api.fails_response'));
               }

        }catch (\Exception $exception){
            return $this->response(false,$exception->getMessage());
        }
    }
    public function getAdminPermissions(Request $request){
        $data = $request->only(['admin_id']);
        $rules = [
            'admin_id' => 'required|exists:industry,id',

        ];

        $validator = Validator::make($data, $rules);
        if ($validator->fails()) {
            return $this->response(false,$this->validationHandle($validator->messages()));
        }
        try {
            $admin= Industry::find($request->admin_id);
            if($admin){

                return $this->response(true,__('api.success_response'),$admin->permissions()->select('name','display_name')->get());
            }else{
                return $this->response(false,__('api.fails_response'));
            }

        }catch (\Exception $exception){
            return $this->response(false,$exception->getMessage());
        }
    }

    //delete
    public function delete($id)
    {

        try {
            $record = Permission::destroy($id);

            if($record)
            {
                $record->delete();
                return $this->response(true,'success');

            }else {
                return $this->response(false,'failed');
            }
        } catch (Exception $e) {
            return $this->response(false,'system error');
        }

    }
    public function destroy()
    {

        try {
            $record = Permission::query()->delete();

            if($record)
            {

                return $this->response(true,'success');

            }else {
                return $this->response(false,'failed');
            }
        } catch (Exception $e) {
            return $this->response(false,'system error');
        }

    }

}
