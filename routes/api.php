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
    Route::get('google-voice', 'Api\StorefrontController@googleVoice');
    Route::get('apple-id', 'Api\StorefrontController@appleId');
    Route::get('prices', 'Api\StorefrontController@prices');
    Route::get('seo', 'Api\StorefrontController@seo');
});

Route::get('generateZoomMetting', 'ApiController@generateZoomMetting');
Route::get('get-dollar-price', 'ApiController@getDollarPrice');
Route::get('new-services', 'TelegramTools\IndexController@checkType');
Route::get('new-services/ziba-call-back','TelegramTools\IndexController@zibalCallback')->name('new-service-zibal-callback');


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

Route::group(['prefix' => 'hegza'], function () {
    Route::post('testTelegram', 'Api\BotController@testTelegram');
    Route::get('buy-ip', 'Api\BotController@buyIp');
    Route::get('get-service', 'Api\BotController@getService');
    Route::get('get-bandwidth', 'Api\BotController@getBandwidth');
    Route::get('submit-order', 'Api\BotController@submitOrder');
    Route::get('zarinpal-callback', 'Api\BotController@zarinPalCallback');
    Route::post('zarinpal-callback', 'Api\BotController@zarinPalCallback');

    Route::get('order-list', 'Api\BotController@ordersList');
    Route::get('show-order', 'Api\BotController@showOrder');
    Route::get('get-bandwidth-for-order', 'Api\BotController@getBandwidthForOrder');
    Route::get('buy-bandwidth', 'Api\BotController@buyBandwidth');
    Route::get('get-renew-items', 'Api\BotController@getRenewItems');
    Route::get('buy-renew-order', 'Api\BotController@buyRenewIp');
    Route::get('buy-volume-wallet', 'Api\BotController@buyVolumeWallet');
    Route::get('buy-renew-wallet', 'Api\BotController@buyRenewWithWallet');
    Route::get('cart-be-cart', 'Api\BotController@cartBeCart');
    Route::get('send-document-cart-be-cart', 'Api\BotController@sentDocumentForCartBeCart');
    Route::get('confirm-cart-be-cart', 'Api\BotController@confirmCartBeCart');

    Route::get('search-by-code-step-one', 'Api\BotController@searchByCodeStepOne');
    Route::get('search-by-code-step-two', 'Api\BotController@searchByCodeStepTwo');

    Route::get('show-profile', 'Api\BotController@showProfile');
    Route::get('charge-wallet', 'Api\BotController@chargeWallet');

    Route::get('test-server-list', 'Api\BotController@testServersList');
    Route::get('get-server-list', 'Api\BotController@getServersList');

    Route::get('buy-order-wallet','Api\BotController@buyOrderWithWalletV2');

    Route::get('referral', 'Api\BotController@referral');

    Route::get('telegramSendMessage', 'Api\BotController@telegramSendMessage');
    Route::get('report', 'Api\BotController@report');

    Route::get('home', 'Api\BotController@Home');
    Route::get('rules', 'Api\BotController@rules');

    Route::get('showPage', 'Api\BotController@showPage');
    Route::get('/product-detail', 'Api\BotController@IpSabetProductDetailJson')->name('ip-sabet-product-detail');
    Route::post('/submit-order', 'Api\BotController@SubmitOrderFromSite')->name('ip-sabet-submit-order');

    Route::get('sync-order', 'Api\BotController@syncOrder');
    Route::get('show-detail/{id}', 'Api\BotController@showDetail')->name('bot-show-order');

    Route::get('checkSuspendedOrders','Api\BotController@checkSuspendedOrders');
    Route::get('checkFailedOrders','Api\BotController@checkFailedOrders');

    Route::get('charge-crypto','Api\BotController@cryptoCharge');

});


Route::group(['prefix' => 'bot'], function () {
    Route::get('checkNumber', 'Api\ArayeshController@checkNumber');
    Route::get('setNumber', 'Api\ArayeshController@setNumber');
    Route::get('checkCode', 'Api\ArayeshController@checkCode');
});

Route::group(['prefix' => 'bank-api'],function (){
    Route::get('step-one', 'BankApiController@stepOne');
    Route::get('step-two', 'BankApiController@stepTwo');
    Route::get('step-three', 'BankApiController@stepThree');
    Route::post('step-three', 'BankApiController@stepThree');
    Route::get('step-four', 'BankApiController@checkTransaction');
    Route::post('step-four', 'BankApiController@checkTransaction');
});



