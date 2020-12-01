<?php

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;

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
Route::post('register','Api\AuthController@register');
Route::post('registerAdmin','Api\AuthController@registerAdmin');
Route::post('login','Api\AuthController@login');
Route::post('loginAdmin','Api\AuthController@loginAdmin');
Route::get('email/verify/{id}', 'Api\VerificationController@verify')->name('verification.verify');

Route::group(['middleware' => 'auth:api'], function(){
    Route::get('item', 'Api\ItemController@index');
    Route::get('item/{id}', 'Api\ItemController@show');
    Route::post('item', 'Api\ItemController@store');
    Route::put('item/{id}', 'Api\ItemController@update');
    Route::delete('item/{id}', 'Api\ItemController@destroy');

    //Route::get('order', 'Api\OrderController@index');
    //Route::get('order/{id}', 'Api\OrderController@show');
    Route::get('order/{id}', 'Api\OrderController@index');
    Route::get('orderFinished/{id}', 'Api\OrderController@indexByFinished');
    Route::get('orderProcessInAdmin', 'Api\OrderController@indexByProcessInAdmin');
    Route::get('orderFinishedInAdmin', 'Api\OrderController@indexByFinishedInAdmin');

    Route::post('order', 'Api\OrderController@store');
    Route::put('order/{id}', 'Api\OrderController@update');
    Route::delete('order/{id}', 'Api\OrderController@destroy');

    Route::get('promo', 'Api\PromoController@index');
    Route::get('promo/{id}', 'Api\PromoController@show');
    Route::post('promo', 'Api\PromoController@store');
    Route::put('promo/{id}', 'Api\PromoController@update');
    Route::delete('promo/{id}', 'Api\PromoController@destroy');

    Route::get('logout', 'Api\AuthController@logout');

    Route::get('profil/{id}','Api\AuthController@show');
    Route::put('profil/{id}','Api\AuthController@update');
});
