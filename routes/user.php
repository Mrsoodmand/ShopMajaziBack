<?php

/*
 * The old Blade customer panel is retired: its pages redirect to the Next.js panel (/account, StorefrontUrl), keeping
 * their route names so payment callbacks and old links that still build them land on the matching /account page.
 * Payment, callback and form-post routes below are unchanged.
 */
$Panel = fn (string $Path) => fn ($id = null) => redirect(StorefrontUrl('/account' . str_replace('{id}', (string) $id, $Path)));

Route::get('/home', $Panel(''))->name('home');
Route::get('/referral', $Panel(''))->name('referral');

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
Route::group(['prefix' => 'orders'] ,function () use ($Panel) {
    Route::get('/', $Panel('/orders'))->name('orders-list');
    Route::get('/{id}', $Panel('/orders/{id}'))->name('single-order');
    Route::post('send/chat','User\OrdersController@SendOrderChat')->name('chat-order');
});
/**/
Route::group(['prefix' => 'wallet'] ,function () use ($Panel) {
    Route::get('/', $Panel('/funds'))->name('wallet-list');
    Route::post('/charge-wallet','v2\ShopController@ChargeWallet')->name('ChargeWallet');
    Route::get('/callback','v2\ShopController@ChargeWalletCallBack')->name('ChargeWalletCallBack');
});
/**/
Route::group(['prefix' => 'tickets'] ,function () use ($Panel) {
    Route::get('/', $Panel('/support'))->name('ticket-list');
    Route::get('/create', $Panel('/support/new'))->name('ticket-create');
    Route::post('/submit','User\TicketController@submit')->name('ticket-submit');
    Route::post('/update','User\TicketController@update')->name('ticket-update');
    Route::get('/{id}', $Panel('/support/{id}'))->name('ticket-detail');
});
/**/
Route::group(['prefix' => 'profile'] ,function () use ($Panel) {

    Route::get('/', $Panel('/profile'))->name('profile');
    Route::post('/submit','User\HomeController@submit_profile')->name('');

});







