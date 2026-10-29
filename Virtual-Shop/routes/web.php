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

Route::get('/', [\App\Http\Controllers\Site\SiteController::class,'index']);
Route::get('/about-us', function (){
    return view('site.about-us');
});

Route::get('/contact-us', function (){
    return view('site.contact-us');
});
Route::get('/terms-conditions', function (){
    return view('site.terms-conditions');
});


Route::get('/test', [\App\Http\Controllers\Controller::class,'CreateNewVirtualNumbersProduct']);
//Route::get('/test-ajax', [\App\Http\Controllers\Site\ShopController::class,'text_ajax']);
Route::get('/get-country-ajax', [\App\Http\Controllers\Site\SiteController::class,'GetServicesOfCountryNew'])->name('get_country_ajax');
Route::get('/get-product-ajax', [\App\Http\Controllers\Site\SiteController::class,'GetProductAjax'])->name('GetProductAjax');
Route::get('/blog', [\App\Http\Controllers\Site\SiteController::class,'Blogs'])->name('Blogs');
Route::get('/blog/{id}', [\App\Http\Controllers\Site\SiteController::class,'SingleBlog'])->name('Single Blog');
Route::post('/send-comment', [\App\Http\Controllers\Site\SiteController::class,'sendComment'])->name('sendComment');

Route::get('services/{id}', [\App\Http\Controllers\Site\SiteController::class,'SingleService'])->name('Single Service');
Route::get('country/{id}', [\App\Http\Controllers\Site\SiteController::class,'SingleCountry'])->name('Single Country');
Route::get('product/{id}', [\App\Http\Controllers\Site\SiteController::class,'SingleProduct'])->name('SingleProduct');

Route::get('generetSlug', [\App\Http\Controllers\Site\SiteController::class,'generetSlug'])->name('generetSlug');

Route::group(['prefix' => 'social-services','namespace' => 'App\Http\Controllers'],function (){

    Route::get('/','Site\SiteController@SocialMemberCategories')->name('SocialMemberCategories');
    Route::get('/{cat}','Site\SiteController@Category')->name('SingleServiceCategory');
    Route::get('/{cat}/{slug}','Site\SiteController@SingleSocialServiceProduct')->name('SingleSocialServiceProduct');

});



Route::get('/CreateService', [\App\Http\Controllers\Controller::class,'MakeProduct']);
Route::get('/CheckPvaQuantity', [\App\Http\Controllers\Controller::class,'CheckPvaQuantity']);
Route::get('/Products', [\App\Http\Controllers\Admin\VirtualServiceController::class,'Products']);
Route::get('/UpdateCount', [\App\Http\Controllers\Admin\VirtualServiceController::class,'UpdateCount']);
Route::get('/UpdatePrice', [\App\Http\Controllers\Admin\VirtualServiceController::class,'UpdatePrice']);
Route::get('/UpdateTomanPrice', [\App\Http\Controllers\Admin\VirtualServiceController::class,'UpdateTomanPrice']);
Route::get('/BuyCharge', [\App\Http\Controllers\Site\SiteController::class,'BuyCharge'])->name('BuyCharge');

Route::group(['prefix' => 'admin','namespace' => '\App\Http\Controllers'], function () {
  Route::get('/login', 'AdminAuth\LoginController@showLoginForm')->name('login');
  Route::post('/login', 'AdminAuth\LoginController@login');
  Route::post('/logout', 'AdminAuth\LoginController@logout')->name('logout');

  Route::get('/register', 'AdminAuth\RegisterController@showRegistrationForm')->name('register');
  Route::post('/register', 'AdminAuth\RegisterController@register');

  Route::post('/password/email', 'AdminAuth\ForgotPasswordController@sendResetLinkEmail')->name('password.request');
  Route::post('/password/reset', 'AdminAuth\ResetPasswordController@reset')->name('password.email');
  Route::get('/password/reset', 'AdminAuth\ForgotPasswordController@showLinkRequestForm')->name('password.reset');
  Route::get('/password/reset/{token}', 'AdminAuth\ResetPasswordController@showResetForm');
});

Route::group(['prefix' => 'user' ,'namespace' => '\App\Http\Controllers' ], function () {
  Route::get('/login', 'UserAuth\LoginController@showLoginForm')->name('login');
  Route::post('/login', 'UserAuth\LoginController@login');
  Route::get('/logout', 'UserAuth\LoginController@logout')->name('logout');

    Route::post('/register/step1', 'Site\RegisterController@Step1');
    Route::post('/register/step2', 'Site\RegisterController@step2');

    Route::get('/register', 'UserAuth\RegisterController@showRegistrationForm')->name('register');
  Route::post('/register', 'UserAuth\RegisterController@register');

  Route::post('/password/email', 'UserAuth\ForgotPasswordController@sendResetLinkEmail')->name('password.request');
  Route::post('/password/reset', 'UserAuth\ResetPasswordController@reset')->name('password.email');
  Route::get('/password/reset', 'UserAuth\ForgotPasswordController@showLinkRequestForm')->name('password.reset');
  Route::get('/password/reset/{token}', 'UserAuth\ResetPasswordController@showResetForm');
});
