<?php
/*
 * The old Blade admin pages are gone: the React panel (/admin/app, below) covers every section.
 * What stays from them: the old page URLs as redirects (links in earlier Telegram/SMS alerts and bookmarks, and the
 * route names the alerts still build: admin.order single / user single / ticket single), and the four form posts the
 * React screens still send (ReactAppController boot › actions).
 */

Route::redirect('/home', '/admin/app')->name('home');
Route::get('orders/{id}', fn ($id) => redirect('/admin/app/orders/' . $id))->whereNumber('id')->name('order single');
Route::get('users/{id}', fn ($id) => redirect('/admin/app/users/' . $id))->whereNumber('id')->name('user single');
Route::get('tickets/{id}', fn ($id) => redirect('/admin/app/tickets/' . $id))->whereNumber('id')->name('ticket single');

Route::post('orders/single/change-status', 'Admin\OrderController@ChangeOrderStatus')->name('change order status');
Route::post('orders/charge-wallet', 'Admin\OrderController@ChangeStatusWallet')->name('return back order money to user');
Route::post('orders/order-chat', 'Admin\OrderController@SendOrderChat')->name('send order chat');
Route::post('users/edit/submio', 'Admin\UserController@SubmitUserChange')->name('user submit');
// «ورود به پنل کاربر» on admin › user: signs in as that customer and opens the storefront's /account.
Route::get('users/panel/{id}', 'Admin\UserController@UserPanel')->whereNumber('id')->name('user panel');

/* پنل جدید React (Vite) — JSON reads for the React screens, then the app itself (React handles every path below /admin/app) */
Route::group(['prefix' => 'api', 'as' => 'api.'], function () {
    Route::get('dashboard', 'Admin\DashboardApiController')->name('dashboard');
    Route::get('menu-counts', 'Admin\ReactAppController@counts')->name('menu-counts');
    Route::get('csrf', 'Admin\ReactAppController@csrf')->name('csrf'); // lib/api.ts retries a 419 with this
    Route::get('orders', 'Admin\OrderApiController@index')->name('orders');
    Route::get('orders/{id}', 'Admin\OrderApiController@show')->whereNumber('id')->name('order');
    // A file a buyer sent with a verification order (private disk; Admin\VerificationApiController::file).
    Route::get('orders/{id}/files/{field}', 'Admin\VerificationApiController@file')->whereNumber('id')->where('field', '[A-Za-z][A-Za-z0-9_]*')->name('order-file');
    // Orders support makes for a customer, and extra amounts (tax, VAT…) on an order: Admin\OrderMakeApiController.
    Route::get('orders/catalog', 'Admin\OrderMakeApiController@catalog')->name('order-catalog');
    Route::post('orders/make', 'Admin\OrderMakeApiController@store')->name('order-make');
    Route::post('orders/{id}/charges', 'Admin\OrderMakeApiController@charge')->whereNumber('id')->name('order-charge');
    Route::post('orders/charges/{id}/drop', 'Admin\OrderMakeApiController@drop')->whereNumber('id')->name('order-charge-drop');
    Route::get('users', 'Admin\UserApiController@index')->name('users');
    Route::get('users/{id}', 'Admin\UserApiController@show')->whereNumber('id')->name('user');
    // admin › user actions and custom Apple ID delivery (Admin\UserActionApiController).
    Route::post('users/{id}/status', 'Admin\UserActionApiController@status')->whereNumber('id')->name('user-status');
    Route::post('users/{id}/ticket', 'Admin\UserActionApiController@ticket')->whereNumber('id')->name('user-ticket');
    Route::post('orders/{id}/apple-id', 'Admin\UserActionApiController@completeAppleId')->whereNumber('id')->name('order-apple-id');
    // Account orders: which shop card (admin › کارت‌های خارجی) paid the outside service.
    Route::post('orders/{id}/external-card', 'Admin\OrderApiController@setExternalCard')->whereNumber('id')->name('order-external-card');
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
        Route::get('telegram-stars', 'Admin\TelegramStarsController@show')->name('telegram-stars');
        Route::post('telegram-stars', 'Admin\TelegramStarsController@save');
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
        Route::post('tickets/bulk-close', 'Admin\TicketApiController@bulkClose');
    });

    // «درخواست اکانت» (App\Services\AccountRequests): accounts-list reads, accounts-create-edit answers and edits the form.
    Route::middleware('role_or_permission:super-admin|accounts-list|accounts-create-edit')->group(function () {
        Route::get('account-requests', 'Admin\AccountRequestApiController@index')->name('account-requests');
        Route::get('account-requests/{id}', 'Admin\AccountRequestApiController@show')->whereNumber('id');
        Route::get('account-requests/{id}/files/{key}', 'Admin\AccountRequestApiController@file')->whereNumber('id')->where('key', '[a-z][a-z0-9_]*')->name('account-request-file');
        Route::get('account-requests/settings', 'Admin\AccountRequestApiController@settings');
    });
    Route::middleware('role_or_permission:super-admin|accounts-create-edit')->group(function () {
        Route::post('account-requests/{id}/quote', 'Admin\AccountRequestApiController@quote')->whereNumber('id');
        Route::post('account-requests/{id}/reject', 'Admin\AccountRequestApiController@reject')->whereNumber('id');
        Route::post('account-requests/settings', 'Admin\AccountRequestApiController@saveSettings');
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
        Route::post('orders/{id}/gv-preorder/retry', 'Admin\GoogleVoicePreorderApiController@retry')->whereNumber('id');
        Route::post('orders/{id}/gv-preorder/deliver', 'Admin\GoogleVoicePreorderApiController@deliver')->whereNumber('id');
    });

    // ارزها و سود: market rates and profit percentages (App\Services\Pricing).
    Route::middleware('role_or_permission:super-admin')->group(function () {
        Route::get('cache', 'Admin\CacheApiController@index')->name('cache');
        Route::post('cache/clear', 'Admin\CacheApiController@clear');
        Route::post('cache/times', 'Admin\CacheApiController@saveTimes');
        // کپچا: which captcha the public forms use (App\Services\Captcha).
        Route::get('captcha', 'Admin\CaptchaApiController@index')->name('captcha');
        Route::post('captcha', 'Admin\CaptchaApiController@save');
        Route::get('pricing', 'Admin\PricingApiController@index')->name('pricing');
        Route::post('pricing', 'Admin\PricingApiController@save');
        Route::post('pricing/refresh', 'Admin\PricingApiController@refresh');
        // درگاه‌های پرداخت (App\Services\Payments\GatewayManager).
        Route::get('payment-gateways', 'Admin\PaymentGatewayApiController@index')->name('payment-gateways');
        Route::post('payment-gateways', 'Admin\PaymentGatewayApiController@save');
        Route::post('payment-gateways/{key}/test', 'Admin\PaymentGatewayApiController@test')->where('key', '[a-z]+');
        // کارت‌های خارجی: the shop's own cards for paying outside services (App\Models\ExternalCard).
        Route::get('external-cards', 'Admin\ExternalCardApiController@index')->name('external-cards');
        Route::post('external-cards', 'Admin\ExternalCardApiController@save');
        Route::post('external-cards/{id}', 'Admin\ExternalCardApiController@save')->whereNumber('id');
        Route::post('external-cards/{id}/delete', 'Admin\ExternalCardApiController@delete')->whereNumber('id');
        // معرفی دوستان: on/off and the default commission per service (App\Services\Referrals).
        Route::get('referral', 'Admin\ReferralApiController@index')->name('referral');
        Route::post('referral', 'Admin\ReferralApiController@save');
        // پیامک: SMS.ir credit, automatic patterns, bulk sends (App\Services\Sms).
        Route::get('sms', 'Admin\SmsApiController@index')->name('sms');
        Route::post('sms', 'Admin\SmsApiController@save');
        Route::post('sms/patterns/{key}/test', 'Admin\SmsApiController@test')->where('key', '[a-z_]+');
        Route::post('sms/bulk/preview', 'Admin\SmsApiController@preview');
        Route::post('sms/bulk', 'Admin\SmsApiController@send');
        Route::get('sms/campaigns', 'Admin\SmsApiController@campaigns');
        Route::post('sms/campaigns/{id}/cancel', 'Admin\SmsApiController@cancel')->whereNumber('id');
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
    // وریفای: verification products (App\Services\VerificationOrders). Same staff as foreign accounts.
    Route::middleware('role_or_permission:super-admin|accounts-list|accounts-create-edit')->group(function () {
        Route::get('verification', 'Admin\VerificationApiController@index')->name('verification');
        Route::get('verification/{id}', 'Admin\VerificationApiController@show')->whereNumber('id');
    });
    Route::middleware('role_or_permission:super-admin|accounts-create-edit')->group(function () {
        Route::post('verification', 'Admin\VerificationApiController@save');
        Route::post('verification/{id}', 'Admin\VerificationApiController@save')->whereNumber('id');
        Route::post('uploads/verification-image', 'Admin\VerificationApiController@upload');
    });
    // حساب‌های سازمانی (App\Services\Organizations): requests, organisations + discount, the form. Same staff as users.
    Route::middleware('role_or_permission:super-admin|users-list|users-detail|edit-user')->group(function () {
        Route::get('organizations/requests', 'Admin\OrganizationApiController@requests')->name('organization-requests');
        Route::get('organizations/requests/{id}', 'Admin\OrganizationApiController@request')->whereNumber('id');
        Route::get('organizations/requests/{id}/files/{key}', 'Admin\OrganizationApiController@file')->whereNumber('id')->where('key', '[a-z][a-z0-9_]*')->name('organization-file');
        Route::get('organizations', 'Admin\OrganizationApiController@organizations')->name('organizations');
        Route::get('organizations/settings', 'Admin\OrganizationApiController@settings');
    });
    Route::middleware('role_or_permission:super-admin|edit-user')->group(function () {
        Route::post('organizations/requests/{id}/approve', 'Admin\OrganizationApiController@approve')->whereNumber('id');
        Route::post('organizations/requests/{id}/reject', 'Admin\OrganizationApiController@reject')->whereNumber('id');
        Route::post('organizations/{userId}/discount', 'Admin\OrganizationApiController@discount')->whereNumber('userId');
        Route::post('organizations/{userId}/cancel', 'Admin\OrganizationApiController@cancel')->whereNumber('userId');
        Route::post('organizations/{userId}/note', 'Admin\OrganizationApiController@note')->whereNumber('userId');
        // One referrer's own commission percents (admin › user › معرفی‌شده‌ها).
        Route::post('users/{id}/referral', 'Admin\ReferralApiController@saveUser')->whereNumber('id');
        Route::post('organizations/settings', 'Admin\OrganizationApiController@saveSettings');
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

    /* Management: roles and admins are for super-admins only; partners follow partner-list. */
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
