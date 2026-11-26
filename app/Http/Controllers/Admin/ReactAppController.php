<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use Illuminate\Support\Facades\Auth;

/**
 * Serves the React admin panel (resources/admin). React handles every path under /admin/app.
 */
class ReactAppController extends Controller
{
    public function __invoke()
    {
        $admin = Auth::guard('admin')->user();

        return view('admin-react.app', [
            'boot' => [
                'admin' => ['id' => $admin->id, 'name' => $admin->full_name ?: $admin->name ?: $admin->mobile],
                'basePath' => '/admin/app',
                'csrfToken' => csrf_token(),
                'links' => [
                    'logout' => url('/admin-url-login-with-secure-path/logout'),
                ],
                'actions' => [
                    'changeStatus' => route('admin.change order status'),
                    'refund' => route('admin.return back order money to user'),
                    'sendMessage' => route('admin.send order chat'),
                    'saveUser' => route('admin.user submit'),
                ],
                'labels' => [
                    'orderStatus' => __('order.status'),
                    'orderType' => collect(__('order.type'))->except('0')->put('9', 'سفارش اختصاصی'), // 9: App\Services\OrderCharges
                    'payStatus' => __('payments.status'),
                    'paymentType' => __('payments.type'),
                ],
                'menu' => $this->menu($admin),
                'menuCounts' => self::menuCounts($admin),
            ],
        ]);
    }

    /**
     * The numbers beside «سفارش‌ها» and «تیکت‌ها» in the sidebar: orders that need action and tickets waiting for a
     * reply. Rendered into the page and refreshed by GET /admin/api/menu-counts; each is left out without its permission.
     */
    public static function menuCounts($admin): array
    {
        $isSuper = $admin->hasRole('super-admin');
        $counts = [];
        if ($isSuper || $admin->hasAnyPermission(['order'])) {
            $counts['orders'] = \App\Models\Order::whereIn('status', OrderApiController::NEEDS_ACTION)->count();
        }
        if ($isSuper || $admin->hasAnyPermission(['accounts-list', 'accounts-create-edit'])) {
            $counts['accountRequests'] = \App\Services\AccountRequests::pendingCount();
        }
        if ($isSuper || $admin->hasAnyPermission(['tickets-list'])) {
            $counts['tickets'] = \App\Models\Ticket::whereIn('status', TicketApiController::WAITING)->count();
        }

        return $counts;
    }

    public function counts()
    {
        return response()->json(self::menuCounts(Auth::guard('admin')->user()));
    }

    /** The session's current CSRF token, for a save that came back 419 with the token the page was rendered with. */
    public function csrf()
    {
        return response()->json(['token' => csrf_token()]);
    }

    /** The sidebar, mirroring resources/views/admin-v2/layout/menu.blade.php and its permission checks. */
    private function menu($admin): array
    {
        $isSuper = $admin->hasRole('super-admin');
        $can = fn (array $perms) => $isSuper || $admin->hasAnyPermission($perms);
        $orders = fn (int $type) => ['label' => 'سفارش‌ها', 'to' => '/orders?type=' . $type . '&view=all'];

        $sections = [
            [
                'title' => null,
                'items' => [
                    ['label' => 'داشبورد', 'icon' => 'home', 'to' => '/'],
                ],
            ],
            [
                'title' => 'سفارش‌ها و حسابداری',
                'items' => array_values(array_filter([
                    $can(['order']) ? ['label' => 'سفارش‌ها', 'icon' => 'orders', 'to' => '/orders', 'badge' => 'orders'] : null,
                    // One React page replaces the old transactions, bank payments and mismatch lists; the API limits what each permission sees.
                    $can(['transaction-list', 'bank-transaction', 'moghayerat']) ? ['label' => 'تراکنش‌ها', 'icon' => 'transactions', 'to' => '/transactions'] : null,
                ])),
            ],
            [
                'title' => 'کاربران',
                'items' => array_values(array_filter([
                    $can(['users-list']) ? ['label' => 'کاربران', 'icon' => 'users', 'to' => '/users'] : null,
                    // Organisation accounts (App\Services\Organizations): requests, discounts, the form.
                    $can(['users-list', 'users-detail', 'edit-user']) ? ['label' => 'حساب‌های سازمانی', 'icon' => 'organization', 'to' => '/organizations'] : null,
                    $can(['tickets-list']) ? ['label' => 'تیکت‌ها', 'icon' => 'tickets', 'to' => '/tickets', 'badge' => 'tickets'] : null,
                    $can(['comments-list', 'accept-comment']) ? ['label' => 'نظرها', 'icon' => 'comments', 'to' => '/comments'] : null,
                ])),
            ],
            [
                'title' => 'خدمات',
                'items' => array_values(array_filter([
                    $can(['virtual-number-countries', 'virtual-number-services', 'virtual-number-setting', 'virtual-number-orders']) ? ['label' => 'شماره مجازی', 'icon' => 'phone', 'children' => array_values(array_filter([
                        $can(['virtual-number-services']) ? ['label' => 'سرویس‌ها', 'to' => '/virtual-numbers/services'] : null,
                        $can(['virtual-number-countries']) ? ['label' => 'کشورها', 'to' => '/virtual-numbers/countries'] : null,
                        $can(['virtual-number-setting']) ? ['label' => 'تنظیمات', 'to' => '/virtual-numbers/settings'] : null,
                        $can(['virtual-number-orders']) ? $orders(1) : null,
                    ]))] : null,
                    $can(['virtual-service', 'virtual-service-orders', 'create-edit-virtual-service']) ? ['label' => 'خدمات مجازی', 'icon' => 'services', 'children' => array_values(array_filter([
                        $can(['create-edit-virtual-service']) ? ['label' => 'پلتفرم‌ها و سرویس‌ها', 'to' => '/services'] : null,
                        $can(['create-edit-virtual-service']) ? ['label' => 'تلگرام استارز', 'to' => '/telegram-stars'] : null,
                        $can(['edit-page']) ? ['label' => 'متن صفحه', 'to' => '/pages/6'] : null, // replaces the old Blade settings page (same row: pages #6)
                        $can(['virtual-service-orders']) ? $orders(2) : null,
                    ]))] : null,
                    $can(['add-apple-id', 'ready-apple-id-orders']) ? ['label' => 'اپل آیدی', 'icon' => 'appleid', 'children' => array_values(array_filter([
                        $can(['add-apple-id']) ? ['label' => 'موجودی اپل آیدی', 'to' => '/apple-ids'] : null,
                        $can(['add-apple-id']) ? ['label' => 'افزودن اپل آیدی', 'to' => '/apple-ids/new'] : null,
                        $can(['edit-page']) ? ['label' => 'قیمت و متن صفحه', 'to' => '/pages/3'] : null,
                        $can(['ready-apple-id-orders']) ? $orders(3) : null,
                    ]))] : null,
                    $can(['ip-sabet-orders', 'ip-sabet-list', 'create-edit-ip-sabet']) ? ['label' => 'آی‌پی ثابت', 'icon' => 'ip', 'children' => array_values(array_filter([
                        $can(['ip-sabet-list', 'create-edit-ip-sabet']) ? ['label' => 'کشورها و سرورها', 'to' => '/ip'] : null,
                        $can(['edit-page']) ? ['label' => 'متن صفحه', 'to' => '/pages/5'] : null,
                        $can(['ip-sabet-orders']) ? $orders(5) : null,
                    ]))] : null,
                    $can(['google-voice-list', 'google-voice-orders']) ? ['label' => 'گوگل ویس', 'icon' => 'voice', 'children' => array_values(array_filter([
                        $can(['google-voice-list']) ? ['label' => 'شماره‌ها', 'to' => '/google-voice'] : null,
                        $can(['edit-page']) ? ['label' => 'قیمت و نامبرلند', 'to' => '/pages/4'] : null,
                        $can(['google-voice-orders']) ? $orders(6) : null,
                    ]))] : null,
                    $can(['accounts-list', 'accounts-create-edit']) ? ['label' => 'اکانت‌های خارجی', 'icon' => 'accounts', 'children' => [
                        ['label' => 'لیست اکانت‌ها', 'to' => '/accounts'],
                        ['label' => 'درخواست اکانت', 'to' => '/account-requests', 'badge' => 'accountRequests'],
                        $orders(7),
                    ]] : null,
                    $can(['accounts-list', 'accounts-create-edit']) ? ['label' => 'وریفای', 'icon' => 'verify', 'children' => [
                        ['label' => 'محصولات وریفای', 'to' => '/verification'],
                        $orders(\App\Services\VerificationOrders::TYPE),
                    ]] : null,
                ])),
            ],
            [
                'title' => 'محتوا',
                'items' => array_values(array_filter([
                    $can(['blog-list', 'create-edit-blog']) ? ['label' => 'بلاگ', 'icon' => 'blog', 'to' => '/blog'] : null,
                    $can(['news-list', 'create-edit-news']) ? ['label' => 'اخبار', 'icon' => 'news', 'to' => '/news'] : null,
                    $can(['page-list', 'edit-page']) ? ['label' => 'صفحه‌ها', 'icon' => 'pages', 'to' => '/pages'] : null,
                    $can(['page-list', 'edit-page']) ? ['label' => 'سئوی صفحه‌ها', 'icon' => 'seo', 'to' => '/seo'] : null,
                    ['label' => 'کتابخانه رسانه', 'icon' => 'media', 'to' => '/media'], // every admin; deleting and moving files is super-admin only (CKFinderAccess)
                ])),
            ],
        ];

        $sections[] = [
            'title' => 'مدیریت',
            'items' => array_values(array_filter([
                $isSuper ? ['label' => 'ارزها و سود', 'icon' => 'pricing', 'to' => '/pricing'] : null,
                $isSuper ? ['label' => 'درگاه‌های پرداخت', 'icon' => 'bank', 'to' => '/payment-gateways'] : null,
                $isSuper ? ['label' => 'کارت‌های خارجی', 'icon' => 'card', 'to' => '/external-cards'] : null,
                $isSuper ? ['label' => 'معرفی دوستان', 'icon' => 'referral', 'to' => '/referral'] : null,
                $isSuper ? ['label' => 'مدیریت کش', 'icon' => 'cache', 'to' => '/cache'] : null,
                $isSuper ? ['label' => 'کپچا', 'icon' => 'captcha', 'to' => '/captcha'] : null,
                $isSuper ? ['label' => 'پیامک', 'icon' => 'sms', 'to' => '/sms'] : null,
                $isSuper ? ['label' => 'نقش‌ها و دسترسی‌ها', 'icon' => 'roles', 'to' => '/roles'] : null,
                $isSuper ? ['label' => 'مدیران', 'icon' => 'admins', 'to' => '/admins'] : null,
                $can(['partner-list']) ? ['label' => 'همکاران', 'icon' => 'partners', 'to' => '/partners'] : null,
            ])),
        ];

        return array_values(array_filter($sections, fn ($s) => count($s['items']) > 0));
    }
}
