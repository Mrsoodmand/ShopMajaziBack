<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use Illuminate\Support\Facades\Auth;

/**
 * Serves the React admin panel (resources/admin). React handles every path under /admin/app.
 * Sections not yet rebuilt in React link to their old Blade pages from the sidebar.
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
                    'legacyHome' => route('admin.home'),
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
                    'orderType' => collect(__('order.type'))->except('0'),
                    'payStatus' => __('payments.status'),
                    'paymentType' => __('payments.type'),
                ],
                'menu' => $this->menu($admin),
            ],
        ]);
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
                    $can(['order']) ? ['label' => 'سفارش‌ها', 'icon' => 'orders', 'to' => '/orders'] : null,
                    // One React page replaces the old transactions, bank payments and mismatch lists; the API limits what each permission sees.
                    $can(['transaction-list', 'bank-transaction', 'moghayerat']) ? ['label' => 'تراکنش‌ها', 'icon' => 'transactions', 'to' => '/transactions'] : null,
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
                        $can(['virtual-service-setting']) ? ['label' => 'تنظیمات', 'href' => route('admin.virtualService-setting-page')] : null,
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
                        $orders(7),
                    ]] : null,
                ])),
            ],
            [
                'title' => 'کاربران',
                'items' => array_values(array_filter([
                    $can(['users-list']) ? ['label' => 'کاربران', 'icon' => 'users', 'to' => '/users'] : null,
                    $can(['tickets-list']) ? ['label' => 'تیکت‌ها', 'icon' => 'tickets', 'to' => '/tickets'] : null,
                    $can(['comments-list', 'accept-comment']) ? ['label' => 'نظرها', 'icon' => 'comments', 'to' => '/comments'] : null,
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
                $isSuper ? ['label' => 'نقش‌ها و دسترسی‌ها', 'icon' => 'roles', 'to' => '/roles'] : null,
                $isSuper ? ['label' => 'مدیران', 'icon' => 'admins', 'to' => '/admins'] : null,
                $can(['partner-list']) ? ['label' => 'همکاران', 'icon' => 'partners', 'to' => '/partners'] : null,
                $isSuper ? ['label' => 'ربات تلگرام', 'icon' => 'bot', 'children' => [
                    ['label' => 'کاربران ربات', 'to' => '/bot/users'],
                    ['label' => 'سفارش‌های ربات', 'to' => '/bot/orders'],
                    ['label' => 'آی‌پی‌های ربات', 'to' => '/bot/ip'],
                    ['label' => 'اطلاعیه‌ها', 'to' => '/bot/messages'],
                ]] : null,
            ])),
        ];

        return array_values(array_filter($sections, fn ($s) => count($s['items']) > 0));
    }
}
