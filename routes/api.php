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

Route::middleware('auth:sanctum')->get('/user', function (Request $request) {
    return $request->user();
});

/* Next.js storefront (frontend/) */
Route::group(['prefix' => 'v1/storefront'], function () {
    Route::get('home', 'Api\StorefrontController@home');
    Route::get('virtual-services', 'Api\StorefrontController@virtualServices');
    Route::get('virtual-numbers', 'Api\StorefrontController@virtualNumbers');
    Route::get('virtual-numbers/offers', 'Api\StorefrontController@virtualNumberOffers');
    Route::get('blog', 'Api\StorefrontController@blog');
    Route::get('blog/{slug}', 'Api\StorefrontController@blogPost');
    Route::post('blog/{slug}/view', 'Api\StorefrontController@blogView')->middleware('throttle:20,1');
    Route::get('virtual-services/{slug}', 'Api\StorefrontController@serviceCategory');
    Route::get('virtual-services/{category}/{slug}', 'Api\StorefrontController@serviceDetail');
    Route::get('accounts', 'Api\StorefrontController@accounts');
    Route::get('accounts/{slug}', 'Api\StorefrontController@account');
    Route::get('google-voice', 'Api\StorefrontController@googleVoice');
    Route::get('apple-id', 'Api\StorefrontController@appleId');
    Route::get('prices', 'Api\StorefrontController@prices');
    Route::get('seo', 'Api\StorefrontController@seo');
});

Route::get('generateZoomMetting', 'ApiController@generateZoomMetting');
Route::get('get-dollar-price', 'ApiController@getDollarPrice');


Route::get('check-failed-numbers', [\App\Http\Controllers\ApiController::class, 'CheckNumbers']);
Route::get('checkSanaie', 'ApiController@checkSanaie');
Route::get('check-sms-pva-price', 'Api\VirtualNumberController@PvaCheckPrice');
Route::get('check-sms-man-price', 'Api\VirtualNumberController@SmsCheckPrice');
Route::get('check-sms-active-price', 'Api\VirtualNumberController@ActiveCheckPrice');
Route::get('check-virtual-number-order-expire', 'Api\VirtualNumberController@CheckVirtualNumberExpire');

Route::get('mailtest', 'Controller@mailtest');
Route::get('update-dollar-price', 'Api\SettingController@GetCurrencyPrice');
Route::post('sendTelegram/{token}', 'Api\SettingController@SendTelegramMessage');


Route::get('sendToZibal', 'Api\ZibalController@sendToZibal');
Route::get('CheckApi', 'ApiController@CheckApi');
Route::get('mailtest', 'ApiController@mailtest');
Route::get('buyFromSpotPlayer', 'ApiController@buyFromSpotPlayer');
Route::get('ishostingLogin', 'ApiController@ishostingLogin');

Route::get('blogs-sitemap', [\App\Http\Controllers\ApiController::class, 'blog_map']);
Route::get('news_map', [\App\Http\Controllers\ApiController::class, 'news_map']);
Route::get('newJibitPayment', [\App\Http\Controllers\ApiController::class, 'newJibitPayment']);
Route::get('checkBlogs', [\App\Http\Controllers\ApiController::class, 'checkBlogs']);

Route::group(['prefix' => 'ipsabet'], function () {
    Route::get('checkSuspendIps', 'Api\IpSabetController@checkSuspendIps');
    Route::get('checkFailed', 'Api\IpSabetController@checkFailed');
    Route::get('sendSms', 'Api\IpSabetController@sendSms');

});

Route::group(['prefix' => 'bank-api'],function (){
    Route::get('step-one', 'BankApiController@stepOne');
    Route::get('step-two', 'BankApiController@stepTwo');
    Route::get('step-three', 'BankApiController@stepThree');
    Route::post('step-three', 'BankApiController@stepThree');
    Route::get('step-four', 'BankApiController@checkTransaction');
    Route::post('step-four', 'BankApiController@checkTransaction');
});



