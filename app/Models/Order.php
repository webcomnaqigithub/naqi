<?php

namespace App\Models;
use Carbon\Carbon;
use Illuminate\Database\Eloquent\SoftDeletes;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Notifications\Notifiable;

class Order extends Model
{
    use SoftDeletes,Notifiable;
    protected $table = 'orders';
//    protected $guarded = [];
    protected $fillable = [
        'userId',
        'addressId',
        'agentId',
        'delegatorId',
        'assignDate',
        'deliveryDate',
        'addressType',
        'rejectionReason',
        'rejectionDate',
        'cancelDate',
        'amount',
        'coupon',
        'points',
        'paymentReference',
        'reviewText',
        'productsReview',
        'productsReview',
        'delegatorReview',
        'serviceReview',
        'status',
        'completionDate',
        'pointsDiscount',
        'couponDiscount',
        'city_id',
        'district_id',
        'region_id',
        'deliveryTime',
        'preorder',
        'deliveryLocation',
        'deliveryTimePeriod',
        'coupon_id',
        'time_slot_id',
        'schedule_slot_id',
        'flat_location_id',
        'payment_type_id',
        'parent_order_id',
        'type',
        'sub_total',
        'total_discount',
        'sub_total_2',
        'tax_ratio',
        'tax',
        'delivery_cost',
        'use_points',
        'points',
        'delivery_schedule_date',
        'is_paid',
        'payment_transaction_id',
        'offer_id',
        'creatable_type'
        ];

/**
     * The attributes that should be hidden for arrays.
     *
     * @var array
     */
    protected $hidden = [
         'deleted_at',
    ];

    protected $casts = [
        'deliveryDate' => 'date|Y-m-d',
    ];
    protected $appends=['status_label','client_delivery_date'];
    public function products()
    {

        return $this->hasMany('App\Models\OrderProduct','orderId')
        ->select('orderProducts.id','orderProducts.productId','orderProducts.orderId',
        'products.arabicName','products.englishName','products.picture',
        'products.mosquePrice','products.otherPrice','products.homePrice','products.officialPrice','orderProducts.amount','products.type','orderProducts.price','orderProducts.total')
        ->leftJoin('products', 'products.id', '=', 'orderProducts.productId');
    }
    public function coupon(){
        return $this->belongsTo(Coupon::class,'coupon_id');
    }
    
    public function coupona(){
        return $this->belongsTo(Coupon::class,'coupon_id');
    }

    public function customer(){
        return $this->belongsTo(Customer::class,'userId');
    }
    public function address(){
        return $this->belongsTo(Address::class,'addressId')->withTrashed();
    }

    public function city()
    {
        return $this->belongsTo(City::class,'city_id');
    }
   public function agent()
    {
        return $this->belongsTo(Agent::class,'agentId');
    }

    public function region()
    {
        return $this->belongsTo(Region::class,'region_id');
    }


    public function district()
    {
        return $this->belongsTo(\App\Models\District::class,'district_id');
    }
    public function orderproducts(){
        return $this->hasMany(OrderProduct::class,'orderId');
    }
    public function totalqty(){
        return $this->orderproducts()->sum('amount');
    }

    public function paymentType(){
        return $this->belongsTo(PaymentType::class,'payment_type_id');
    }
    public function delegator(){
        return $this->belongsTo(Delegator::class,'delegatorId');
    }
    public function timeSlot(){
        return $this->belongsTo(TimeSlot::class,'time_slot_id')->withTrashed();
    }
    public function scheduleSlot(){
        return $this->belongsTo(OrderScheduleSlot::class,'schedule_slot_id');
    }
    public function flatLocation(){
        return $this->belongsTo(DeliveryFlatLocation::class,'flat_location_id')->withTrashed();
    }
    public function offer(){
        return $this->belongsTo(Offer::class,'offer_id')->withTrashed();
    }
    public function getStatusLabelAttribute(){
        return __('api.order_status.'.$this->status);

//        return [
//            'created'=>__('api.order_status.created'),
//            'cancelledByClient'=>__('api.order_status.cancelledByClient'),
//            'cancelledByApp'=>__('api.order_status.cancelledByApp'),
//            'in_the_way'=>__('api.order_status.in_the_way'),
//            'completed'=>__('api.order_status.completed'),
//        ][$this->status];
    }
    public function creatable(){
        return $this->morphTo();
    }
    public function createdBy(){
        if($this->creatable() instanceof Customer){
            return 'client';
        }
        if($this->creatable() instanceof Agent){
            return 'Agent';
        }
        if($this->creatable() instanceof Industry){
            return 'Naqi';
        }

        return null;
    }

    public function getClientDeliveryDateAttribute(){

        if($this->delivery_date =='immediately'){
            return Carbon::parse($this->assignDate)->format("Y-m-d");
        }

        if($this->delivery_date =='schedule'){
            return Carbon::parse($this->delivery_schedule_date)->format("Y-m-d");
        }
        return null;

    }

}
