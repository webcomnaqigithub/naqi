<?php



Route::group(['prefix' => 'customer','middleware' => 'localization','namespace' => 'Customer'],function(){


//    Route::get('/home','ProductController@HomeProducts');

    //payment
    Route::group(['prefix' => 'payment'],function(){
        Route::get('/visa-status','HyperPayPaymentController@paymentVisaStatus');
        Route::get('/mada-status','HyperPayPaymentController@paymentMadaStatus');
        Route::get('/apple-status','HyperPayPaymentController@paymentAppleStatus');
    });
    Route::group(['prefix' => 'auth'], function () {
        Route::post('/register', 'AuthController@register');
        Route::post('/login', 'AuthController@login');
        Route::post('/forget-password', 'AuthController@forgetPassword');

        Route::post('/check-otp-code', 'AuthController@checkOtp');
        Route::post('/reset-password', 'AuthController@resetPassword');
        Route::post('/send-otp', 'AuthController@sendOtpToCustomer');
        Route::post('/send-otp-code', 'AuthController@customerSendOtp')->middleware('auth:customer');
        Route::post('/logout', 'AuthController@logout')->middleware('auth:customer');
    });

    Route::group(['prefix' => 'products'],function(){

        Route::group(['middleware' => 'auth:sanctum'],function(){
            Route::get('/favorite-list', 'ProductController@favoriteList');
            Route::post('/add-remove-favorite', 'ProductController@addRemoveProductFavorite');
        });
    });


    Route::group(['middleware' => 'auth:sanctum'], function () {


        Route::post('/home','ProductController@home');
        Route::post('/update-fcm-token','AuthController@updateFcm');
        Route::post('/update-profile','AuthController@updateProfile');
        Route::get('/points','HomeController@getUserPoints');
        Route::get('/notifications','HomeController@listClientNotifications');

        //offers
        Route::group(['prefix' => 'offers'],function(){
            Route::get('/', 'ProductController@offersList');
            Route::post('/place-order', 'OrderController@offerOrders');
        });


        // cart
        Route::group(['prefix' => 'cart'],function(){
        Route::post('/add','CartController@add');
        Route::post('/remove','CartController@remove');
        Route::post('/get-customer-cart','CartController@getCustomerCart');
        Route::get('/get-settings','CartController@getSettings');
        });
        //orders
        Route::group(['prefix' => 'order'],function(){
            Route::post('/place','OrderController@place');
            Route::post('/cancel','OrderController@cancelOrderByclient');
            Route::post('/review','OrderController@review');
            Route::get('/my-orders','OrderController@getCustomerOrder');
        });
        //addresses
        Route::group(['prefix' => 'addresses'],function(){
           Route::get('/','AddressController@all');
           Route::post('/create','AddressController@create');
           Route::post('/update','AddressController@update');
           Route::post('/change-default','AddressController@changeDefaultAddress');
           Route::delete('/delete/{id}','AddressController@destroy');
        });
        //coupons
        Route::group(['prefix' => 'coupon'],function(){
            Route::post('/check','CouponController@check');
        });
        //complaints
        Route::group(['prefix'=>'complaints'],function(){
            Route::post('/send','ComplainController@send');
            Route::get('/','ComplainController@list');
        });

        //payment
        Route::group(['prefix' => 'payment'],function(){
           Route::post('/checkout','HyperPayPaymentController@getPaymentCheckoutId');
           Route::post('/offer-checkout','HyperPayPaymentController@getOfferPaymentCheckout');
           Route::get('/payment_handler','HyperPayPaymentController@payment_handler')->name('payment_handler');
           Route::get('/do_checkout','HyperPayPaymentController@do_checkout')->name('do_checkout');

        });
        //cancel order
        Route::group(['prefix' => 'cancel'],function(){
                Route::get('/reasons','OrderController@getCancelReasons');
                Route::post('/order','OrderController@cancelOrder');
        });

    });
});
