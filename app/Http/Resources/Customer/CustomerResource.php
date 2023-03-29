<?php

namespace App\Http\Resources\Customer;

use App\Http\Resources\Address\AddressResource;
use Illuminate\Http\Resources\Json\JsonResource;

class CustomerResource extends JsonResource
{
    /**
     * Transform the resource into an array.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return array
     */
    protected $withDefaultAddress;
    public function __construct($resource,$withDefaultAddress=true)
    {
        $this->withDefaultAddress=$withDefaultAddress;
        parent::__construct($resource);
    }
    public function toArray($request)
    {
        $data= [
            "id"=>$this->id,
            "avatar_url"=>$this->avatar_url,
            "name"=>$this->name,
            "mobile"=>$this->mobile,
            "token"=>$this->access_token,
            "points"=>$this->finalPoints,
            "language"=>$this->language,
            "otp_sms"=>$this->otp_sms,
            "is_verified"=>$this->is_verified,
            "fcm_token"=>$this->fcmToken,
            "has_default_address"=>$this->has_default_address,

//            "default_address"=>@AddressResource::make($this->defaultAddress),
        ];

        if($this->withDefaultAddress){
            $data['default_address']=@AddressResource::make($this->defaultAddress);
        }
        return $data;
    }
}
