<?php

Route::group(['middleware' => 'localization'],function(){
    Route::get('/home','HomeController@home');
    
    Route::get('/regions','HomeController@regions');
    Route::get('/cities/{regionId}','HomeController@cities');
    Route::get('/districts/{cityId}','HomeController@districts');
    Route::get('/dash/client-app-settings','SettingController@clientAppSettings');

    Route::group(['namespace'=>'Customer'],function(){
        Route::post('/check-flat-location-delivery-cost','CartController@getFlatLocationPrice');
        Route::post('/flat-price-by-agent','CartController@getFlatPriceByAgent');
    });

    Route::group(['middleware' => 'auth:sanctum'], function () {
        Route::get('/client-app-settings','SettingController@clientAppSettings');
        Route::get('/client-cart-settings','SettingController@clientCartSettings');
        Route::post('/agent-minimum-cartons','SettingController@getAgentMinimumCartons');
    });
});
