<?php

namespace App\Http\Controllers\Admin;

use App\Admin;
use App\Http\Controllers\Controller;
use App\lib\Jdf;
use App\Models\Card;
use App\Models\History;
use App\Models\Order;
use App\Models\Payment;
use App\User;
use Illuminate\Contracts\Cache\LockTimeoutException;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\ValidationException;

/**
 * JSON for the React transactions screen (resources/admin/pages/transactions): every row of `payments`
 * (order payments, refunds, wallet top-ups, commissions, IP add-ons).
 *
 * What an admin sees follows the old sidebar: `transaction-list` sees everything, `bank-transaction` only
 * bank payments, `moghayerat` only mismatches. Refunds reuse OrderController@ChangeStatusWallet; the only
 * write here is resolving a mismatched wallet top-up, which the old admin had no action for.
 */
class TransactionApiController extends Controller
{
    /** Statuses an admin has to look at; the list opens on these, oldest first. -3 = card mismatch, -2 = under review. */
    const REVIEW = ['-3', '-2'];

    /** -4 = the bank refused to verify the payment. */
    const FAILED = ['failed', '-4'];

    const PER_PAGE = 30;

    /** Old refunds were stored with type "in"; they are the same thing as type 2. */
    const REFUND_TYPES = ['2', 'in'];

    public function index(Request $request)
    {
        $view = $request->input('view', 'review');
        $search = $this->latinDigits(trim((string) $request->input('q', '')));
        [$from, $to] = $this->dateRange($request);

        $base = Payment::query();
        $this->scope($base);

        if ($search !== '') {
            $base->where(function (Builder $q) use ($search) {
                if (ctype_digit($search)) {
                    $q->where('id', (int) $search)->orWhere('refid', $search)->orWhere('order_id', (int) $search);
                } else {
                    $q->where('authority', $search)->orWhere('card', 'LIKE', '%' . $search . '%');
                }
                $userIds = User::where('mobile', 'LIKE', '%' . $search . '%')
                    ->when(!ctype_digit($search), fn ($u) => $u->orWhere('name', 'LIKE', '%' . $search . '%'))
                    ->limit(500)->pluck('id');
                if ($userIds->isNotEmpty()) {
                    $q->orWhereIn('user_id', $userIds);
                }
            });
        }
        if ($type = (string) $request->input('type', '')) {
            $type === '2' ? $base->whereIn('type', self::REFUND_TYPES) : $base->where('type', $type);
        }
        match ((string) $request->input('gate', '')) {
            'Bank' => $base->where('gate', 'Bank'),
            'Wallet' => $base->where('gate', 'Wallet'),
            'none' => $base->where(fn ($q) => $q->whereNull('gate')->orWhere('gate', '')),
            default => null,
        };
        if ($userId = (int) $request->input('user')) {
            $base->where('user_id', $userId);
        }
        if ($from) {
            $base->where('created_at', '>=', $from . ' 00:00:00');
        }
        if ($to) {
            $base->where('created_at', '<=', $to . ' 23:59:59');
        }

        $counts = (clone $base)->select('status', DB::raw('count(*) as c'))->groupBy('status')->pluck('c', 'status')
            ->mapWithKeys(fn ($c, $s) => [strtolower((string) $s) => (int) $c]);
        $sum = fn (array $statuses) => collect($statuses)->sum(fn ($s) => $counts[strtolower($s)] ?? 0);
        $viewCounts = [
            'review' => $sum(self::REVIEW),
            'paid' => $sum(['Complete']),
            'waiting' => $sum(['waiting to pay']),
            'failed' => $sum(self::FAILED),
            'all' => $counts->sum(),
        ];

        $list = (clone $base)->with('User:id,name,mobile');
        match ($view) {
            'review' => $list->whereIn('status', self::REVIEW),
            'paid' => $list->where('status', 'Complete'),
            'waiting' => $list->where('status', 'waiting to pay'),
            'failed' => $list->whereIn('status', self::FAILED),
            default => null,
        };
        // The review queue is worked oldest first; every other view is newest first.
        $view === 'review' ? $list->orderBy('created_at')->orderBy('id') : $list->orderByDesc('id');
        $page = $list->paginate(self::PER_PAGE);

        $jdf = new Jdf();
        $user = $userId ? User::find($userId, ['id', 'name', 'mobile']) : null;

        return response()->json([
            'counts' => $viewCounts,
            'page' => $page->currentPage(),
            'lastPage' => $page->lastPage(),
            'total' => $page->total(),
            'scope' => $this->visibleScope(),
            'user' => $user ? ['id' => $user->id, 'name' => $user->name, 'mobile' => $user->mobile] : null,
            'transactions' => $page->getCollection()->map(fn (Payment $p) => $this->row($jdf, $p))->values(),
        ]);
    }

    public function show($id)
    {
        $query = Payment::query()->where('id', $id);
        $this->scope($query);
        $payment = $query->with('User')->first();
        if (!$payment) {
            return response()->json(['error' => ['تراکنش پیدا نشد.']], 404);
        }

        $jdf = new Jdf();
        $user = $payment->User;
        $order = $payment->order_id ? Order::find($payment->order_id) : null;
        $type = $this->type($payment);

        // A refund (type 2) keeps the refunded payment's id in `error`.
        $refundOf = $type === '2' && ctype_digit((string) $payment->error) ? (int) $payment->error : null;
        $refund = $type === '1' && $order
            ? Payment::where('order_id', $order->id)->whereIn('type', self::REFUND_TYPES)->where('error', (string) $payment->id)->first()
            : null;

        $registered = $payment->card_id ? Card::find($payment->card_id) : null;
        $registeredMasked = $registered ? $this->mask((string) $registered->card) : null;

        $history = History::where('type', 'payment')->where('post_id', $payment->id)->orderByDesc('id')->get();
        $admins = Admin::whereIn('id', $history->pluck('admin_id')->filter()->unique())->pluck('full_name', 'id');

        return response()->json([...$this->row($jdf, $payment), ...[
            'gateway' => $payment->details,
            'authority' => $payment->authority,
            'fee' => $payment->fee !== null ? (int) $payment->fee : null,
            'error' => $refundOf ? null : $payment->error,
            'registeredCard' => $registeredMasked,
            'cardMatches' => $registeredMasked && $payment->card ? $registeredMasked === $payment->card : null,
            'updatedAt' => $this->date($jdf, $payment->updated_at),
            'customer' => $user ? [
                'id' => $user->id,
                'name' => $user->name,
                'mobile' => $user->mobile,
                'email' => $user->email,
                'wallet' => (int) $user->wallet,
                'walletBlocked' => (int) $user->wallet_blocked === 1,
            ] : null,
            'order' => $order ? [
                'id' => $order->id,
                'type' => (string) $order->type,
                'service' => trim((string) $order->service, ' -') ?: null,
                'price' => (int) $order->price,
                'status' => $order->status,
            ] : null,
            'refundOf' => $refundOf,
            'refundedBy' => $refund ? ['id' => $refund->id, 'at' => $this->date($jdf, $refund->created_at)] : null,
            'history' => $history->map(function ($h) use ($jdf, $admins) {
                $d = json_decode($h->detail, true) ?: [];
                return [
                    'id' => $h->id,
                    'from' => $d['old_status'] ?? null,
                    'to' => $d['new_status'] ?? null,
                    'reason' => $d['detail'] ?? null,
                    'by' => $admins[$h->admin_id] ?? null,
                    'at' => $this->date($jdf, $h->created_at),
                ];
            })->values(),
            'can' => [
                'resolve' => $this->isMismatch($payment) && $this->allowed(['moghayerat']),
                'refund' => $type === '1' && $payment->status === 'Complete' && $order && $user && !$refund && $this->allowed(['order']),
            ],
        ]]);
    }

    /**
     * Resolves a wallet top-up whose paying card didn't match the registered card (status -3): the bank took the
     * money but the wallet was never credited. "credit" adds the amount to the wallet and marks the payment paid;
     * "reject" marks it failed (the money went back to the card outside the site). Both are logged.
     */
    public function resolve(Request $request, $id)
    {
        $data = $request->validate([
            'action' => 'required|in:credit,reject',
            'note' => 'required|string|max:500',
        ], [
            'action.required' => 'نوع اقدام را انتخاب کنید.',
            'action.in' => 'نوع اقدام نامعتبر است.',
            'note.required' => 'توضیح را بنویسید؛ در تاریخچه تراکنش ثبت می‌شود.',
            'note.max' => 'توضیح حداکثر ۵۰۰ نویسه باشد.',
        ]);
        $note = trim($data['note']);
        $admin = Auth::guard('admin')->user();

        $payment = Payment::find($id);
        if (!$payment) {
            return response()->json(['error' => ['تراکنش پیدا نشد.']], 404);
        }

        try {
            // Same lock as wallet purchases, so a purchase in flight can't overwrite the credited balance.
            $result = WalletPurchaseLock((int) $payment->user_id)->block(5, fn () => DB::transaction(function () use ($id, $data, $note, $admin) {
                $payment = Payment::lockForUpdate()->find($id);
                if (!$this->isMismatch($payment)) {
                    return ['error' => 'این تراکنش دیگر منتظر بررسی نیست؛ صفحه را تازه کنید.', 'code' => 409];
                }
                $amount = (int) round((float) $payment->price);
                $credit = $data['action'] === 'credit';

                if ($credit) {
                    $user = User::lockForUpdate()->find($payment->user_id);
                    if (!$user) {
                        return ['error' => 'کاربر این تراکنش پیدا نشد.', 'code' => 422];
                    }
                    $user->wallet = (int) $user->wallet + $amount;
                    $user->save();

                    $payment->status = 'Complete';
                    $payment->paid_at = now();
                    $payment->help = $note;
                } else {
                    $payment->status = 'failed';
                    $payment->help = $note;
                }
                $payment->save();

                $this->log('payment', $payment->id, $admin->id, [
                    'old_status' => '-3',
                    'new_status' => $payment->status,
                    'detail' => $note,
                    'amount' => $credit ? $amount : 0,
                ]);
                if ($credit) {
                    // Same shape UserController@SubmitUserChange writes, so it shows in the user's account history.
                    $this->log('user', $payment->user_id, $admin->id, [
                        'wallet increment' => $amount,
                        'detail' => "تأیید مغایرت تراکنش {$payment->id}: $note",
                        'payment_id' => $payment->id,
                    ]);
                }

                return ['success' => $credit
                    ? number_format($amount) . ' تومان به کیف پول کاربر اضافه شد.'
                    : 'تراکنش رد شد و در تاریخچه ثبت شد.'];
            }));
        } catch (LockTimeoutException) {
            return response()->json(['error' => ['پرداخت دیگری از کیف پول این کاربر در جریان است؛ چند ثانیه بعد دوباره تلاش کنید.']], 409);
        }

        if (isset($result['error'])) {
            return response()->json(['error' => [$result['error']]], $result['code']);
        }
        return response()->json($result);
    }

    private function row(Jdf $jdf, Payment $p): array
    {
        return [
            'id' => $p->id,
            'type' => $this->type($p),
            'status' => (string) $p->status,
            'gate' => $p->gate ?: null,
            'price' => (int) round((float) $p->price),
            'orderId' => $p->order_id ?: null,
            'refId' => $p->refid,
            'card' => $p->card,
            'note' => $p->help,
            'createdAt' => $this->date($jdf, $p->created_at),
            'createdTs' => $p->created_at ? strtotime((string) $p->created_at) : null,
            'paidAt' => $this->date($jdf, $p->paid_at),
            'customer' => $p->User ? ['id' => $p->User->id, 'name' => $p->User->name, 'mobile' => $p->User->mobile] : null,
        ];
    }

    private function type(Payment $p): string
    {
        return in_array((string) $p->type, self::REFUND_TYPES, true) ? '2' : (string) $p->type;
    }

    private function isMismatch(?Payment $p): bool
    {
        return $p && (string) $p->status === '-3' && (string) $p->type === '3';
    }

    private function allowed(array $perms): bool
    {
        $admin = Auth::guard('admin')->user();
        return $admin->hasRole('super-admin') || $admin->hasAnyPermission($perms);
    }

    /** Limits a payments query to what this admin may see (see the class comment). */
    private function scope(Builder $q): void
    {
        $scope = $this->visibleScope();
        if ($scope === 'all') {
            return;
        }
        $q->where(function (Builder $w) use ($scope) {
            if (in_array('bank', $scope, true)) {
                $w->orWhere('gate', 'Bank');
            }
            if (in_array('mismatch', $scope, true)) {
                $w->orWhere('status', '-3');
            }
        });
    }

    /** 'all', or the parts this admin may see: ['bank'], ['mismatch'] or both. */
    private function visibleScope(): string|array
    {
        if ($this->allowed(['transaction-list'])) {
            return 'all';
        }
        return array_values(array_filter([
            $this->allowed(['bank-transaction']) ? 'bank' : null,
            $this->allowed(['moghayerat']) ? 'mismatch' : null,
        ]));
    }

    /** Jalali `from` / `to` (1404/08/07, Persian digits allowed) → Gregorian Y-m-d. */
    private function dateRange(Request $request): array
    {
        $jdf = new Jdf();
        $errors = [];
        $out = [];
        foreach (['from', 'to'] as $key) {
            $raw = $this->latinDigits(trim((string) $request->input($key, '')));
            if ($raw === '') {
                $out[] = null;
                continue;
            }
            if (!preg_match('~^(\d{4})[/\-.](\d{1,2})[/\-.](\d{1,2})$~', $raw, $m) || !$jdf->jcheckdate((int) $m[2], (int) $m[3], (int) $m[1])) {
                $errors[$key] = ['تاریخ را به شکل ۱۴۰۴/۰۸/۰۷ بنویسید.'];
                $out[] = null;
                continue;
            }
            [$gy, $gm, $gd] = $jdf->jalali_to_gregorian((int) $m[1], (int) $m[2], (int) $m[3]);
            $out[] = sprintf('%04d-%02d-%02d', $gy, $gm, $gd);
        }
        if ($errors) {
            throw ValidationException::withMessages($errors);
        }
        return $out;
    }

    private function log(string $type, int $postId, int $adminId, array $detail): void
    {
        $h = new History();
        $h->type = $type;
        $h->post_id = $postId;
        $h->detail = json_encode($detail, JSON_UNESCAPED_UNICODE);
        $h->admin_id = $adminId;
        $h->save();
    }

    /** 6037991234565814 → 603799******5814, the way ZarinPal reports the paying card. */
    private function mask(string $card): ?string
    {
        $digits = preg_replace('/\D/', '', $card);
        return strlen($digits) >= 16 ? substr($digits, 0, 6) . '******' . substr($digits, -4) : null;
    }

    private function latinDigits(string $s): string
    {
        return strtr($s, ['۰' => '0', '۱' => '1', '۲' => '2', '۳' => '3', '۴' => '4', '۵' => '5', '۶' => '6', '۷' => '7', '۸' => '8', '۹' => '9',
            '٠' => '0', '١' => '1', '٢' => '2', '٣' => '3', '٤' => '4', '٥' => '5', '٦' => '6', '٧' => '7', '٨' => '8', '٩' => '9']);
    }

    private function date(Jdf $jdf, $value): ?string
    {
        return $value ? $jdf->jdate('Y/m/d H:i', strtotime((string) $value)) : null;
    }
}
