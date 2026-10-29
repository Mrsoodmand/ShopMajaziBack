<?php
/**
 * Orders
 * Virtual Numbers
 * Category
 */


/* ادمین */
Route::group(['prefix' => 'bot'],function (){
    Route::get('/','Bot\HomeController@index')->name('bot-index');

    Route::group(['prefix' => 'settings'], function () {

        Route::get('/','Bot\SettingController@index')->name('bot-settings');
        Route::get('/single','Bot\SettingController@edit')->name('bot-settings-edit');
        Route::post('/update','Bot\SettingController@update')->name('bot-settings-update');

    });

    Route::group(['prefix' => 'user'],function (){
        Route::get('/','Bot\UserController@index')->name('bot-user-list');
        Route::post('/change-user-wallet','Bot\UserController@SubmitUserChange')->name('bot-user-charge');
        Route::get('/{id}','Bot\UserController@single')->name('bot-user-single');
    });

    Route::group(['prefix' => 'order'],function (){
        Route::get('/','Bot\OrderController@index')->name('bot-order-list');
        Route::post('/change-status','Bot\OrderController@changeOrderStatus')->name('bot-order-change-status');
        Route::post('/change-ip-status','Bot\OrderController@changeIpStatus')->name('bot-order-change-ip');
        Route::post('/change-server','Bot\OrderController@changeServer')->name('bot-server-ip');
        Route::get('/{id}','Bot\OrderController@single')->name('bot-order-single');
    });

    Route::group(['prefix' => 'payment'], function () {
        Route::get('/', 'Bot\PaymentController@Transactions')->name('bot-payment-index');
        Route::get('single', 'Bot\PaymentController@single')->name('bot-payment-single');
    });

    Route::group(['prefix' => 'server'], function () {
        Route::get('/', 'Bot\ServerController@index')->name('bot-server-index');
        Route::get('create', 'Bot\ServerController@create')->name('bot-server-create');
        Route::post('store', 'Bot\ServerController@store')->name('bot-server-store');
        Route::get('edit/{id}', 'Bot\ServerController@edit')->name('bot-server-edit');
        Route::post('update', 'Bot\ServerController@update')->name('bot-server-update');
    });

    Route::group(['prefix' => 'message'], function () {
        Route::get('/','Bot\MessageController@index')->name('bot-message');
        Route::get('/create','Bot\MessageController@create')->name('bot-message-order');
        Route::post('/store','Bot\MessageController@store')->name('bot-message-store');
    });

});


/* Disabled: app/Http/Controllers/Arayesh/*Controller.php are empty files, so none of these routes can run.
Route::group(['prefix' => 'arayeshghar'],function (){

    Route::group(['prefix' => 'service'],function (){
       Route::get('/','Arayesh\ServiceController@index');
       Route::post('/store','Arayesh\ServiceController@store');
       Route::get('/edit','Arayesh\ServiceController@edit');
       Route::post('/update','Arayesh\ServiceController@update');
       Route::get('/delete','Arayesh\ServiceController@delete');
    });

    
    Route::group(['prefix' => 'artist'],function (){
    Route::get('/','Arayesh/ArtistController@index');
    Route::post('/store','Arayesh/ArtistController@store');
    Route::get('/edit','Arayesh/ArtistController@edit');
    Route::post('/update','Arayesh/ArtistController@update');
    Route::get('/delete','Arayesh/ArtistController@delete');
    });

    
    Route::group(['prefix' => 'time'],function (){
    Route::get('/','Arayesh/TimeController@index');
    Route::post('/store','Arayesh/TimeController@store');
    Route::get('/edit','Arayesh/TimeController@edit');
    Route::post('/update','Arayesh/TimeController@update');
    Route::get('/delete','Arayesh/TimeController@delete');
    });


});
*/


/* آی پی ثابت ها*/
// The bot's old screens: super-admins only, like the menu that links to them.
Route::group(['prefix' => 'hegza', 'middleware' => 'role:super-admin,admin'], function () {

    Route::group(['prefix' => 'setting'], function () {

        Route::get('/','Admin\BotSettingController@index')->name('bot-setting');
        Route::post('/update','Admin\BotSettingController@update')->name('bot-setting-update');
        Route::get('/{id}','Admin\BotSettingController@edit')->name('bot-setting-edit');

    });


    Route::group(['prefix' => 'ip'], function () {
        Route::get('/{type}', 'Admin\BotIpController@index')->name('hegza-ip-list')->middleware('role_or_permission:super-admin|ip-sabet-list');
        Route::get('/create/{type}', 'Admin\BotIpController@create')->name('hegza-ip-create')->middleware('role_or_permission:super-admin|create-edit-ip-sabet');
        Route::post('/store/{type}/form', 'Admin\BotIpController@store')->name('hegza-ip-store')->middleware('role_or_permission:super-admin|create-edit-ip-sabet');
        Route::get('/edit/{id}', 'Admin\BotIpController@edit')->name('hegza-ip-edit')->middleware('role_or_permission:super-admin|create-edit-ip-sabet');
        Route::post('/update/ip-sabet/update', 'Admin\BotIpController@update')->name('hegza-ip-update')->middleware('role_or_permission:super-admin|create-edit-ip-sabet');
    });

    Route::group(['prefix' => 'user'], function () {

        Route::get('/','Admin\BotUserController@index')->name('bot-users');
        Route::get('/{id}','Admin\BotUserController@singleUser')->name('bot-single-user');
        Route::get('edit/{id}','Admin\BotUserController@editUser')->name('bot-edit-user');
        Route::post('edit/submit','Admin\BotUserController@SubmitUserChange')->name('bot-submit-user');

    });

    Route::group(['prefix' => 'order'], function () {

        Route::get('/','Admin\BotOrderController@index')->name('bot-orders');
        Route::post('/changeOrderStatus','Admin\BotOrderController@changeOrderStatus')->name('bot-orders-change-status');
        Route::get('/{id}','Admin\BotOrderController@single')->name('bot-orders-single');

    });

    Route::group(['prefix' => 'message'], function () {
        Route::get('/','Admin\BotMessageController@index')->name('bot-messages');
        Route::get('/create','Admin\BotMessageController@create')->name('bot-messages-order');
        Route::post('/store','Admin\BotMessageController@store')->name('bot-messages-store');
        Route::get('/edit/{id}','Admin\BotMessageController@edit')->name('bot-messages-edit');
        Route::post('/update','Admin\BotMessageController@update')->name('bot-messages-update');
    });
});

Route::get('/home', 'Admin\PanelController@index')->name('home');

/* همکاران */
Route::group(['prefix' => 'partner'], function () {
    Route::get('/', 'Admin\PartnerController@index')->name('partner list')->middleware('role_or_permission:super-admin|partner-list');
    Route::get('create', 'Admin\PartnerController@create')->name('create partner')->middleware('role_or_permission:super-admin|partner-list');
    Route::post('store', 'Admin\PartnerController@store')->name('store partner')->middleware('role_or_permission:super-admin|partner-list');
    Route::get('edit/{id}', 'Admin\PartnerController@edit')->name('edit partner')->middleware('role_or_permission:super-admin|partner-list');
    Route::post('update', 'Admin\PartnerController@update')->name('update partner')->middleware('role_or_permission:super-admin|partner-list');
    Route::get('delete/{id}', 'Admin\PartnerController@delete')->name('delete partner')->middleware('role_or_permission:super-admin|partner-list');
});

/* اکانت های خارجی */
Route::group(['prefix' => 'accounts'], function () {
    Route::get('/', 'Admin\AccountsController@index')->name('accounts list')->middleware('role_or_permission:super-admin|accounts-list');
    Route::get('create/', 'Admin\AccountsController@create')->name('create accounts')->middleware('role_or_permission:super-admin|accounts-create-edit');
    Route::post('store', 'Admin\AccountsController@store')->name('store accounts')->middleware('role_or_permission:super-admin|accounts-create-edit');
    Route::get('edit/{id}', 'Admin\AccountsController@edit')->name('edit accounts')->middleware('role_or_permission:super-admin|accounts-create-edit');
    Route::post('update', 'Admin\AccountsController@update')->name('update accounts')->middleware('role_or_permission:super-admin|accounts-create-edit');
    Route::get('delete/{id}', 'Admin\AccountsController@delete')->name('delete accounts')->middleware('role_or_permission:super-admin|accounts-create-edit');
});

/* صفحات */
Route::group(['prefix' => 'page'], function () {
    Route::get('/', 'Admin\PagesController@index')->name('page list')->middleware('role_or_permission:super-admin|page-list');
    Route::get('edit/{id}', 'Admin\PagesController@edit')->name('edit page')->middleware('role_or_permission:super-admin|edit-page');
    Route::post('update', 'Admin\PagesController@update')->name('update update')->middleware('role_or_permission:super-admin|edit-page');
});

/* بلاگ ها*/
Route::group(['prefix' => 'news'], function () {
    Route::get('/', 'Admin\NewController@index')->name('news list')->middleware('role_or_permission:super-admin|news-list');
    Route::get('create/', 'Admin\NewController@create')->name('create news')->middleware('role_or_permission:super-admin|create-edit-news');
    Route::post('store', 'Admin\NewController@store')->name('store news')->middleware('role_or_permission:super-admin|create-edit-news');
    Route::get('edit/{id}', 'Admin\NewController@edit')->name('edit news')->middleware('role_or_permission:super-admin|create-edit-news');
    Route::post('update', 'Admin\NewController@update')->name('update news')->middleware('role_or_permission:super-admin|create-edit-news');
    Route::get('delete/{id}', 'Admin\NewController@delete')->name('delete news')->middleware('role_or_permission:super-admin|create-edit-news');
});

/* مدیر ها */
Route::group(['prefix' => 'admins'], function () {
    Route::get('/', 'Admin\AdminController@index')->name('admin list')->middleware('role_or_permission:super-admin|');
    Route::get('create/', 'Admin\AdminController@create')->name('create admin')->middleware('role_or_permission:super-admin');
    Route::post('store', 'Admin\AdminController@store')->name('store admin')->middleware('role_or_permission:super-admin');
    Route::get('edit/{id}', 'Admin\AdminController@edit')->name('edit admin')->middleware('role_or_permission:super-admin');
    Route::post('update', 'Admin\AdminController@update')->name('update admin')->middleware('role_or_permission:super-admin');
    Route::get('delete/{id}', 'Admin\AdminController@delete')->name('delete admin')->middleware('role_or_permission:super-admin');

    Route::get('change-role', 'Admin\AdminController@changeRole')->name('change role')->middleware('role_or_permission:super-admin');

});

/* دسترسی ها */
Route::group(['prefix' => 'permission'], function () {
    Route::get('/', 'Admin\PermissionController@index')->name('permission list')->middleware('role_or_permission:super-admin');
    Route::get('create/', 'Admin\PermissionController@create')->name('create permission')->middleware('role_or_permission:super-admin');
    Route::post('store', 'Admin\PermissionController@store')->name('store permission')->middleware('role_or_permission:super-admin');
    Route::get('edit', 'Admin\PermissionController@edit')->name('edit permission')->middleware('role_or_permission:super-admin');
    Route::post('update', 'Admin\PermissionController@update')->name('update permission')->middleware('role_or_permission:super-admin');
    Route::get('delete/{id}', 'Admin\PermissionController@delete')->name('delete permission')->middleware('role_or_permission:super-admin');
});

/* دسترسی ها */
Route::group(['prefix' => 'role'], function () {
    Route::get('/', 'Admin\RoleController@index')->name('role list')->middleware('role_or_permission:super-admin');
    Route::get('create/', 'Admin\RoleController@create')->name('create role')->middleware('role_or_permission:super-admin');
    Route::post('store', 'Admin\RoleController@store')->name('store role')->middleware('role_or_permission:super-admin');
    Route::get('edit/{id}', 'Admin\RoleController@edit')->name('edit role')->middleware('role_or_permission:super-admin');
    Route::post('update', 'Admin\RoleController@update')->name('update role')->middleware('role_or_permission:super-admin');
    Route::get('delete/{id}', 'Admin\RoleController@delete')->name('delete role')->middleware('role_or_permission:super-admin');
});

/* بلاگ ها*/
Route::group(['prefix' => 'blog'], function () {
    Route::get('/', 'Admin\BlogController@index')->name('blog list')->middleware('role_or_permission:super-admin|blog-list');
    Route::get('create/', 'Admin\BlogController@create')->name('create blog')->middleware('role_or_permission:super-admin|create-edit-blog');
    Route::post('store', 'Admin\BlogController@store')->name('store blog')->middleware('role_or_permission:super-admin|create-edit-blog');
    Route::get('edit/{id}', 'Admin\BlogController@edit')->name('edit blog')->middleware('role_or_permission:super-admin|create-edit-blog');
    Route::post('update', 'Admin\BlogController@update')->name('update blog')->middleware('role_or_permission:super-admin|create-edit-blog');
    Route::get('delete/{id}', 'Admin\BlogController@delete')->name('delete blog')->middleware('role_or_permission:super-admin|create-edit-blog');
});

/* دسته بندی ها */
Route::group(['prefix' => 'category'], function () {
    Route::get('/{type}', 'Admin\CategoryController@index')->name('category list')->middleware('role_or_permission:super-admin|category-list');
    Route::get('/create/{type}', 'Admin\CategoryController@create')->name('create category')->middleware('role_or_permission:super-admin|create-edit-category');
    Route::post('/store', 'Admin\CategoryController@store')->name('store category')->middleware('role_or_permission:super-admin|create-edit-category');
    Route::get('/edit/{id}', 'Admin\CategoryController@edit')->name('edit category')->middleware('role_or_permission:super-admin|create-edit-category');
    Route::post('/update', 'Admin\CategoryController@update')->name('update category')->middleware('role_or_permission:super-admin|create-edit-category');
    Route::post('/delete', 'Admin\CategoryController@delete')->name('delete category')->middleware('role_or_permission:super-admin|create-edit-category');
});

/* کامنت ها */
Route::group(['prefix' => 'comment'], function () {
    Route::get('/', 'Admin\CommentController@Comments')->name('comment-index')->middleware('role_or_permission:super-admin|comments-list');
    Route::get('/status', 'Admin\CommentController@ChangeCommentStatus')->name('change-comment-status')->middleware('role_or_permission:super-admin|accept-comment');
    Route::get('/comment-data', 'Admin\CommentController@comment_data')->name('comment-data')->middleware('role_or_permission:super-admin|accept-comment');
    Route::post('/submit-comment-answer', 'Admin\CommentController@AnswerComment')->name('submit-comment-answer')->middleware('role_or_permission:super-admin|accept-comment');
});

/* اپل آیدی */
Route::group(['prefix' => 'google-voice'], function () {
    Route::get('/', 'Admin\GoogleVoiceController@index')->name('google-voice-list')->middleware('role_or_permission:super-admin|google-voice-list');
    Route::get('/create', 'Admin\GoogleVoiceController@create')->name('google-voice-create')->middleware('role_or_permission:super-admin|google-voice-orders');
    Route::post('/submit', 'Admin\GoogleVoiceController@submit')->name('google-voice-submit')->middleware('role_or_permission:super-admin|google-voice-orders');
});

/* آی پی ثابت ها*/
Route::group(['prefix' => 'ip-sabet'], function () {
    Route::get('/setting', 'Admin\IpSabetController@Setting')->name('ip-sabet-setting')->middleware('role_or_permission:super-admin|ip-sabet-list');
    Route::post('/setting/update', 'Admin\IpSabetController@submit')->name('ip-sabet-setting-update')->middleware('role_or_permission:super-admin|create-edit-ip-sabet');

    Route::get('/{type}', 'Admin\IpSabetController@index')->name('ip-sabet-list')->middleware('role_or_permission:super-admin|ip-sabet-list');
    Route::get('/create/{type}', 'Admin\IpSabetController@create')->name('ip-sabet-create')->middleware('role_or_permission:super-admin|create-edit-ip-sabet');
    Route::post('/store/{type}/form', 'Admin\IpSabetController@store')->name('ip-sabet-store')->middleware('role_or_permission:super-admin|create-edit-ip-sabet');
    Route::get('/edit/{id}', 'Admin\IpSabetController@edit')->name('ip-sabet-edit')->middleware('role_or_permission:super-admin|create-edit-ip-sabet');
    Route::post('/update/ip-sabet/update', 'Admin\IpSabetController@update')->name('ip-sabet-update')->middleware('role_or_permission:super-admin|create-edit-ip-sabet');
});

/* شماره های مجازی */
Route::group(['prefix' => 'virtual-number'], function () {

    /*Country Routes*/
    Route::get('countries', 'Admin\VirtualCountryController@CountriesList')->name('Virtual-Countries')->middleware('role_or_permission:super-admin|');
    Route::get('countries/create', 'Admin\VirtualCountryController@create')->name('Create-Virtual-Countries')->middleware('role_or_permission:super-admin|');
    Route::post('countries', 'Admin\VirtualCountryController@StoreCreate')->name('store-Virtual-Countries')->middleware('role_or_permission:super-admin|');
    Route::get('edit/{id}', 'Admin\VirtualCountryController@Single')->name('Single-Virtual-Countries')->middleware('role_or_permission:super-admin|');
    Route::post('update', 'Admin\VirtualCountryController@update')->name('update-Virtual-Countries')->middleware('role_or_permission:super-admin|');
    Route::get('FastUpdate', 'Admin\VirtualCountryController@FastUpdate')->name('FastUpdate')->middleware('role_or_permission:super-admin|');
    Route::get('GenerateCountry/{id}', 'Admin\VirtualCountryController@GenerateProduct')->name('GenerateProduct');

    /* Service List */
    Route::get('service', 'Admin\VirtualServiceController@CountriesList')->name('Virtual-Services')->middleware('role_or_permission:super-admin|');
    Route::get('create-service', 'Admin\VirtualServiceController@Create')->name('create-Virtual-Services')->middleware('role_or_permission:super-admin|');
    Route::post('store-service', 'Admin\VirtualServiceController@store')->name('store-Virtual-Services')->middleware('role_or_permission:super-admin|');
    Route::get('service/{id}', 'Admin\VirtualServiceController@Edit')->name('edit-Virtual-Services')->middleware('role_or_permission:super-admin|');
    Route::post('service/', 'Admin\VirtualServiceController@update')->name('Single-Virtual-Services')->middleware('role_or_permission:super-admin|');
    Route::get('GenerateService/{id}', 'Admin\VirtualServiceController@GenerateProduct')->name('GenerateService');

    /* Products */
    Route::get('products', 'Admin\ProductController@index')->name('Virtual-Products')->middleware('role_or_permission:super-admin|');
    Route::get('create-products', 'Admin\ProductController@Create')->name('create-Virtual-Products')->middleware('role_or_permission:super-admin|');
    Route::post('store-products', 'Admin\ProductController@store')->name('store-Virtual-Products')->middleware('role_or_permission:super-admin|');
    Route::get('products/{id}', 'Admin\ProductController@Edit')->name('edit-Virtual-Products')->middleware('role_or_permission:super-admin|');
    Route::post('products/', 'Admin\ProductController@update')->name('Single-Virtual-Products')->middleware('role_or_permission:super-admin|');

    /* Setting */
    Route::get('/setting', 'Admin\SettingController@VirtualNumber')->name('virtualNumber-setting-page')->middleware('role_or_permission:super-admin|');
    Route::post('/setting', 'Admin\SettingController@SubmitVirtualNumberSetting')->name('virtualNumber-setting-submit')->middleware('role_or_permission:super-admin|');

});

/* سرویس های مجازی */
Route::group(['prefix' => 'virtual-service'], function () {
    Route::get('/', 'Admin\ServiceController@index')->name('service-list')->middleware('role_or_permission:super-admin|');
    Route::get('/create', 'Admin\ServiceController@create')->name('service-create')->middleware('role_or_permission:super-admin|');
    Route::get('edit/{id}', 'Admin\ServiceController@edit')->name('service-edit')->middleware('role_or_permission:super-admin|');
    Route::post('/store', 'Admin\ServiceController@store')->name('service-store')->middleware('role_or_permission:super-admin|');
    Route::post('/update', 'Admin\ServiceController@update')->name('service-update')->middleware('role_or_permission:super-admin|');

    /* Setting */
    Route::get('/setting', 'Admin\SettingController@VirtualService')->name('virtualService-setting-page')->middleware('role_or_permission:super-admin|');
    Route::post('/setting', 'Admin\SettingController@VirtualServiceSubmit')->name('virtual-service-setting-submit')->middleware('role_or_permission:super-admin|');
});

/* اپل آیدی */
Route::group(['prefix' => 'apple-id'], function () {
    Route::get('/', 'Admin\AppleIdController@index')->name('apple-id-list')->middleware('role_or_permission:super-admin|');
    Route::get('/create', 'Admin\AppleIdController@create')->name('apple-id-create')->middleware('role_or_permission:super-admin|');
    Route::post('/submit', 'Admin\AppleIdController@submit')->name('apple-id-submit')->middleware('role_or_permission:super-admin|');
    Route::post('/submit_custom_apple_id', 'Admin\AppleIdController@submit_custom_apple_id')->name('submit-custom-apple-id')->middleware('role_or_permission:super-admin|');
});

/* تیکت ها */
Route::group(['prefix' => 'tickets'], function () {
    Route::get('/', 'Admin\TicketController@index')->name('tickets list')->middleware('role_or_permission:super-admin|');
    Route::get('/{id}', 'Admin\TicketController@single')->name('ticket single')->middleware('role_or_permission:super-admin|');
    Route::post('submit/ticket', 'Admin\TicketController@SendTicket')->name('send ticket')->middleware('role_or_permission:super-admin|');
});

/* کاربران */
Route::group(['prefix' => 'users'], function () {
    Route::get('/', 'Admin\UserController@index')->name('user list');
    Route::get('/{id}', 'Admin\UserController@single')->name('user single');
    Route::post('edit/submio', 'Admin\UserController@SubmitUserChange')->name('user submit');
    Route::get('edit/{id}', 'Admin\UserController@editUser')->name('user edit');
    Route::get('panel/{id}', 'Admin\UserController@UserPanel')->name('user panel');
});

/* پرداختی ها */
Route::group(['prefix' => 'payment'], function () {
    Route::get('/transaction', 'Admin\PaymentController@Transactions')->name('transaction list');
    Route::get('/bank-payments', 'Admin\PaymentController@BankPayment')->name('bank payments list');
    Route::get('/contradiction', 'Admin\PaymentController@Contradiction')->name('contradiction payments list');
    Route::get('single', 'Admin\PaymentController@Single')->name('Single-Payment');
});

/* سفارشات */
Route::group(['prefix' => 'orders'], function () {
    Route::get('/', 'Admin\OrderController@index')->name('order list');
    Route::post('submit-apple-id', 'Admin\OrderController@completeCustomAppleIdOrder')->name('submit-apple-id');
    Route::post('single/change-status', 'Admin\OrderController@ChangeOrderStatus')->name('change order status');
    Route::post('charge-wallet', 'Admin\OrderController@ChangeStatusWallet')->name('return back order money to user');
    Route::post('order-chat', 'Admin\OrderController@SendOrderChat')->name('send order chat');
    Route::get('/{id}', 'Admin\OrderController@single')->name('order single');


});


Route::group(['prefix' => 'v2'], function () {
    Route::group(['prefix' => 'product'], function () {
        Route::get('/', 'Admin\ProductController@services')->name('product list');
        Route::get('/create/', 'Admin\ProductController@create')->name('create product');
        Route::post('/store', 'Admin\ProductController@store')->name('store product');
        Route::get('/edit/{id}', 'Admin\ProductController@edit')->name('edit product');
        Route::post('/update', 'Admin\ProductController@update')->name('update product');
        Route::get('/delete/{id}', 'Admin\ProductController@single')->name('delete product');
    });
//Category

// Virtual Numbers
    Route::group(['prefix' => 'virtual'], function () {

        /* Products */
        Route::get('products', 'Admin\ProductController@index')->name('Virtual-Products');
        Route::get('create-products', 'Admin\ProductController@Create')->name('create-Virtual-Products');
        Route::post('store-products', 'Admin\ProductController@store')->name('store-Virtual-Products');
        Route::get('products/{id}', 'Admin\ProductController@Edit')->name('edit-Virtual-Products');
        Route::post('products/', 'Admin\ProductController@update')->name('Single-Virtual-Products');

    });

// Orders


});

/* پنل جدید React (Vite) — JSON reads for the React screens, then the app itself (React handles every path below /admin/app) */
Route::group(['prefix' => 'api', 'as' => 'api.'], function () {
    Route::get('dashboard', 'Admin\DashboardApiController')->name('dashboard');
    Route::get('orders', 'Admin\OrderApiController@index')->name('orders');
    Route::get('orders/{id}', 'Admin\OrderApiController@show')->whereNumber('id')->name('order');
    Route::get('users', 'Admin\UserApiController@index')->name('users');
    Route::get('users/{id}', 'Admin\UserApiController@show')->whereNumber('id')->name('user');
    Route::middleware('role_or_permission:super-admin|transaction-list|bank-transaction|moghayerat')->group(function () {
        Route::get('transactions', 'Admin\TransactionApiController@index')->name('transactions');
        Route::get('transactions/{id}', 'Admin\TransactionApiController@show')->whereNumber('id')->name('transaction');
    });
    Route::post('transactions/{id}/resolve', 'Admin\TransactionApiController@resolve')->whereNumber('id')
        ->middleware('role_or_permission:super-admin|moghayerat')->name('transaction-resolve');
    Route::middleware('role_or_permission:super-admin|create-edit-virtual-service')->group(function () {
        Route::get('service-categories', 'Admin\ServiceApiController@categories')->name('service-categories');
        Route::post('service-categories', 'Admin\ServiceApiController@saveCategory');
        Route::post('service-categories/{id}', 'Admin\ServiceApiController@saveCategory')->whereNumber('id');
        Route::get('services', 'Admin\ServiceApiController@services')->name('services');
        Route::post('services', 'Admin\ServiceApiController@saveService');
        Route::get('services/{id}', 'Admin\ServiceApiController@service')->whereNumber('id');
        Route::post('services/{id}', 'Admin\ServiceApiController@saveService')->whereNumber('id');
        Route::post('uploads/service-image', 'Admin\ServiceApiController@upload');
    });
    Route::group(['prefix' => 'virtual-numbers', 'as' => 'virtual-numbers.'], function () {
        Route::middleware('role_or_permission:super-admin|virtual-number-services')->group(function () {
            Route::get('services', 'Admin\VirtualNumberApiController@services')->name('services');
            Route::post('services', 'Admin\VirtualNumberApiController@saveService');
            Route::get('services/{id}', 'Admin\VirtualNumberApiController@service')->whereNumber('id');
            Route::post('services/{id}', 'Admin\VirtualNumberApiController@saveService')->whereNumber('id');
            Route::post('services/{id}/codes', 'Admin\VirtualNumberApiController@serviceCodes')->whereNumber('id');
            Route::post('services/{id}/rebuild', 'Admin\VirtualNumberApiController@rebuildService')->whereNumber('id');
        });
        Route::middleware('role_or_permission:super-admin|virtual-number-countries')->group(function () {
            Route::get('countries', 'Admin\VirtualNumberApiController@countries')->name('countries');
            Route::post('countries', 'Admin\VirtualNumberApiController@saveCountry');
            Route::get('countries/{id}', 'Admin\VirtualNumberApiController@country')->whereNumber('id');
            Route::post('countries/{id}', 'Admin\VirtualNumberApiController@saveCountry')->whereNumber('id');
            Route::post('countries/{id}/codes', 'Admin\VirtualNumberApiController@countryCodes')->whereNumber('id');
            Route::post('countries/{id}/rebuild', 'Admin\VirtualNumberApiController@rebuildCountry')->whereNumber('id');
        });
        Route::middleware('role_or_permission:super-admin|virtual-number-services|virtual-number-countries')->group(function () {
            Route::get('offers', 'Admin\VirtualNumberApiController@offers')->name('offers');
        });
        Route::middleware('role_or_permission:super-admin|virtual-number-services|virtual-number-countries|virtual-number-setting')->group(function () {
            Route::post('uploads/image', 'Admin\VirtualNumberApiController@upload');
        });
        Route::middleware('role_or_permission:super-admin|virtual-number-setting')->group(function () {
            Route::get('settings', 'Admin\VirtualNumberApiController@settings')->name('settings');
            Route::post('settings', 'Admin\VirtualNumberApiController@saveSettings');
        });
    });
    Route::get('seo-pages', 'Admin\SeoApiController@index')->name('seo-pages')->middleware('role_or_permission:super-admin|page-list|edit-page');
    Route::middleware('role_or_permission:super-admin|edit-page')->group(function () {
        Route::post('seo-pages/{key}', 'Admin\SeoApiController@save')->where('key', '[a-z-]+');
        Route::post('uploads/seo-image', 'Admin\SeoApiController@upload');
    });

    /* Blog and tech news: one controller, the type comes from the route. */
    foreach (['blog' => ['blog-list', 'create-edit-blog'], 'news' => ['news-list', 'create-edit-news']] as $postType => [$canRead, $canWrite]) {
        Route::group(['prefix' => 'posts/' . $postType], function () use ($postType, $canRead, $canWrite) {
            Route::get('/', 'Admin\PostApiController@index')->defaults('type', $postType)->name('posts.' . $postType)
                ->middleware("role_or_permission:super-admin|$canRead|$canWrite");
            Route::get('{id}', 'Admin\PostApiController@show')->whereNumber('id')->defaults('type', $postType)
                ->middleware("role_or_permission:super-admin|$canRead|$canWrite");
            Route::middleware("role_or_permission:super-admin|$canWrite")->group(function () use ($postType) {
                Route::post('/', 'Admin\PostApiController@save')->defaults('type', $postType);
                Route::post('{id}', 'Admin\PostApiController@save')->whereNumber('id')->defaults('type', $postType);
                Route::post('{id}/delete', 'Admin\PostApiController@delete')->whereNumber('id')->defaults('type', $postType);
            });
        });
    }
    Route::post('uploads/post-image', 'Admin\PostApiController@upload')->middleware('role_or_permission:super-admin|create-edit-blog|create-edit-news');

    /* Service landing pages (texts, FAQ, head tags, Apple ID / Google Voice prices). */
    Route::middleware('role_or_permission:super-admin|page-list|edit-page')->group(function () {
        Route::get('pages', 'Admin\PageApiController@index')->name('pages');
        Route::get('pages/{id}', 'Admin\PageApiController@show')->whereNumber('id');
    });
    Route::middleware('role_or_permission:super-admin|edit-page')->group(function () {
        Route::post('pages/{id}', 'Admin\PageApiController@save')->whereNumber('id');
        Route::post('uploads/page-image', 'Admin\PageApiController@upload');
    });

    Route::get('comments', 'Admin\CommentApiController@index')->name('comments')->middleware('role_or_permission:super-admin|comments-list|accept-comment');
    Route::middleware('role_or_permission:super-admin|accept-comment')->group(function () {
        Route::post('comments/bulk', 'Admin\CommentApiController@bulk');
        Route::post('comments/{id}/status', 'Admin\CommentApiController@setStatus')->whereNumber('id');
        Route::post('comments/{id}/reply', 'Admin\CommentApiController@reply')->whereNumber('id');
    });

    Route::middleware('role_or_permission:super-admin|tickets-list')->group(function () {
        Route::get('tickets', 'Admin\TicketApiController@index')->name('tickets');
        Route::get('tickets/{id}', 'Admin\TicketApiController@show')->whereNumber('id');
        Route::post('tickets/{id}/reply', 'Admin\TicketApiController@reply')->whereNumber('id');
        Route::post('tickets/{id}/status', 'Admin\TicketApiController@setStatus')->whereNumber('id');
    });

    Route::middleware('role_or_permission:super-admin|add-apple-id')->group(function () {
        Route::get('apple-ids', 'Admin\AppleIdApiController@index')->name('apple-ids');
        Route::post('apple-ids', 'Admin\AppleIdApiController@store');
        Route::get('apple-ids/{id}', 'Admin\AppleIdApiController@show')->whereNumber('id');
        Route::post('apple-ids/{id}', 'Admin\AppleIdApiController@update')->whereNumber('id');
        Route::post('apple-ids/{id}/delete', 'Admin\AppleIdApiController@delete')->whereNumber('id');
    });

    Route::middleware('role_or_permission:super-admin|google-voice-list')->group(function () {
        Route::get('google-voices', 'Admin\GoogleVoiceApiController@index')->name('google-voices');
        Route::post('google-voices', 'Admin\GoogleVoiceApiController@store');
        Route::get('google-voices/{id}', 'Admin\GoogleVoiceApiController@show')->whereNumber('id');
        Route::post('google-voices/{id}', 'Admin\GoogleVoiceApiController@update')->whereNumber('id');
        Route::post('google-voices/{id}/delete', 'Admin\GoogleVoiceApiController@delete')->whereNumber('id');
    });

    Route::middleware('role_or_permission:super-admin|accounts-list|accounts-create-edit')->group(function () {
        Route::get('accounts', 'Admin\AccountApiController@index')->name('accounts');
        Route::get('accounts/{id}', 'Admin\AccountApiController@show')->whereNumber('id');
    });
    Route::middleware('role_or_permission:super-admin|accounts-create-edit')->group(function () {
        Route::post('accounts', 'Admin\AccountApiController@save');
        Route::post('accounts/{id}', 'Admin\AccountApiController@save')->whereNumber('id');
        Route::post('uploads/account-image', 'Admin\AccountApiController@upload');
    });

    Route::group(['prefix' => 'ip', 'as' => 'ip.'], function () {
        Route::middleware('role_or_permission:super-admin|ip-sabet-list|create-edit-ip-sabet')->group(function () {
            Route::get('locations', 'Admin\IpApiController@index')->name('locations');
            Route::get('locations/{id}', 'Admin\IpApiController@show')->whereNumber('id');
            Route::get('locations/{id}/clients', 'Admin\IpApiController@clients')->whereNumber('id');
        });
        Route::middleware('role_or_permission:super-admin|create-edit-ip-sabet')->group(function () {
            Route::post('locations', 'Admin\IpApiController@save');
            Route::post('locations/{id}', 'Admin\IpApiController@save')->whereNumber('id');
            Route::post('locations/{locationId}/servers', 'Admin\IpApiController@saveServer')->whereNumber('locationId');
            Route::post('locations/{locationId}/servers/{id}', 'Admin\IpApiController@saveServer')->whereNumber(['locationId', 'id']);
            Route::post('servers/{id}/clients', 'Admin\IpApiController@addClients')->whereNumber('id');
            Route::post('clients/{id}/delete', 'Admin\IpApiController@deleteClient')->whereNumber('id');
            Route::post('uploads/image', 'Admin\IpApiController@upload');
        });
    });

    /* Management: roles, admins and the Telegram bot are for super-admins only; partners follow partner-list. */
    Route::middleware('role:super-admin,admin')->group(function () {
        Route::get('roles', 'Admin\RoleApiController@index')->name('roles');
        Route::get('roles/{id}', 'Admin\RoleApiController@show')->whereNumber('id');
        Route::get('permissions', 'Admin\RoleApiController@permissions')->name('permissions');
        Route::post('roles', 'Admin\RoleApiController@save');
        Route::post('roles/{id}', 'Admin\RoleApiController@save')->whereNumber('id');
        Route::post('roles/{id}/delete', 'Admin\RoleApiController@delete')->whereNumber('id');

        Route::get('admins', 'Admin\AdminUserApiController@index')->name('admins');
        Route::get('admins/options', 'Admin\AdminUserApiController@options')->name('admins.options');
        Route::get('admins/{id}', 'Admin\AdminUserApiController@show')->whereNumber('id');
        Route::post('admins', 'Admin\AdminUserApiController@save');
        Route::post('admins/{id}', 'Admin\AdminUserApiController@save')->whereNumber('id');
        Route::post('admins/{id}/delete', 'Admin\AdminUserApiController@delete')->whereNumber('id');

        Route::group(['prefix' => 'bot', 'as' => 'bot.'], function () {
            Route::get('users', 'Admin\BotUserApiController@index')->name('users');
            Route::get('users/{id}', 'Admin\BotUserApiController@show')->whereNumber('id');
            Route::post('users/{id}/wallet', 'Admin\BotUserApiController@wallet')->whereNumber('id');

            Route::get('orders', 'Admin\BotOrderApiController@index')->name('orders');
            Route::get('orders/{id}', 'Admin\BotOrderApiController@show')->whereNumber('id');
            Route::post('orders/{id}/status', 'Admin\BotOrderApiController@setStatus')->whereNumber('id');

            Route::get('messages', 'Admin\BotMessageApiController@index')->name('messages');
            Route::get('messages/{id}', 'Admin\BotMessageApiController@show')->whereNumber('id');
            Route::post('messages', 'Admin\BotMessageApiController@save');
            Route::post('messages/{id}', 'Admin\BotMessageApiController@save')->whereNumber('id');
            Route::post('messages/{id}/delete', 'Admin\BotMessageApiController@delete')->whereNumber('id');

            Route::group(['prefix' => 'ip', 'as' => 'ip.'], function () {
                Route::get('locations', 'Admin\BotIpApiController@index')->name('locations');
                Route::get('locations/{id}', 'Admin\BotIpApiController@show')->whereNumber('id');
                Route::get('locations/{id}/clients', 'Admin\BotIpApiController@clients')->whereNumber('id');
                Route::post('locations', 'Admin\BotIpApiController@save');
                Route::post('locations/{id}', 'Admin\BotIpApiController@save')->whereNumber('id');
                Route::post('locations/{locationId}/servers', 'Admin\BotIpApiController@saveServer')->whereNumber('locationId');
                Route::post('locations/{locationId}/servers/{id}', 'Admin\BotIpApiController@saveServer')->whereNumber(['locationId', 'id']);
                Route::post('servers/{id}/clients', 'Admin\BotIpApiController@addClients')->whereNumber('id');
                Route::post('clients/{id}/delete', 'Admin\BotIpApiController@deleteClient')->whereNumber('id');
                Route::post('uploads/image', 'Admin\BotIpApiController@upload');
            });
        });
    });

    Route::middleware('role_or_permission:super-admin|partner-list')->group(function () {
        Route::get('partners', 'Admin\PartnerApiController@index')->name('partners');
        Route::get('partners/{id}', 'Admin\PartnerApiController@show')->whereNumber('id');
        Route::post('partners', 'Admin\PartnerApiController@save');
        Route::post('partners/{id}', 'Admin\PartnerApiController@save')->whereNumber('id');
        Route::post('partners/{id}/settle', 'Admin\PartnerApiController@settle')->whereNumber('id');
        Route::post('partners/{id}/delete', 'Admin\PartnerApiController@delete')->whereNumber('id');
    });
});
/* CKFinder media library, only for signed-in admins (config/ckfinder.php, App\Http\Middleware\CKFinderAccess).
   Laravel's CSRF check skips these; CKFinder sends its own token (VerifyCsrfToken / EncryptCookies). */
Route::any('ckfinder/connector', [\CKSource\CKFinderBridge\Controller\CKFinderController::class, 'requestAction'])->name('ckfinder_connector');
Route::any('ckfinder/browser', [\CKSource\CKFinderBridge\Controller\CKFinderController::class, 'browserAction'])->name('ckfinder_browser');

Route::get('/app/{any?}', 'Admin\ReactAppController')->where('any', '.*')->name('react-app');
