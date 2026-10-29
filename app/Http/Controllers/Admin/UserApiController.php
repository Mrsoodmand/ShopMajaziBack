<?php

namespace App\Http\Controllers\Admin;

use App\Admin;
use App\Http\Controllers\Controller;
use App\lib\Jdf;
use App\Models\History;
use App\Models\Order;
use App\Models\Payment;
use App\Models\Ticket;
use App\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

/**
 * JSON reads for the React users screens (resources/admin/pages/users).
 * Edits still go through UserController@SubmitUserChange, which writes the history row.
 */
class UserApiController extends Controller
{
    const PER_PAGE = 30;

    public function index(Request $request)
    {
        $filter = $request->input('filter', 'all');
        $search = trim((string) $request->input('q', ''));

        $base = User::query();
        if ($search !== '') {
            $base->where(function ($q) use ($search) {
                $q->where('mobile', 'LIKE', '%' . $search . '%')
                    ->orWhere('name', 'LIKE', '%' . $search . '%')
                    ->orWhere('email', 'LIKE', '%' . $search . '%');
                if (ctype_digit($search)) {
                    $q->orWhere('id', (int) $search);
                }
            });
        }

        $blocked = fn ($q) => $q->where(fn ($w) => $w->where('status', 1)->orWhere('wallet_blocked', 1)
            ->orWhere('bank_blocked', 1)->orWhere('account_blocked', 1));

        $counts = [
            'all' => (clone $base)->count(),
            'balance' => (clone $base)->where('wallet', '>', 0)->count(),
            'blocked' => $blocked(clone $base)->count(),
        ];

        $list = (clone $base)->withCount('Order');
        match ($filter) {
            'balance' => $list->where('wallet', '>', 0)->orderByDesc('wallet'),
            'blocked' => $blocked($list),
            default => null,
        };
        $page = $list->orderByDesc('id')->paginate(self::PER_PAGE);
        $jdf = new Jdf();

        return response()->json([
            'counts' => $counts,
            'page' => $page->currentPage(),
            'lastPage' => $page->lastPage(),
            'total' => $page->total(),
            'users' => $page->getCollection()->map(fn (User $u) => [
                'id' => $u->id,
                'name' => $u->name,
                'mobile' => $u->mobile,
                'email' => $u->email,
                'wallet' => (int) $u->wallet,
                'blockedWallet' => (int) $u->blocked_wallet,
                'orders' => (int) $u->order_count,
                'flags' => $this->flags($u),
                'createdAt' => $this->date($jdf, $u->created_at),
            ])->values(),
        ]);
    }

    public function show($id)
    {
        $user = User::find($id);
        if (!$user) {
            return response()->json(['error' => ['کاربر پیدا نشد.']], 404);
        }
        $jdf = new Jdf();

        $orders = Order::where('user_id', $user->id)->orderByDesc('id')->take(20)->get();
        $payments = Payment::where('user_id', $user->id)->orderByDesc('id')->take(20)->get();
        $history = History::where('type', 'user')->where('post_id', $user->id)->orderByDesc('id')->get();
        $admins = Admin::whereIn('id', $history->pluck('admin_id')->filter()->unique())->pluck('full_name', 'id');

        return response()->json([
            'id' => $user->id,
            'name' => $user->name,
            'mobile' => $user->mobile,
            'email' => $user->email,
            'wallet' => (int) $user->wallet,
            'blockedWallet' => (int) $user->blocked_wallet,
            'flags' => $this->flags($user),
            'createdAt' => $this->date($jdf, $user->created_at),
            'stats' => [
                'orders' => Order::where('user_id', $user->id)->count(),
                'completed' => Order::where('user_id', $user->id)->where('status', 'complete')->count(),
                'spent' => (int) Order::where('user_id', $user->id)->where('status', 'complete')->sum('price'),
            ],
            'links' => [
                'legacy' => route('admin.user single', $user->id),
                'panel' => route('admin.user panel', $user->id),
            ],
            'orders' => $orders->map(fn ($o) => [
                'id' => $o->id,
                'type' => (string) $o->type,
                'service' => trim((string) $o->service, ' -') ?: null,
                'price' => (int) $o->price,
                'status' => $o->status,
                'createdAt' => $this->date($jdf, $o->created_at),
            ])->values(),
            'payments' => $payments->map(fn ($p) => [
                'id' => $p->id,
                'orderId' => $p->order_id,
                'type' => (string) $p->type,
                'status' => $p->status,
                'gate' => $p->gate,
                'price' => (int) $p->price,
                'note' => $p->help,
                'at' => $this->date($jdf, $p->created_at),
            ])->values(),
            'tickets' => Ticket::where('user_id', $user->id)->orderByDesc('id')->take(20)->get()->map(fn ($t) => [
                'id' => $t->id,
                'subject' => $t->subject,
                'status' => $t->status,
                'statusLabel' => __('all.tickets.' . $t->status) === 'all.tickets.' . $t->status ? $t->status : __('all.tickets.' . $t->status),
                'unread' => (bool) $t->new_for_admin,
                'url' => route('admin.ticket single', $t->id),
                'at' => $this->date($jdf, $t->created_at),
            ])->values(),
            'referrals' => User::where('parent_id', $user->id)->orderByDesc('id')->take(50)->get(['id', 'name', 'mobile', 'created_at'])
                ->map(fn ($r) => ['id' => $r->id, 'name' => $r->name, 'mobile' => $r->mobile, 'createdAt' => $this->date($jdf, $r->created_at)])->values(),
            'history' => $history->map(function ($h) use ($jdf, $admins) {
                return [
                    'id' => $h->id,
                    'changes' => $this->describe(json_decode($h->detail, true) ?: []),
                    'by' => $admins[$h->admin_id] ?? null,
                    'at' => $this->date($jdf, $h->created_at),
                ];
            })->values(),
        ]);
    }

    /** Account switches; 1 means blocked for every one of them. */
    private function flags(User $u): array
    {
        return [
            'loginBlocked' => (int) $u->status === 1,
            'walletBlocked' => (int) $u->wallet_blocked === 1,
            'bankBlocked' => (int) $u->bank_blocked === 1,
            'accountBlocked' => (int) $u->account_blocked === 1,
        ];
    }

    /** Turns a SubmitUserChange history entry into readable lines. Every save records all switches, so only money, password and reason are listed. */
    private function describe(array $d): array
    {
        $lines = [];
        $money = fn ($v) => number_format((int) $v) . ' تومان';
        if (isset($d['wallet increment'])) $lines[] = ['kind' => 'credit', 'text' => 'افزایش کیف پول: ' . $money($d['wallet increment'])];
        if (isset($d['wallet decrement'])) $lines[] = ['kind' => 'debit', 'text' => 'کسر از کیف پول: ' . $money($d['wallet decrement'])];
        if (isset($d['blocked wallet increment'])) $lines[] = ['kind' => 'credit', 'text' => 'افزایش موجودی مسدود: ' . $money($d['blocked wallet increment'])];
        if (isset($d['blocked wallet decrement'])) $lines[] = ['kind' => 'debit', 'text' => 'کسر از موجودی مسدود: ' . $money($d['blocked wallet decrement'])];
        if (isset($d['password'])) $lines[] = ['kind' => 'info', 'text' => 'رمز عبور تغییر کرد'];
        $blocks = array_filter([
            !empty($d['status']) ? 'ورود' : null,
            !empty($d['wallet_blocked']) ? 'خرید با کیف پول' : null,
            !empty($d['bank_blocked']) ? 'خرید با درگاه' : null,
        ]);
        // Entries written elsewhere (a resolved transaction) carry no switches.
        if (array_key_exists('status', $d) || array_key_exists('wallet_blocked', $d) || array_key_exists('bank_blocked', $d)) {
            $lines[] = ['kind' => 'info', 'text' => $blocks ? 'مسدود: ' . implode('، ', $blocks) : 'بدون محدودیت'];
        }
        if (!empty($d['detail'])) $lines[] = ['kind' => 'reason', 'text' => $d['detail']];
        return $lines;
    }

    private function date(Jdf $jdf, $value): ?string
    {
        return $value ? $jdf->jdate('Y/m/d H:i', strtotime((string) $value)) : null;
    }
}
