<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\lib\Jdf;
use App\Models\History;
use App\Models\Payment;
use App\Services\Referrals;
use App\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Validation\ValidationException;

/*
 * Admin › معرفی دوستان (App\Services\Referrals): the on/off switch and the default commission per service
 * (super-admin), and one referrer's own percents on admin › user › معرفی‌شده‌ها (edit-user).
 */
class ReferralApiController extends Controller
{
    public function index()
    {
        $recent = Payment::where('type', Referrals::PAYMENT_TYPE)->orderByDesc('id')->take(30)->get();
        $users = User::whereIn('id', $recent->pluck('user_id')->filter()->unique())->get(['id', 'name', 'mobile'])->keyBy('id');
        return response()->json([
            'settings' => Referrals::settings(),
            'families' => $this->families(),
            'stats' => [
                'referred' => User::where('parent_id', '>', 0)->count(),
                'referrers' => User::where('parent_id', '>', 0)->distinct()->count('parent_id'),
                'paid' => (int) Payment::where('type', Referrals::PAYMENT_TYPE)->where('status', 'Complete')->sum('price'),
                'custom' => User::whereNotNull('referral_percents')->count(),
            ],
            'recent' => $recent->map(fn ($p) => [
                'id' => $p->id,
                'orderId' => $p->order_id,
                'price' => (int) $p->price,
                'status' => $p->status,
                'note' => $p->help,
                'user' => ($u = $users->get($p->user_id)) ? ['id' => $u->id, 'name' => $u->name, 'mobile' => $u->mobile] : null,
                'at' => $this->date($p->created_at),
            ])->values(),
        ]);
    }

    public function save(Request $request)
    {
        $this->validatePercents($request, 'percents');
        Referrals::saveSettings([
            'enabled' => filter_var($request->input('enabled'), FILTER_VALIDATE_BOOLEAN),
            'percents' => (array) $request->input('percents', []),
        ]);
        return response()->json(['success' => 'تنظیمات معرفی دوستان ذخیره شد.']);
    }

    /** One referrer's own percents: {percents: {family: percent | ''}}; empty = use the default. */
    public function saveUser(Request $request, $id)
    {
        $user = User::find($id);
        if (!$user) {
            return response()->json(['error' => ['این کاربر پیدا نشد.']], 404);
        }
        $this->validatePercents($request, 'percents');
        $own = Referrals::saveOwnPercents($user, (array) $request->input('percents', []));

        $text = $own
            ? 'پورسانت اختصاصی معرفی: ' . collect($own)->map(fn ($p, $f) => Referrals::FAMILIES[$f] . ' ' . rtrim(rtrim(number_format($p, 2, '.', ''), '0'), '.') . '٪')->implode('، ')
            : 'پورسانت اختصاصی معرفی برداشته شد (درصدهای پیش‌فرض)';
        $h = new History();
        $h->type = 'user';
        $h->post_id = $user->id;
        $h->admin_id = (int) Auth::guard('admin')->id();
        $h->detail = json_encode(['detail' => $text, 'changed_by' => $h->admin_id, 'changed_date' => now()], JSON_UNESCAPED_UNICODE);
        $h->save();

        return response()->json(['success' => $own ? 'پورسانت اختصاصی ذخیره شد.' : 'این کاربر حالا درصدهای پیش‌فرض را می‌گیرد.']);
    }

    /** What admin › user shows: code, who referred them, earnings, and their own percents next to the defaults. */
    public static function forUser($user): array
    {
        $parent = (int) $user->parent_id > 0 ? User::find($user->parent_id, ['id', 'name', 'mobile']) : null;
        return [
            'enabled' => Referrals::enabled(),
            'code' => $user->token ? (string) $user->token : null,
            'referredBy' => $parent ? ['id' => $parent->id, 'name' => $parent->name, 'mobile' => $parent->mobile] : null,
            'earned' => Referrals::earned($user),
            'own' => (object) Referrals::ownPercents($user),
            'defaults' => Referrals::settings()['percents'],
            'families' => collect(Referrals::FAMILIES)->map(fn ($label, $key) => ['key' => $key, 'label' => $label])->values()->all(),
        ];
    }

    private function families(): array
    {
        return collect(Referrals::FAMILIES)->map(fn ($label, $key) => ['key' => $key, 'label' => $label])->values()->all();
    }

    private function validatePercents(Request $request, string $field): void
    {
        $errors = [];
        foreach ((array) $request->input($field, []) as $family => $value) {
            if (!array_key_exists($family, Referrals::FAMILIES) || !is_scalar($value) && $value !== null) {
                continue;
            }
            $text = trim(convertPersianNumbersToEnglish((string) $value));
            if ($text === '') {
                continue;
            }
            $n = str_replace(['٫', '،', ','], '.', $text);
            if (!is_numeric($n) || (float) $n < 0 || (float) $n > 100) {
                $errors["$field.$family"] = 'یک درصد بین ۰ تا ۱۰۰ بنویسید.';
            }
        }
        if ($errors) {
            throw ValidationException::withMessages($errors);
        }
    }

    private function date($value): ?string
    {
        return $value ? (new Jdf())->jdate('Y/m/d H:i', strtotime((string) $value)) : null;
    }
}
