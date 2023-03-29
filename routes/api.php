<?php

use Illuminate\Http\Request;
use App\Models\Address;

/*
|--------------------------------------------------------------------------
| API Routes
|--------------------------------------------------------------------------
|
| Here is where you can register API routes for your application. These
| routes are loaded by the RouteServiceProvider within a group which
| is assigned the "api" middleware group. Enjoy building your API!
|
*/


// Changes
//Route::get('regions', 'HomeController@regions');
//Route::get('regions', 'AreaLookupController@listRegions');
//Route::get('region_cities/{regionId}', 'AreaLookupController@listRegionCities');
//Route::get('region_city_districts/{regionId}/{cityId?}', 'AreaLookupController@listRegionCityDistricts');


// agent apis
Route::group(['agent' => 'agent', 'middleware' => ['assign.guard:agent', 'jwt.auth']], function ()
{
    Route::get('agent', 'AgentController@getAgent');
    Route::post('/agent/password/reset', 'AgentController@resetPasswordFromPortal');

    Route::get('notifications/agent', 'NotificationController@listAgentNotifications');

    Route::post('agent/profile', 'AgentController@updateProfile');
    Route::post('otp/agent', 'AgentController@requestOtpToResetPassword');

    Route::post('agent/portal/orders', 'OrderController@listOrdersOfAgentFromPortal');


});
// agent orders

Route::post('agent/order/postpone', 'OrderController@agentPostponeOrderDate');
Route::post('postpone/orders','OrderController@postponeOrders');
Route::post('agent/order/cancel', 'OrderController@cancelOrderByApp');
Route::post('agent/order/cancel/update_reason', 'OrderController@UpdateCancelOrderByApp');
Route::post('agent/order/most-order-users', 'OrderController@MostOrderUsers');
Route::post('agent/orders', 'OrderController@listOrdersOfAgent');
Route::post('agent/orders/list', 'OrderController@newListOrdersOfAgent');
// new agent
Route::post('/agent/delegator/report','OrderController@agentDelegatorReport');
Route::post('/agent/delegator/assigned-orders','OrderController@agentDelegatorAssignedReport');
Route::post('/agent/delegator-list','AgentController@delegators');

Route::post('agent/status/orders', 'OrderController@listOrdersOfAgentInAgentApp');

// Route::get('/agent', 'AgentController@getAgent')->middleware('jwt.verify');
Route::post('agent/status', 'AgentController@changeStatus');
Route::get('agents', 'AgentController@list');
Route::post('agent/search', 'AgentController@search');
Route::post('agent/search/test', 'AgentController@testSearch');
Route::post('agent', 'AgentController@create');
Route::post('agent/login', 'AgentController@login');
Route::post('agent/areas/delete', 'AgentController@deleteAgentArea');
Route::post('agent/areas/update', 'AgentController@updateAgentArea');
Route::post('agent/areas/create', 'AgentController@createNewAgentArea');
Route::get('agent/{id}', 'AgentController@details');

Route::post('agent/update', 'AgentController@update');
Route::get('agent/product/{agentId}', 'AgentProductController@listAgentProducts');
//Route::get('agent/offer/{agentId}', 'AgentOfferController@listAgentOffers');
Route::delete('agent/{id}', 'AgentController@delete');
Route::post('agent/changePassword', 'AgentController@changePassword');
Route::post('agent/resetPassword', 'AgentController@checkAgentToResetPassword');

// delegator apis
Route::group(['delegator' => 'delegator', 'middleware' => ['assign.guard:delegator', 'jwt.auth']], function ()
{
    Route::post('otp/delegator', 'DelegatorController@requestOtpToResetPassword');
    Route::get('delegator', 'DelegatorController@getDelegator');
    Route::post('delegator/orders', 'OrderController@listOrderOfDelegator');
    Route::post('delegator/orders/list', 'OrderController@listNewOrderOfDelegator');
    Route::get('delegator/postpone-reasons','DelegatorController@postponeReasonsList');
    Route::post('delegator/postpone-order','DelegatorController@postponeOrder');
    Route::post('delegator/order/on-the-way', 'OrderController@makeOrderOnTheWay');
    Route::get('notifications/delegator', 'NotificationController@listDelegatorNotifications');
    Route::post('order/delegator/review', 'OrderController@reviewByDelegator');
    Route::post('order/reject', 'OrderController@cancelOrderByApp');
    Route::post('order/complete', 'OrderController@completeOrder');
    Route::post('order/new-complete', 'OrderController@newCompleteOrder');
    Route::post('delegator/profile', 'DelegatorController@updateProfile');

});

Route::post('delegator/changePassword', 'DelegatorController@changePassword');
Route::post('delegator/resetPassword', 'DelegatorController@checkDelegatorToResetPassword');

Route::post('delegator/status', 'DelegatorController@changeStatus');
Route::post('delegator', 'DelegatorController@create');
Route::get('delegators', 'DelegatorController@list');
Route::post('delegator/update', 'DelegatorController@update');
Route::get('delegator/{id}', 'DelegatorController@details');
Route::delete('delegator/{id}', 'DelegatorController@delete');
Route::post('agent/delegators', 'DelegatorController@listPerAgent');

Route::post('delegator/login', 'DelegatorController@login');

// industry apis control panel
Route::group(['prefix' => 'industry', 'middleware' => ['assign.guard:industry', 'jwt.auth']], function ()
{
    Route::post('/password/reset', 'IndustryController@resetPasswordFromPortal');
    Route::get('/industry', 'IndustryController@getIndustry');
    Route::get('notifications/industry', 'NotificationController@listIndustryNotifications');
    Route::post('otp/industry', 'IndustryController@requestOtpToResetPassword');
    Route::post('industry/profile', 'IndustryController@updateProfile');
    Route::post('industry/agent/report', 'OrderController@getAgentReport');
    Route::get('industry/order/list', 'OrderController@listOrderOfIndustry');
    Route::post('industry/order/list', 'OrderController@listOrderOfIndustryPerType');
    Route::post('industry/agent/orders', 'OrderController@listOrdersOfAgent');
    Route::post('notification/topic', 'UserController@sendNotificationsToTopic');
//    Route::delete('/delete/{id}', 'IndustryController@delete');

});

Route::post('order/assign', 'OrderController@assignOrder');

Route::post('industry/changePassword', 'IndustryController@changePassword');
Route::post('industry/resetPassword', 'IndustryController@checkIndustryToResetPassword');

Route::post('industry/status', 'IndustryController@changeStatus');
Route::post('industry', 'IndustryController@create');
Route::post('industry/login', 'IndustryController@login');
Route::post('industry/website/login', 'IndustryController@loginFromPortal');

Route::get('industries', 'IndustryController@list');
Route::post('industry/update', 'IndustryController@update');
Route::get('industry/{id}', 'IndustryController@details');
Route::delete('/industry/delete/{id}', 'IndustryController@delete');
Route::post('industry/search-mobile', 'IndustryController@getUserByMobile');

// address apis
Route::get('address', 'AddressController@list');
Route::get('client-address', 'AddressController@getAddress');
Route::post('address/default', 'AddressController@updateDefaultAddress');
Route::get('address/user/{userId}', 'AddressController@listUserAddress');
Route::get('address/{id}', 'AddressController@details');
Route::post('address', 'AddressController@create');
Route::post('address/update', 'AddressController@update');
Route::delete('address/{id}', 'AddressController@delete');
Route::post('address/status', 'AddressController@changeStatus');

// product apis
Route::get('product', 'ProductController@list');
Route::post('product/status', 'ProductController@changeStatus');

Route::get('product/{id}', 'ProductController@details');
Route::post('product', 'ProductController@create');
Route::post('product/create', 'ProductController@createWithImageUrl');
Route::post('product/update', 'ProductController@update');
Route::post('product/update2', 'ProductController@updateWithImageUrl');
Route::delete('product/{id}', 'ProductController@delete');
Route::post('product/search', 'ProductController@search');
Route::post('products', 'ProductController@searchByLocation');



// offer apis

Route::group(['prefix' => 'offers'],function(){
    Route::get('/', 'OfferController@list');
    Route::get('/details/{id}', 'OfferController@details');
    Route::post('/status', 'OfferController@changeStatus');
    Route::post('/', 'OfferController@create');
    Route::post('/create', 'OfferController@createWithImageUrl');
    Route::post('/update', 'OfferController@update');
    Route::post('/update2', 'OfferController@updateWithImageUrl');
    Route::delete('/{id}', 'OfferController@delete');

    Route::post('/search', 'OfferController@search');
});

//Route::post('offers', 'OfferController@searchByLocation');

// agent products add many products
Route::post('agent/products', 'AgentProductController@create');
Route::post('agent/products/update', 'AgentProductController@update');
Route::post('agent/products/updateValueById', 'AgentProductController@updateValueById');
Route::post('agent/products/update/{id}', 'AgentProductController@update2');
Route::post('agent/products/copy', 'AgentProductController@copy');

Route::any('agent/products/search', 'AgentProductController@search');
Route::delete('agent/products/{id}', 'AgentProductController@delete');
Route::post('agent/products/status', 'AgentProductController@changeStatus');
Route::post('agent/products/deleteMultiple', 'AgentProductController@deleteMultiple');
// Route::get('agent/products/{id}','AgentProductController@details');
Route::get('agents/products', 'AgentProductController@list');


// agent offers add many offers
//Route::post('agent/offers', 'AgentOfferController@create');
//Route::post('agent/offers/update', 'AgentOfferController@update');
//Route::post('agent/offers/updateValueById', 'AgentOfferController@updateValueById');
//Route::post('agent/offers/update/{id}', 'AgentOfferController@update2');
//Route::post('agent/offers/copy', 'AgentOfferController@copy');
//
//Route::any('agent/offers/search', 'AgentOfferController@search');
//Route::delete('agent/offers/{id}', 'AgentOfferController@delete');
//Route::post('agent/offers/status', 'AgentOfferController@changeStatus');
//Route::post('agent/offers/deleteMultiple', 'AgentOfferController@deleteMultiple');
//// Route::get('agent/offers/{id}','AgentOfferController@details');
//Route::get('agents/offers', 'AgentOfferController@list');



// favorite product apis
Route::get('product/favorite/user/{userId}', 'FavoriteProductController@listUserFavoriteProduct');
Route::post('product/favorite', 'FavoriteProductController@create');
Route::delete('product/favorite/{id}', 'FavoriteProductController@delete');
Route::delete('favorite/clear/{userId}', 'FavoriteProductController@clear');
Route::post('product/delete/favorite', 'FavoriteProductController@deleteMultiple');
Route::post('product/favorite/remove', 'FavoriteProductController@removeFromFavoriteList');

// notifications apis
Route::post('notifications', 'NotificationController@create');

// coupon apis
Route::get('coupon', 'CouponController@list');
Route::post('coupon/status', 'CouponController@changeStatus');

Route::get('coupon/{id}', 'CouponController@details');
Route::post('coupon/code', 'CouponController@detailsByCode');
Route::post('coupon', 'CouponController@create');
Route::post('coupon/update', 'CouponController@update');
//Route::post('coupon/check', 'CouponController@check'); // use in mobile
Route::post('points/apply', 'PointsController@apply');
Route::delete('coupon/{id}', 'CouponController@delete');

// order product apis
Route::post('order/product/list', 'OrderProductController@listOrderProduct');
// Route::post('order/product/update','OrderProductController@update');
// Route::delete('product/order/{id}','OrderProductController@delete');
// Route::delete('order/clear/{userId}','OrderProductController@clear');
// Route::post('product/delete/order','OrderProductController@deleteMultiple');


// order product apis
// Route::post('cart/product/list', 'CartProductController@listCartProduct');
Route::group(['user' => 'user', 'middleware' => ['assign.guard:api', 'jwt.auth']], function ()
{

//    Route::post('cart/favorite/add', 'CartProductController@addFavoriteToCart');

});
//Route::delete('cart/clear/{cartId}', 'CartProductController@clear');
//Route::post('cart/delete/products', 'CartProductController@deleteMultiple');
//
//Route::post('cart/product/list', 'CartProductController@listCartProduct');
Route::post('cart/product/update', 'CartProductController@update');

// order  apis
Route::post('order/list', 'OrderController@listOrderOfUser');
Route::post('order', 'OrderController@create');// mobile
Route::post('order/portal/create', 'OrderController@createOrderFromPortal');
//Route::post('order/cancel', 'OrderController@cancelOrderByclient');
Route::post('order/update-time-slot','OrderController@UpdateTimeSlot');
Route::post('order/review', 'OrderController@review');
Route::post('order/update', 'OrderController@update');
Route::delete('order/{id}', 'OrderController@delete');
Route::delete('order/delete/{id}', 'OrderController@delete');

Route::post('order/reorder', 'OrderController@reorder');
// api in portal
Route::get('orders/list', 'OrderController@listAllOrders');
Route::post('orders/new-search', 'OrderController@newSearch');
Route::post('orders/create','OrderController@place');
Route::post('orders/update-carton-amount','OrderController@UpdateCartonAmount');
Route::post('products/agent','AgentProductController@getAgentProduct');
Route::any('orders/review/list', 'OrderController@listAllReviewedOrders');
Route::get('order/details/{id}', 'OrderProductController@details');
Route::get('order/view/{id}', 'OrderProductController@viewDetails');
Route::post('order/products', 'OrderController@getProductsOfOrder');
Route::post('order/status', 'OrderController@changeStatus');

// Route::post('reviewClient','OrderController@reviewClient');
// banner apis
Route::get('banner', 'BannerController@list');
Route::get('banner/{id}', 'BannerController@details');
Route::post('banner', 'BannerController@create');
Route::post('banner/status', 'BannerController@changeStatus');

Route::post('banner/update', 'BannerController@update');
Route::delete('banner/{id}', 'BannerController@delete');
Route::post('banner/search', 'BannerController@search');

// client
Route::group(['user' => 'user', 'middleware' => ['assign.guard:api', 'jwt.auth']], function ()
{
    Route::get('/user', 'UserController@getAgent');
    Route::get('/user/points', 'UserController@getUserPoints');
    Route::get('notifications/client', 'NotificationController@listClientNotifications');

});
Route::post('user/logout', 'UserController@logout');
// Route::post('user/search','UserController@searchInIndex');
Route::post('user/status', 'UserController@changeStatus');
Route::get('user/search', 'UserController@getSearchResults');
Route::post('users/search', 'UserController@search');
Route::get('users/search', 'UserController@search');

Route::get('users', 'UserController@list');
Route::post('users', 'UserController@list');
Route::get('user/{id}', 'UserController@details');
Route::post('user/update', 'UserController@update');
Route::post('user/updateClient', 'UserController@updateNameAndMobile');
Route::post('user/language', 'UserController@updateLanguage');

Route::post('/user/search', 'UserController@search');
Route::post('user', 'UserController@create');
Route::post('user/create', 'UserController@register');
Route::post('user/friendPoints', 'UserController@addFriendMobile');
Route::post('user/login', 'UserController@login');
Route::post('user/forgetPassword', 'UserController@checkUserToResetPassword');
Route::delete('/user/{id}', 'UserController@delete');

// client sms
Route::post('sms', 'SmsController@check');
Route::post('user/changePassword', 'SmsController@changePassword');

// setting apis
Route::get('setting', 'SettingController@list');
Route::get('setting/{id}', 'SettingController@details');
Route::post('setting', 'SettingController@create');
Route::post('setting/update', 'SettingController@update');
Route::get('setting/about', 'SettingController@getAboutValue');
Route::delete('setting/{id}', 'SettingController@delete');
Route::post('setting/quantity', 'SettingController@checkQuantity');
Route::post('setting/point/policy', 'SettingController@updatePointsPolicy');
Route::post('point/policy', 'SettingController@getPointsPolicy');

Route::group(['prefix' => 'customers'],function(){
    Route::get('/','CustomerController@index');
});
// payment type
//s
Route::get('payment-types/list', 'PaymentTypeController@list');
Route::post('payment-types/status', 'PaymentTypeController@changeStatus');


Route::group(['prefix' => 'order-schedule-slot'],function(){
    Route::get('/list', 'SettingController@orderScheduleSlotList');
    Route::post('/status', 'SettingController@orderScheduleSlotsStatus');
    Route::post('/update', 'SettingController@orderScheduleSlotUpdate');
});

Route::group(['prefix' => 'delivery-flat-locations'],function(){
    Route::get('/list', 'DeliveryFlatLocationController@deliveryFlatLocationList');
    Route::post('/status', 'DeliveryFlatLocationController@deliveryFlatLocationsStatus');
    Route::post('/add', 'DeliveryFlatLocationController@store');
    Route::post('/update', 'DeliveryFlatLocationController@update');
    Route::delete('/delete/{id}', 'DeliveryFlatLocationController@delete');
});
Route::group(['prefix' => 'with-hold-agent-points'],function(){
    Route::get('/list', 'WithHoldingAgentPointController@index');
    Route::post('/add', 'WithHoldingAgentPointController@store');
    Route::post('/update', 'WithHoldingAgentPointController@update');
    Route::delete('/delete/{id}', 'WithHoldingAgentPointController@delete');
});

Route::group(['prefix' => 'order-time-slots'],function(){
    Route::get('/list', 'TimeSlotController@timeSlotList');
    Route::get('/details/{id}', 'TimeSlotController@edit');
    Route::post('/update', 'TimeSlotController@update');
    Route::post('/store', 'TimeSlotController@store');
    Route::post('/status', 'TimeSlotController@changeStatus');
    Route::delete('/delete/{id}', 'TimeSlotController@delete');
});

Route::group(['prefix' => 'postpone-reason'],function(){
    Route::get('/list', 'PostponeReasonController@index');
    Route::get('/details/{id}', 'PostponeReasonController@edit');
    Route::post('/update', 'PostponeReasonController@update');
    Route::post('/store', 'PostponeReasonController@store');
    Route::post('/status', 'PostponeReasonController@changeStatus');
    Route::delete('/delete/{id}', 'PostponeReasonController@delete');
});
// setting apis
Route::get('complain', 'ComplainController@list');
Route::post('complain/search', 'ComplainController@search');
Route::get('complain/{id}', 'ComplainController@details');
Route::post('complain', 'ComplainController@create');
Route::post('complain/update', 'ComplainController@update');
Route::delete('complain/{id}', 'ComplainController@delete');
Route::post('complain/status', 'ComplainController@changeStatus');

// cities apis
Route::get('city', 'CityController@list');
Route::get('city/region/{regionId}', 'CityController@listRegionCities');
Route::get('city/{id}', 'CityController@details');
Route::post('city', 'CityController@create');
Route::post('city/status', 'CityController@changeStatus');
Route::post('city/update', 'CityController@update');
Route::delete('city/{id}', 'CityController@delete');

// regions apis
Route::get('region', 'RegionController@list');
Route::post('region/status', 'RegionController@changeStatus');
Route::get('region/{id}', 'RegionController@details');
Route::post('region', 'RegionController@create');
Route::post('region/update', 'RegionController@update');
Route::delete('region/{id}', 'RegionController@delete');

// file apis
Route::post('image/banner', 'FileController@uploadBanner');
Route::post('image/product', 'FileController@uploadProductImage');
Route::post('image/offer', 'FileController@uploadOfferImage');

// points
Route::get('loyaltyPoints', 'ReferralProgramController@list');

Route::any('checkout/notify', 'PaymentController@notify');
Route::post('checkout', 'PaymentController@checkout');
Route::get('checkout/list', 'PaymentController@list');
Route::post('payment/status', 'PaymentController@getStatus');

// rejectionReason apis
Route::get('reason', 'RejectionReasonController@list');
Route::get('reason/mobile/list', 'RejectionReasonController@listForApp');
Route::post('reason/status', 'RejectionReasonController@changeStatus');
Route::get('reason/{id}', 'RejectionReasonController@details');
Route::post('reason', 'RejectionReasonController@create');
Route::post('reason/update', 'RejectionReasonController@update');
Route::delete('reason/{id}', 'RejectionReasonController@delete');

// reports
Route::post('dashboard/pie', 'OrderController@getOrdersCount');
Route::post('orders/search', 'OrderController@search');
Route::post('payment/online', 'OrderController@sumReport');
Route::post('agent/delivery', 'OrderController@deliveryReport');
Route::post('products/top', 'OrderController@topProducts');
Route::post('orders/quantity', 'OrderController@quantityReport');
Route::post('agent/client/count', 'OrderController@countUsersOfAgent');
Route::post('dashboard/revenue', 'OrderController@getOrdersRevenue');
Route::any('agent/points', 'PointsController@agentPoints');
Route::any('agent/points/list', 'PointsController@list');
Route::post('agents/update-points', 'PointsController@updateClientPoint');

Route::get('agent/points/delete/{id}', 'PointsController@delete');
Route::get('agent/points/show/{id}', 'PointsController@show');
Route::post('agent/points/update', 'PointsController@update');




Route::any('user/subscription/points', 'UserController@addSbscriptionPoints');

Route::get('test', 'UserController@test');
Route::post('test_fcm_notification', 'NotificationController@testSendFCM');
Route::post('test-notification', 'NotificationController@sendTestNotification');
Route::post('admin-notify-agents', 'CpFcmNotificationController@AdminNotifyAgent');
Route::post('agent-notify-users', 'CpFcmNotificationController@AgentNotifyUsers');
Route::get('agent-notification-list', 'CpFcmNotificationController@agentNotificationlist');
Route::get('admin-notification-list', 'CpFcmNotificationController@adminNotificationlist');

Route::post('create-permission','PermissionController@create');
Route::post('delete-permission/{id}','PermissionController@delete');
Route::post('delete-all-permission','PermissionController@destroy');
Route::get('get-permissions-list','PermissionController@getPermissionsList');
Route::post('assign-permissions','PermissionController@assign');
Route::post('get-admin-permission','PermissionController@getAdminPermissions');


