<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Admin\Concerns\AdminApi;
use App\Http\Controllers\Controller;
use App\Models\Order;
use App\Models\OrganizationRequest;
use App\Models\User;
use App\Services\Organizations;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Storage;

/**
 * React admin › حساب‌های سازمانی (App\Services\Organizations): requests to review, approved organisations with their
 * discount, the form customers fill in, and the settings (open for requests, default discount, product families).
 */
class OrganizationApiController extends Controller
{
    use AdminApi;

    /* ---------- Requests ---------- */

    public function requests(Request $request)
    {
        $status = (string) $request->input('status', 'pending');
        $search = trim((string) $request->input('q', ''));
        $base = OrganizationRequest::query()->with('user:id,name,mobile,type,org_discounts');
        if ($search !== '') {
            $base->where(function ($q) use ($search) {
                $q->where('company_name', 'LIKE', "%$search%")->orWhere('tax_code', 'LIKE', "%$search%")
                    ->orWhereHas('user', fn ($u) => $u->where('mobile', 'LIKE', "%$search%")->orWhere('name', 'LIKE', "%$search%"));
            });
        }
        $counts = collect(Organizations::STATUSES)->mapWithKeys(fn ($s) => [$s => (clone $base)->where('status', $s)->count()]);
        $page = (clone $base)->when(in_array($status, Organizations::STATUSES, true), fn ($q) => $q->where('status', $status))
            ->orderByDesc('id')->paginate($this->perPage($request, 20));

        return response()->json([
            'counts' => $counts,
            'requests' => collect($page->items())->map(fn (OrganizationRequest $r) => [
                'id' => $r->id,
                'status' => $r->status,
                'company' => $r->company_name,
                'taxCode' => $r->tax_code,
                'user' => $r->user ? ['id' => $r->user->id, 'name' => $r->user->name, 'mobile' => $r->user->mobile] : null,
                'createdAt' => $this->jdate($r->created_at),
                'reviewedAt' => $this->jdate($r->reviewed_at),
            ])->values(),
            'meta' => $this->pageMeta($page),
        ]);
    }

    public function request($id)
    {
        $r = OrganizationRequest::with('user')->find($id);
        if (!$r) {
            return $this->missing('این درخواست پیدا نشد.');
        }
        $data = Organizations::requestData($r, true);
        $data['createdAt'] = $this->jdate($r->created_at);
        $data['reviewedAt'] = $this->jdate($r->reviewed_at);
        $data['reviewer'] = $r->reviewed_by ? optional(\App\Admin::find($r->reviewed_by))->full_name : null;
        $data['user'] = $r->user ? [
            'id' => $r->user->id, 'name' => $r->user->name, 'mobile' => $r->user->mobile, 'email' => $r->user->email,
            'organization' => Organizations::isOrganization($r->user), 'discounts' => (object) Organizations::discountsOf($r->user),
        ] : null;
        $data['others'] = OrganizationRequest::where('user_id', $r->user_id)->where('id', '!=', $r->id)->orderByDesc('id')
            ->get(['id', 'status', 'created_at'])->map(fn ($o) => ['id' => $o->id, 'status' => $o->status, 'createdAt' => $this->jdate($o->created_at)]);
        $data['defaults'] = (object) Organizations::settings()['defaults'];
        $data['families'] = $this->families();
        $data['maxDiscount'] = (int) config('organization.max_discount', 50);
        return response()->json(['request' => $data]);
    }

    public function approve(Request $request, $id, Organizations $orgs)
    {
        $r = OrganizationRequest::find($id);
        if (!$r || $r->status !== 'pending') {
            return $this->refuse('فقط درخواست‌های در انتظار بررسی را می‌توان تایید کرد.');
        }
        $this->validateDiscounts($request);
        $orgs->approve($r, (array) $request->input('discounts', []), (int) Auth::guard('admin')->id());
        return response()->json(['success' => 'حساب سازمانی تایید شد.']);
    }

    public function reject(Request $request, $id, Organizations $orgs)
    {
        $r = OrganizationRequest::find($id);
        if (!$r || $r->status !== 'pending') {
            return $this->refuse('فقط درخواست‌های در انتظار بررسی را می‌توان رد کرد.');
        }
        $request->validate(['note' => 'required|string|max:1000'], ['note.*' => 'دلیل رد را برای مشتری بنویسید.']);
        $orgs->reject($r, trim((string) $request->input('note')), (int) Auth::guard('admin')->id());
        return response()->json(['success' => 'درخواست رد شد.']);
    }

    /** A file an applicant sent (private disk, admins only). */
    public function file($id, $key)
    {
        $r = OrganizationRequest::find($id);
        $row = $r ? collect((array) $r->answers)->first(fn ($a) => ($a['key'] ?? null) === $key && ($a['type'] ?? null) === 'file') : null;
        $disk = Storage::disk(config('organization.disk', 'local'));
        $path = (string) ($row['file'] ?? '');
        if (!$row || $path === '' || !str_starts_with($path, Organizations::folder($r->id) . '/') || str_contains($path, '..') || !$disk->exists($path)) {
            return $this->missing('این فایل پیدا نشد.');
        }
        $allowed = array_merge(...array_map('array_keys', array_values(Organizations::ACCEPT)));
        $mime = in_array($row['mime'] ?? '', $allowed, true) ? $row['mime'] : 'application/octet-stream';
        $headers = ['Content-Type' => $mime, 'X-Content-Type-Options' => 'nosniff', 'Cache-Control' => 'private, no-store', 'Referrer-Policy' => 'no-referrer'];
        if (str_starts_with($mime, 'image/')) {
            $headers['Content-Security-Policy'] = "default-src 'none'; img-src 'self'; style-src 'unsafe-inline'; sandbox";
        }
        return $disk->response($path, (string) ($row['value'] ?? basename($path)), $headers, 'inline');
    }

    /* ---------- Organisations ---------- */

    public function organizations(Request $request)
    {
        $search = trim((string) $request->input('q', ''));
        $page = User::where('type', Organizations::USER_TYPE)
            ->when($search !== '', fn ($q) => $q->where(fn ($w) => $w->where('mobile', 'LIKE', "%$search%")->orWhere('name', 'LIKE', "%$search%")
                ->orWhereIn('id', OrganizationRequest::where('status', 'approved')->where('company_name', 'LIKE', "%$search%")->select('user_id'))))
            ->orderByDesc('id')->paginate($this->perPage($request, 20));
        $ids = collect($page->items())->pluck('id');
        $profiles = OrganizationRequest::whereIn('user_id', $ids)->where('status', 'approved')->get()->keyBy('user_id');
        $saved = Order::whereIn('user_id', $ids)->where('discount_code', Organizations::CODE)->where('pay_status', 'Complete')
            ->groupBy('user_id')->selectRaw('user_id, sum(discount_price) as total, count(*) as orders')->get()->keyBy('user_id');

        return response()->json([
            'organizations' => collect($page->items())->map(fn (User $u) => [
                'userId' => $u->id,
                'name' => $u->name,
                'mobile' => $u->mobile,
                'company' => optional($profiles[$u->id] ?? null)->company_name,
                'taxCode' => optional($profiles[$u->id] ?? null)->tax_code,
                'requestId' => optional($profiles[$u->id] ?? null)->id,
                'discounts' => (object) Organizations::discountsOf($u),
                'discountedOrders' => (int) ($saved[$u->id]->orders ?? 0),
                'discountGiven' => (int) ($saved[$u->id]->total ?? 0),
                'since' => $this->jdate(optional($profiles[$u->id] ?? null)->reviewed_at),
            ])->values(),
            'meta' => $this->pageMeta($page),
        ]);
    }

    public function discount(Request $request, $userId, Organizations $orgs)
    {
        $user = User::where('type', Organizations::USER_TYPE)->find($userId);
        if (!$user) {
            return $this->missing('این حساب سازمانی پیدا نشد.');
        }
        $this->validateDiscounts($request);
        $orgs->setDiscounts($user, (array) $request->input('discounts', []));
        return response()->json(['success' => 'تخفیف ذخیره شد.']);
    }

    public function cancel(Request $request, $userId, Organizations $orgs)
    {
        $user = User::where('type', Organizations::USER_TYPE)->find($userId);
        if (!$user) {
            return $this->missing('این حساب سازمانی پیدا نشد.');
        }
        $orgs->cancel($user, trim((string) $request->input('note', '')), (int) Auth::guard('admin')->id());
        return response()->json(['success' => 'حساب به شخصی برگشت.']);
    }

    /* ---------- Form and settings ---------- */

    public function settings()
    {
        $settings = Organizations::settings();
        return response()->json([
            'settings' => ['open' => $settings['open'], 'defaults' => (object) $settings['defaults']],
            'families' => $this->families(),
            'fields' => Organizations::fields(false)->map(fn ($f) => Organizations::fieldData($f, true))->values(),
            'types' => Organizations::FIELD_TYPES,
            'maxDiscount' => (int) config('organization.max_discount', 50),
            'maxMb' => (int) config('organization.max_mb', 10),
        ]);
    }

    public function saveSettings(Request $request)
    {
        $this->validateDiscounts($request, 'settings.defaults');
        [$ok, $error] = Organizations::saveFields((array) $request->input('fields', []));
        if (!$ok) {
            return $this->refuse($error);
        }
        Organizations::saveSettings((array) $request->input('settings', []));
        return response()->json(['success' => 'تنظیمات ذخیره شد.']);
    }

    /** Product families a company can get a percent on, for the admin's discount table. */
    private function families(): array
    {
        return collect(Organizations::FAMILIES)->map(fn ($label, $key) => ['key' => $key, 'label' => $label])->values()->all();
    }

    /** {family: percent}: each percent between 0 and the cap. Errors come back under discounts.{family}. */
    private function validateDiscounts(Request $request, string $field = 'discounts'): void
    {
        $max = (int) config('organization.max_discount', 50);
        // "۱۲٫۵" → "12.5" before checking.
        $given = data_get($request->all(), $field);
        if (is_array($given)) {
            $clean = array_map(fn ($v) => is_scalar($v) ? str_replace(['٫', '،'], '.', convertPersianNumbersToEnglish(trim((string) $v))) : $v, $given);
            $all = $request->all();
            data_set($all, $field, $clean);
            $request->replace($all);
        }
        $rules = [$field => 'nullable|array'];
        $messages = [];
        foreach (array_keys(Organizations::FAMILIES) as $family) {
            $rules["$field.$family"] = "nullable|numeric|min:0|max:$max";
            $messages["$field.$family.*"] = 'درصد باید عددی بین ۰ و ' . strtr((string) $max, ['0' => '۰', '1' => '۱', '2' => '۲', '3' => '۳', '4' => '۴', '5' => '۵', '6' => '۶', '7' => '۷', '8' => '۸', '9' => '۹']) . ' باشد.';
        }
        $request->validate($rules, $messages);
    }
}
