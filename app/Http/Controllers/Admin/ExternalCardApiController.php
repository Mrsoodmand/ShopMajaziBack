<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Admin\Concerns\AdminApi;
use App\Http\Controllers\Controller;
use App\Models\ExternalCard;
use App\Models\Order;
use Illuminate\Http\Request;

/**
 * admin › کارت‌های خارجی (super-admin): the shop's own cards for paying outside services (App\Models\ExternalCard).
 * Only super-admins get full numbers; the order page (any order staff) gets labels only (OrderApiController).
 */
class ExternalCardApiController extends Controller
{
    use AdminApi;

    public function index()
    {
        return response()->json([
            'cards' => ExternalCard::orderByDesc('active')->orderBy('title')->get()->map(fn (ExternalCard $c) => [
                'id' => $c->id,
                'title' => $c->title,
                'number' => $c->number,
                'expiry' => $c->expiry,
                'last4' => $c->last4,
                'active' => $c->active,
                'note' => $c->note,
                'orders' => Order::where('external_card_id', $c->id)->count(),
            ])->values(),
        ]);
    }

    /** New card (no id) or an edit: {title, number, expiry, active, note}. */
    public function save(Request $request, $id = null)
    {
        $card = $id ? ExternalCard::find($id) : new ExternalCard();
        if (!$card) {
            return $this->missing('این کارت پیدا نشد.');
        }
        $all = $request->all();
        $all['number'] = preg_replace('/\D+/', '', convertPersianNumbersToEnglish((string) ($all['number'] ?? '')));
        $all['expiry'] = preg_replace('/\s+/', '', convertPersianNumbersToEnglish((string) ($all['expiry'] ?? '')));
        $request->replace($all);
        $data = $request->validate([
            'title' => 'required|string|max:100',
            'number' => ['required', 'regex:/^\d{13,19}$/', function ($attr, $value, $fail) {
                if (!self::luhn($value)) {
                    $fail('شماره کارت درست نیست؛ دوباره بررسی کنید.');
                }
            }],
            'expiry' => ['required', 'regex:~^\d{2,4}/\d{2,4}$~'],
            'active' => 'boolean',
            'note' => 'nullable|string|max:255',
        ], [
            'title.*' => 'یک نام برای کارت بنویسید (مثلا ویزا ملت).',
            'number.required' => 'شماره کارت را وارد کنید.',
            'number.regex' => 'شماره کارت باید ۱۳ تا ۱۹ رقم باشد.',
            'expiry.*' => 'تاریخ انقضا را مثل 08/28 یا 1405/06 بنویسید.',
            'note.*' => 'یادداشت حداکثر ۲۵۵ حرف است.',
        ]);
        $card->fill([
            'title' => trim($data['title']),
            'number' => $data['number'],
            'expiry' => $data['expiry'],
            'last4' => substr($data['number'], -4),
            'active' => (bool) ($data['active'] ?? true),
            'note' => trim((string) ($data['note'] ?? '')) ?: null,
        ])->save();
        return response()->json(['success' => 'کارت ذخیره شد.', 'id' => $card->id]);
    }

    /** Removes a card no order uses; a used one can only be switched off. */
    public function delete($id)
    {
        $card = ExternalCard::find($id);
        if (!$card) {
            return $this->missing('این کارت پیدا نشد.');
        }
        if (Order::where('external_card_id', $card->id)->exists()) {
            return $this->refuse('این کارت در سفارش‌ها ثبت شده است؛ به‌جای حذف، غیرفعالش کنید.');
        }
        $card->delete();
        return response()->json(['success' => 'کارت حذف شد.']);
    }

    private static function luhn(string $n): bool
    {
        $sum = 0;
        $alt = false;
        for ($i = strlen($n) - 1; $i >= 0; $i--) {
            $d = (int) $n[$i];
            if ($alt) {
                $d *= 2;
                if ($d > 9) {
                    $d -= 9;
                }
            }
            $sum += $d;
            $alt = !$alt;
        }
        return $sum % 10 === 0;
    }
}
