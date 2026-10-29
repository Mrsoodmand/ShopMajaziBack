<?php

use Illuminate\Support\Facades\Route;

/*
|--------------------------------------------------------------------------
| Web Routes
|--------------------------------------------------------------------------
|
| Here is where you can register web routes for your application. These
| routes are loaded by the RouteServiceProvider within a group which
| contains the "web" middleware group. Now create something great!
|
*/


Route::get('/', 'v2\HomeController@Home');
Route::get('/readXml', 'v2\HomeController@readXml');
Route::get('/faq', function () {
    return view('site-v2.faq', ['PageSeo' => app(\App\Services\PageSeo::class)->resolved('faq')]); // admin › SEO
});
Route::get('/contact-us', 'v2\HomeController@ContactUs');
Route::post('/contact-us/submit', 'v2\HomeController@submitContactUs');
Route::post('/send-comment', 'v2\HomeController@sendComment')->name('sendComment');

/* Factor */
Route::group(['prefix' => 'wedding'], function () {
    Route::get('/', 'Controller@wedding');
    Route::post('/weddingForm', 'Controller@weddingForm');
});


/* اکانت های خارجی */
Route::group(['prefix' => 'accounts'], function () {
    Route::get('/', 'v2\AccountsController@index');
    Route::get('/{slug}', 'v2\AccountsController@SingleAccount');
});


/* وبلاگ */
Route::group(['prefix' => 'blogs'], function () {
    Route::get('/', function () {
        return redirect('blog',301);
    });
    Route::get('/{id}', function ($id){
        return redirect('blog/'.$id,301);
    });
});


Route::group(['prefix' => 'blog'], function () {
    Route::get('/', 'v2\BlogController@index')->name('Blogs');
    Route::get('/sitemap.xml', 'v2\BlogController@blogSiteMap')->name('blogSiteMap');
    Route::get('/{id}', 'v2\BlogController@single')->name('Single Blog');
});


/* اخبار */
Route::group(['prefix' => 'tech-news'], function () {
    Route::get('/', 'v2\BlogController@news')->name('news');
    Route::get('/sitemap.xml', 'v2\BlogController@newsSiteMap')->name('newsSiteMap');
    Route::get('/{id}', 'v2\BlogController@singleNews')->name('Single news');
});

/* گوگل ویس */
Route::group(['prefix' => 'google-voice'], function () {
    Route::get('/', 'v2\GoogleVoiceController@index');
});

/* Factor */
Route::group(['prefix' => 'factor'], function () {
    Route::post('/{id}', 'v2\OrderController@Factor')->name('submit-factor');
});

/* اپی ثابت */
Route::group(['prefix' => 'static-ip'], function () {
    Route::get('/', 'v2\IpSabetController@index');
    Route::get('/product-detail', 'v2\IpSabetController@IpSabetProductDetailJson')->name('ip-sabet-product-detail');
    Route::post('/submit-order', 'v2\IpSabetController@SubmitOrder')->name('ip-sabet-submit-order');
});

/* اپل آیدی */
Route::group(['prefix' => 'apple-id'], function () {
    Route::get('/', 'v2\AppleIdController@index');
});

/* شماره مجازی */
Route::get('/virtual-numbers', 'v2\VirtualNumberController@index')->name('virtual number index');
// /virtual-numbers is the Next.js landing (frontend/); the Blade purchase page (orders, SMS polling) lives on /virtual-number/buy.
Route::get('/virtual-number', function (){
    return redirect()->route('virtual-number-buy');
});
Route::group(['prefix' => 'virtual-number'], function () {
    Route::get('buy', 'v2\VirtualNumberController@index')->name('virtual-number-buy');
    Route::get('checkout/{id}', 'v2\VirtualNumberController@Checkout')->name('virtual-number-checkout');
    Route::get('/get-virtual-numbers', 'v2\VirtualNumberController@GetNumbers')->name('get-virtual-service');

    Route::get('get-virtual-single-number', 'v2\VirtualNumberController@GetSingleNumberDetail')->name('get-single-virtual-number');
    Route::get('cancel-virtual-number', 'v2\VirtualNumberController@CancelNumber')->name('cancel-virtual-number');
    Route::get('get-sms', 'v2\VirtualNumberController@CheckNumberSms')->name('get-sms');
    Route::get('filter', 'v2\VirtualNumberController@filterNumbers')->name('filter-numbers');
});
/* v2 */
Route::group(['prefix' => 'virtual-service'], function () {
    Route::get('/', 'v2\VirtualServiceController@index');
    Route::get('/{category}', 'v2\VirtualServiceController@ServiceCategory');
    Route::get('/{category}/{id}', 'v2\VirtualServiceController@SingleService')->name('virtual-service');
});
/* v2 */
Route::group(['prefix' => 'login'], function () {
    Route::post('/check-user', 'v2\LoginAndRegisterController@CheckNumber')->name('CheckNumber');
    Route::post('/login', 'v2\LoginAndRegisterController@Login');
    Route::post('/register', 'v2\LoginAndRegisterController@NewRegister')->name('NewRegister');
    Route::get('/new-register-code', 'v2\LoginAndRegisterController@SendNewCode')->name('SendNewCode');
    Route::get('reset-password-v2', 'v2\LoginAndRegisterController@resetPassword');
    Route::get('fast-login-code', 'v2\LoginAndRegisterController@fastLoginCode');
    Route::post('fast-login', 'v2\LoginAndRegisterController@fastLogin');

});
/* v2 */
Route::group(['prefix' => 'cart'], function () {
    Route::get('/', 'v2\CartController@Cart')->name('Cart');
    Route::post('add', 'v2\CartController@AddToCart')->name('AddToCart');
    Route::get('delete/{id}', 'v2\CartController@DeleteFromCart')->name('DeleteFromCart');
});
/* Next.js storefront (Front/): sign-in, wallet and virtual-number orders. Web group = shared session; reached
   through the Next.js /api/v1 rewrite. POSTs need the X-XSRF-TOKEN header (from the XSRF-TOKEN cookie). */
Route::group(['prefix' => 'api/v1/storefront/account', 'namespace' => 'Api'], function () {
    Route::get('me', 'StorefrontAccountController@me');
    Route::group(['middleware' => 'throttle:15,1'], function () {
        Route::post('start', 'StorefrontAccountController@start');
        Route::post('code', 'StorefrontAccountController@code');
        Route::post('login', 'StorefrontAccountController@login');
        Route::post('register', 'StorefrontAccountController@register');
    });
    Route::post('logout', 'StorefrontAccountController@logout');
    Route::get('virtual-numbers/orders', 'StorefrontAccountController@orders');
    Route::post('virtual-numbers/buy', 'StorefrontAccountController@buy')->middleware('throttle:10,1');
    Route::post('virtual-numbers/pay-card', 'StorefrontAccountController@payByCard')->middleware('throttle:10,1');
    Route::get('dev-bank/{authority}', 'StorefrontAccountController@devBank'); // local development only (404 otherwise)
    Route::get('virtual-numbers/orders/{id}', 'StorefrontAccountController@order')->middleware('throttle:40,1');
    Route::post('virtual-numbers/orders/{id}/cancel', 'StorefrontAccountController@cancel');
    Route::post('google-voice/buy', 'StorefrontAccountController@googleVoiceBuy')->middleware('throttle:10,1');
    Route::post('google-voice/pay-card', 'StorefrontAccountController@googleVoicePayCard')->middleware('throttle:10,1');
    Route::get('google-voice/orders/{id}', 'StorefrontAccountController@googleVoiceOrder')->middleware('throttle:40,1');
    Route::post('apple-id/buy', 'StorefrontAccountController@appleIdBuy')->middleware('throttle:10,1');
    Route::post('apple-id/pay-card', 'StorefrontAccountController@appleIdPayCard')->middleware('throttle:10,1');
    Route::get('apple-id/orders/{id}', 'StorefrontAccountController@appleIdOrder')->middleware('throttle:40,1');
    Route::post('services/buy', 'StorefrontAccountController@servicesBuy')->middleware('throttle:10,1');
    Route::post('services/pay-card', 'StorefrontAccountController@servicesPayCard')->middleware('throttle:10,1');
    Route::get('services/orders/{id}', 'StorefrontAccountController@servicesOrder')->middleware('throttle:40,1');
});

/* Next.js storefront: who is logged in (session cookie reaches Laravel through the Next.js fallback rewrite) */
Route::get('/storefront/me', function () {
    $User = \Illuminate\Support\Facades\Auth::guard('user')->user();
    return response()->json([
        'success' => true,
        'message' => null,
        'data' => [
            'authenticated' => !is_null($User),
            'wallet' => is_null($User) ? 0 : (int)($User->wallet + $User->blocked_wallet),
        ],
    ])->header('Cache-Control', 'no-store');
});

/* v2 */
Route::group(['prefix' => 'payment'], function () {
    Route::post('step-one', 'Payment\BankController@StepOne')->name('service-bank-payment');
    Route::post('callback', 'Payment\CallBackController@BankCallBack')->name('service-bank-callback');
    Route::get('callback', 'Payment\CallBackController@BankCallBack')->name('service-bank-callback');
    Route::get('callback', 'Payment\CallBackController@BankCallBack')->name('service-bank-callback');

    Route::get('wallet/{type}/{id}', 'Payment\WalletController@PayWithWallet')->name('PayWithWallet');
});


Route::post('/buy-sim-card-charge', [\App\Http\Controllers\v2\ShopController::class, 'buyCharge'])->name('charge-simcard');
Route::get('/send-to-bank/{id}', [\App\Http\Controllers\v2\ShopController::class, 'ZarinPalSendToBankBuySimCardCharge']);
Route::get('buy-charge-callback', [\App\Http\Controllers\v2\ShopController::class, 'callbackBankBuySimCardCharge']);
Route::get('/callback', [\App\Http\Controllers\v2\ShopController::class, 'callback']);
Route::post('/callback', [\App\Http\Controllers\v2\ShopController::class, 'callback']);


Route::get('/ropsida', function () {
    return view('site.ropsida');
});
Route::get('/about-us', function () {
    return view('site-v2.about-us', ['PageSeo' => app(\App\Services\PageSeo::class)->resolved('about-us')]); // admin › SEO
});

Route::get('/terms-conditions', function () {
    return view('site.terms-conditions');
});
Route::get('/buy-sim-card-charge', function () {
    return view('site.buy-charge');
});


//Route::get('/test', [\App\Http\Controllers\Controller::class,'CreateNewVirtualNumbersProduct']);
//Route::get('/test-ajax', [\App\Http\Controllers\Site\ShopController::class,'text_ajax']);
Route::get('/get-country-ajax', [\App\Http\Controllers\Site\SiteController::class, 'GetServicesOfCountryNew'])->name('get_country_ajax');
Route::get('/get-product-ajax', [\App\Http\Controllers\Site\SiteController::class, 'GetProductAjax'])->name('GetProductAjax');

Route::get('services/{id}', [\App\Http\Controllers\Site\SiteController::class, 'SingleService'])->name('Single Service');
Route::get('country/{id}', [\App\Http\Controllers\Site\SiteController::class, 'SingleCountry'])->name('Single Country');
//Route::get('product/{id}', [\App\Http\Controllers\Site\SiteController::class, 'SingleProduct'])->name('SingleProduct');

Route::get('generetSlug', [\App\Http\Controllers\Site\SiteController::class, 'generetSlug'])->name('generetSlug');

Route::get('CreateClass', [\App\Http\Controllers\Site\SiteController::class, 'CreateClass'])->name('CreateClass');


Route::get('/CreateService', [\App\Http\Controllers\Controller::class, 'MakeProduct']);
Route::get('/CheckPvaQuantity', [\App\Http\Controllers\Controller::class, 'CheckPvaQuantity']);
Route::get('/Products', [\App\Http\Controllers\Admin\VirtualServiceController::class, 'Products']);
Route::get('/UpdateCount', [\App\Http\Controllers\Admin\VirtualServiceController::class, 'UpdateCount']);
Route::get('/UpdatePrice', [\App\Http\Controllers\Admin\VirtualServiceController::class, 'UpdatePrice']);
Route::get('/UpdateTomanPrice', [\App\Http\Controllers\Admin\VirtualServiceController::class, 'UpdateTomanPrice']);
Route::get('/BuyCharge', [\App\Http\Controllers\Site\SiteController::class, 'BuyCharge'])->name('BuyCharge');

Route::group(['prefix' => 'admin-url-login-with-secure-path', 'namespace' => '\App\Http\Controllers'], function () {
    Route::get('/login', 'AdminAuth\LoginController@showLoginForm')->name('login');
    Route::post('/login', 'AdminAuth\LoginController@login')->name('admin-login');
    Route::get('/logout', 'AdminAuth\LoginController@logout')->name('logout');
});

Route::group(['prefix' => 'user'], function () {
    Route::get('/login', 'UserAuth\LoginController@showLoginForm')->name('login');
    Route::post('/login', 'UserAuth\LoginController@login');
    Route::get('/logout', 'UserAuth\LoginController@logout')->name('logout');

    Route::post('/register/step1', 'Site\RegisterController@Step1');
    Route::post('/register/step2', 'Site\RegisterController@step2');
    Route::post('reset-password-v2', 'Site\RegisterController@resetPassword');

    Route::get('/password/reset', 'UserAuth\ForgotPasswordController@showLinkRequestForm')->name('password.reset');
    Route::post('/password/send-code', 'Site\RegisterController@SendCode')->name('send-code');

    Route::get('/register', 'UserAuth\RegisterController@showRegistrationForm')->name('register');
//  Route::post('/register', 'UserAuth\RegisterController@register');

    Route::post('/password/email', 'UserAuth\ForgotPasswordController@sendResetLinkEmail')->name('password.request');
    Route::post('/password/reset', 'UserAuth\ResetPasswordController@reset')->name('password.email');
    Route::get('/password/reset', 'UserAuth\ForgotPasswordController@showLinkRequestForm')->name('user-password-reset');
    Route::get('/password/reset/{token}', 'UserAuth\ResetPasswordController@showResetForm');
});


Route::get('GetPhotos', [\App\Http\Controllers\Site\SiteController::class, 'HaramEmamHosein']);
Route::get('ChargeForMom', [\App\Http\Controllers\Site\ShopController::class, 'ChargeForMom']);
Route::get('CheckJsonEncode', function () {
    $DB = \Illuminate\Support\Facades\DB::table('telegram_bot_data')->find($_GET['id']);
   return json_decode($DB->data);
});

Route::group(['prefix' => 'partner'], function () {
  Route::get('/login', 'PartnerAuth\LoginController@showLoginForm')->name('login');
  Route::post('/login', 'PartnerAuth\LoginController@login');
  Route::post('/logout', 'PartnerAuth\LoginController@logout')->name('logout');

  Route::get('/register', 'PartnerAuth\RegisterController@showRegistrationForm')->name('register');
  Route::post('/register', 'PartnerAuth\RegisterController@register');

  Route::post('/password/email', 'PartnerAuth\ForgotPasswordController@sendResetLinkEmail')->name('password.request');
  Route::post('/password/reset', 'PartnerAuth\ResetPasswordController@reset')->name('password.email');
  Route::get('/password/reset', 'PartnerAuth\ForgotPasswordController@showLinkRequestForm')->name('password.reset');
  Route::get('/password/reset/{token}', 'PartnerAuth\ResetPasswordController@showResetForm');
});

