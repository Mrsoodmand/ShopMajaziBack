<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Admin\Concerns\AdminApi;
use App\Http\Controllers\Controller;
use App\Models\HegzaHistory;
use App\Models\HegzaIp;
use App\Models\HegzaOrder;
use App\Models\HegzaPayment;
use App\Models\HegzaUser;
use App\lib\Jdf;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;

/**
 * Users of the Telegram bot (hegza_user): their wallet, orders, payments and referrals.
 * A wallet change by an admin is logged in hegza_history (type "user"), the way the bot panel logs it.
 */
class BotUserApiController extends Controller
{
    use AdminApi;

    const PER_PAGE = 40;

    public function index(Request $request)
    {
        $q = trim((string) $request->input('q', ''));
        $base = HegzaUser::query();
        if ($q !== '') {
            $term = ltrim($q, '@');
            $base->where(function ($w) use ($term) {
                $w->where('username', 'LIKE', "%$term%")->orWhere('user_id', 'LIKE', "%$term%")->orWhere('name', 'LIKE', "%$term%");
                if (ctype_digit($term)) {
                    $w->orWhere('id', (int) $term);
                }
            });
        }
        // A buyer has at least one paid order (anything past "waiting for payment").
        $buyers = fn ($query) => $query->whereIn('id', HegzaOrder::where('status', '!=', 0)->select('user_id'));
        $counts = ['all' => (clone $base)->count(), 'buyers' => $buyers(clone $base)->count()];
        $list = clone $base;
        if ($request->input('filter') === 'buyers') {
            $buyers($list);
        }
        $page = $list->orderByDesc('id')->paginate(self::PER_PAGE);
        $orders = HegzaOrder::whereIn('user_id', $page->getCollection()->pluck('id'))->where('status', '!=', 0)
            ->select('user_id', DB::raw('count(*) c'))->groupBy('user_id')->pluck('c', 'user_id');

        return response()->json($this->pageMeta($page) + [
            'counts' => $counts,
            'users' => $page->getCollection()->map(fn (HegzaUser $u) => $this->row($u) + ['orders' => (int) ($orders[$u->id] ?? 0)])->values(),
        ]);
    }

    public function show($id)
    {
        $u = HegzaUser::find($id);
        if (!$u) {
            return $this->missing('این کاربر ربات پیدا نشد.');
        }
        $parent = $u->parent && $u->parent !== '0' ? HegzaUser::find($u->parent) : null;
        $orders = HegzaOrder::where('user_id', $u->id)->orderByDesc('id')->take(20)->get();
        $payments = HegzaPayment::where('user_id', $u->id)->orderByDesc('id')->take(20)->get();
        $history = HegzaHistory::where('user_id', $u->id)->whereIn('type', ['user', 'payment'])->orderByDesc('id')->take(20)->get();

        return response()->json([
            'user' => $this->row($u) + [
                'path' => (string) $u->path,
                'percent' => (int) $u->percent,
                'parent' => $parent ? $this->row($parent) : null,
                'referrals' => HegzaUser::where('parent', (string) $u->id)->count(),
                'commission' => (int) HegzaPayment::where('user_id', $u->id)->where('type', 5)->where('status', 1)->sum('price'),
                'paid' => (int) HegzaPayment::where('user_id', $u->id)->where('type', 1)->where('status', 1)->sum('price'),
                'orders' => HegzaOrder::where('user_id', $u->id)->count(),
                'activeIps' => HegzaIp::where('type', 'pu-ip-sabet-user')->where('user_id', $u->id)->where('status', 1)->count(),
            ],
            'orders' => $orders->map(fn ($o) => BotOrderApiController::summary($o))->values(),
            'payments' => $payments->map(fn ($p) => self::payment($p))->values(),
            'history' => $history->map(function ($h) {
                $d = $this->decode($h->detail);
                $add = $d['wallet increment'] ?? null;
                $sub = $d['wallet decrement'] ?? null;
                return [
                    'id' => $h->id,
                    'change' => $add !== null ? (int) $add : ($sub !== null ? -(int) $sub : null),
                    'note' => (string) ($d['note'] ?? ($h->type === 'payment' ? 'برگشت پرداخت به کیف پول' : '')),
                    'at' => $this->jdate($h->created_at),
                ];
            })->values(),
        ]);
    }

    /** Adds to or takes from the user's bot wallet. */
    public function wallet(Request $request, $id)
    {
        $u = HegzaUser::find($id);
        if (!$u) {
            return $this->missing('این کاربر ربات پیدا نشد.');
        }
        $data = $request->validate([
            'action' => 'required|in:add,subtract',
            'amount' => 'required|integer|min:1|max:100000000',
            'note' => 'required|string|max:255',
        ], $this->messages() + ['amount.min' => 'مبلغ را بنویسید.', 'note.required' => 'دلیل تغییر را بنویسید؛ در سابقه کاربر می‌ماند.']);

        $result = DB::transaction(function () use ($u, $data) {
            $u = HegzaUser::lockForUpdate()->find($u->id);
            $delta = $data['action'] === 'add' ? $data['amount'] : -$data['amount'];
            if ((int) $u->wallet + $delta < 0) {
                return null;
            }
            $u->wallet = (int) $u->wallet + $delta;
            $u->save();

            $h = new HegzaHistory();
            $h->type = 'user';
            $h->post_id = $u->id;
            $h->user_id = $u->id;
            $h->detail = json_encode([($delta > 0 ? 'wallet increment' : 'wallet decrement') => $data['amount'], 'note' => $data['note']]);
            $h->admin_id = Auth::guard('admin')->id();
            $h->save();
            return (int) $u->wallet;
        });
        if ($result === null) {
            return response()->json(['message' => 'مبلغ', 'errors' => ['amount' => ['موجودی کیف پول کمتر از این مبلغ است.']]], 422);
        }

        return response()->json(['success' => 'کیف پول به‌روز شد.', 'wallet' => $result]);
    }

    private function row(HegzaUser $u): array
    {
        return [
            'id' => $u->id,
            'telegramId' => (string) $u->user_id,
            'username' => (string) $u->username,
            'name' => self::text($u->name),
            'wallet' => (int) $u->wallet,
            'joinedAt' => $this->jdate($u->created_at, 'Y/m/d'),
        ];
    }

    /** hegza_user.name is a binary column; show it as text and drop broken bytes. */
    public static function text($value): string
    {
        return mb_scrub((string) $value, 'UTF-8');
    }

    const PAYMENT_STATUS = ['1' => 'paid', '0' => 'waiting', '-1' => 'failed', '-2' => 'review', '-3' => 'mismatch', '-4' => 'bank-error'];
    const PAYMENT_TYPE = ['1' => 'پرداخت سفارش', '2' => 'برگشت وجه', '3' => 'شارژ کیف پول', '4' => 'برداشت', '5' => 'پورسانت', '6' => 'حجم اضافه', '8' => 'تمدید آی‌پی'];

    /** Jalali date and time for the static helpers shared with the bot order screen. */
    public static function when($value, string $format = 'Y/m/d H:i'): ?string
    {
        static $jdf = null;
        $jdf ??= new Jdf();
        return $value ? $jdf->jdate($format, strtotime((string) $value)) : null;
    }

    public static function payment(HegzaPayment $p): array
    {
        return [
            'id' => $p->id,
            'orderId' => $p->order_id ? (int) $p->order_id : null,
            'type' => self::PAYMENT_TYPE[(string) $p->type] ?? 'پرداخت',
            'method' => match ((string) $p->method) {
                'bank' => 'درگاه بانکی',
                'wallet' => 'کیف پول',
                'cart-be-cart' => 'کارت به کارت',
                default => null,
            },
            'amount' => (int) $p->price,
            'status' => self::PAYMENT_STATUS[(string) $p->status] ?? 'waiting',
            'trackId' => $p->track_id ? (string) $p->track_id : null,
            'at' => self::when($p->created_at),
        ];
    }
}
