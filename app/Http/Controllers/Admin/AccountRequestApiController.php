<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Admin\Concerns\AdminApi;
use App\Http\Controllers\Controller;
use App\Models\AccountRequest;
use App\Services\AccountRequests;
use App\Services\FormFields;
use App\Services\OrderCharges;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

/**
 * React admin › اکانت‌های خارجی › درخواست‌ها (App\Services\AccountRequests): requests for subscriptions the site
 * doesn't sell, answered with a quote (a custom order the customer pays) or a rejection, and the form they fill in.
 */
class AccountRequestApiController extends Controller
{
    use AdminApi;

    public function index(Request $request)
    {
        $status = (string) $request->input('status', 'pending');
        $search = trim((string) $request->input('q', ''));
        $base = AccountRequest::query()->with(['user:id,name,mobile', 'order:id,status,pay_status,price']);
        if ($search !== '') {
            $base->where(function ($q) use ($search) {
                $q->where('site_name', 'LIKE', "%$search%")->orWhere('site_url', 'LIKE', "%$search%")
                    ->orWhereHas('user', fn ($u) => $u->where('mobile', 'LIKE', "%$search%")->orWhere('name', 'LIKE', "%$search%"));
                if (ctype_digit($search)) {
                    $q->orWhere('id', (int) $search);
                }
            });
        }
        $counts = collect(AccountRequests::STATUSES)->mapWithKeys(fn ($s) => [$s => (clone $base)->where('status', $s)->count()]);
        $page = (clone $base)->when(in_array($status, AccountRequests::STATUSES, true), fn ($q) => $q->where('status', $status))
            ->orderByDesc('id')->paginate($this->perPage($request, 20));

        return response()->json([
            'counts' => $counts,
            'requests' => collect($page->items())->map(fn (AccountRequest $r) => [
                'id' => $r->id,
                'status' => AccountRequests::shownStatus($r),
                'site' => $r->site_name,
                'url' => $r->site_url,
                'user' => $r->user ? ['id' => $r->user->id, 'name' => $r->user->name, 'mobile' => $r->user->mobile] : null,
                'orderId' => $r->order_id,
                'price' => $r->order ? (int) $r->order->price : null,
                'createdAt' => $this->jdate($r->created_at),
            ])->values(),
            'meta' => $this->pageMeta($page),
        ]);
    }

    public function show($id)
    {
        $r = AccountRequest::with(['user', 'order'])->find($id);
        if (!$r) {
            return $this->missing('این درخواست پیدا نشد.');
        }
        $data = AccountRequests::data($r, true);
        $data['createdAt'] = $this->jdate($r->created_at);
        $data['reviewedAt'] = $this->jdate($r->reviewed_at);
        $data['reviewer'] = $r->reviewed_by ? optional(\App\Admin::find($r->reviewed_by))->full_name : null;
        $data['user'] = $r->user ? ['id' => $r->user->id, 'name' => $r->user->name, 'mobile' => $r->user->mobile, 'email' => $r->user->email] : null;
        $data['others'] = AccountRequest::where('user_id', $r->user_id)->where('id', '!=', $r->id)->orderByDesc('id')->limit(10)
            ->get(['id', 'status', 'site_name', 'created_at'])->map(fn ($o) => ['id' => $o->id, 'status' => $o->status, 'site' => $o->site_name, 'createdAt' => $this->jdate($o->created_at)]);
        return response()->json(['request' => $data]);
    }

    /** {title, note?, currency: toman|usd, amount, message?}: makes the custom order the customer pays. */
    public function quote(Request $request, $id, AccountRequests $requests)
    {
        $r = AccountRequest::find($id);
        if (!$r || $r->status !== 'pending') {
            return $this->refuse('فقط به درخواست‌های در انتظار بررسی می‌توان قیمت داد.');
        }
        $title = mb_substr(trim(strip_tags((string) $request->input('title', ''))), 0, 150);
        if ($title === '') {
            return $this->refuse('عنوان سفارش را بنویسید.');
        }
        [$price, $currency, $amount, $error] = OrderCharges::price((string) $request->input('currency'), $this->westernDigits((string) $request->input('amount', '')));
        if ($error) {
            return $this->refuse($error);
        }
        $note = mb_substr(trim(strip_tags((string) $request->input('note', ''))), 0, 500);
        $admin = Auth::guard('admin')->user();
        $order = $requests->quote($r, [
            'title' => $title, 'note' => $note ?: null, 'currency' => $currency, 'amount' => $amount, 'price' => $price,
            'rate' => $currency === 'usd' ? (int) round(\App\Services\Pricing::servicesRate()) : null,
        ], mb_substr(trim(strip_tags((string) $request->input('message', ''))), 0, 1000), (int) $admin->id, (string) ($admin->full_name ?? $admin->name ?? ''));
        return response()->json(['success' => 'قیمت برای مشتری فرستاده شد؛ سفارش #' . $order->id . ' منتظر پرداخت است.', 'orderId' => $order->id]);
    }

    public function reject(Request $request, $id, AccountRequests $requests)
    {
        $r = AccountRequest::find($id);
        if (!$r || $r->status !== 'pending') {
            return $this->refuse('فقط درخواست‌های در انتظار بررسی را می‌توان رد کرد.');
        }
        $request->validate(['note' => 'required|string|max:1000'], ['note.*' => 'دلیل را برای مشتری بنویسید.']);
        $requests->reject($r, trim(strip_tags((string) $request->input('note'))), (int) Auth::guard('admin')->id());
        return response()->json(['success' => 'درخواست رد شد.']);
    }

    /** A file the customer sent (private disk, admins only). */
    public function file($id, $key)
    {
        $r = AccountRequest::find($id);
        $res = $r ? FormFields::fileResponse((array) $r->answers, (string) $key, (string) config('account_requests.disk', 'local'), AccountRequests::folder($r->id)) : null;
        return $res ?? $this->missing('این فایل پیدا نشد.');
    }

    public function settings()
    {
        return response()->json([
            'settings' => AccountRequests::settings(),
            'fields' => AccountRequests::fields(false)->map(fn ($f) => FormFields::data($f, true))->values(),
            'locked' => ['site_name'],
            'maxMb' => (int) config('account_requests.max_mb', 10),
        ]);
    }

    public function saveSettings(Request $request)
    {
        [$ok, $error] = AccountRequests::saveFields((array) $request->input('fields', []));
        if (!$ok) {
            return $this->refuse($error);
        }
        AccountRequests::saveSettings((array) $request->input('settings', []));
        return response()->json(['success' => 'فرم و تنظیمات ذخیره شد.']);
    }
}
