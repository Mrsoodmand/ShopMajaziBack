<?php

Route::get('/home', [\App\Http\Controllers\User\HomeController::class,'index'])->name('home');
Route::get('/referral', [\App\Http\Controllers\User\HomeController::class,'Referral'])->name('referral');

/**/
Route::group(['prefix' => 'virtual-number','namespace' => '\App\Http\Controllers'],function (){
    Route::post('buy-step-one','Site\ShopController@PayVirtualNumberWithBank')->name('PayVirtualNumberWithBank');
    Route::get('show-number/{order_id}','Site\ShopController@ShowNumber')->name('ShowNumber');
    Route::get('check-sms-number/{order_id}','Site\ShopController@CheckSmsNumber')->name('CheckSmsNumber');
});
/**/
Route::group(['prefix' => 'payment'] ,function (){
    Route::post('step-one','Payment\BankController@StepOne')->name('service-bank-payment');
    Route::post('callback','Payment\CallBackController@BankCallBack')->name('service-bank-callback');
    Route::get('callback','Payment\CallBackController@BankCallBack')->name('service-bank-callback');
    Route::get('/detail','User\WalletController@CheckTransactionDetail')->name('check-transaction-detail');
    Route::get('wallet/callback','Payment\WalletController@WalletCallBack')->name('WalletCallBack');
    Route::get('wallet/{type}/{id}','Payment\WalletController@PayWithWallet')->name('PayWithWallet');
});
/**/
Route::group(['prefix' => 'orders'] ,function (){
    Route::get('/','User\OrdersController@index')->name('orders-list');
    Route::get('/{id}','User\OrdersController@SingleOrder')->name('single-order');
    Route::post('send/chat','User\OrdersController@SendOrderChat')->name('chat-order');
});
/**/
Route::group(['prefix' => 'wallet'] ,function (){
    Route::get('/','User\WalletController@index')->name('wallet-list');
    Route::post('/charge-wallet','v2\ShopController@ChargeWallet')->name('ChargeWallet');
    Route::get('/callback','v2\ShopController@ChargeWalletCallBack')->name('ChargeWalletCallBack');
});
/**/
Route::group(['prefix' => 'tickets'] ,function (){
    Route::get('/','User\TicketController@index')->name('ticket-list');
    Route::get('/create','User\TicketController@create')->name('ticket-create');
    Route::post('/submit','User\TicketController@submit')->name('ticket-submit');
    Route::post('/update','User\TicketController@update')->name('ticket-update');
    Route::get('/{id}','User\TicketController@detail')->name('ticket-detail');
});
/**/
Route::group(['prefix' => 'profile'] ,function (){

    Route::get('/','User\HomeController@profile')->name('profile');
    Route::post('/submit','User\HomeController@submit_profile')->name('');

});







