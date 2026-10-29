<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\lib\Jdf;
use App\Models\Comment;
use App\Models\Order;
use App\Models\Payment;
use App\Models\Ticket;
use App\User;
use Carbon\Carbon;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;

/**
 * JSON for the React dashboard (resources/admin/pages/dashboard): what needs a person now (order queue, tickets,
 * payment mismatches, comments) and how the shop is doing (today vs yesterday, last 14 days, sales by service).
 * Each block is only sent to admins allowed to open its section, mirroring the sidebar's permission checks.
 * "Sales" is the price of completed orders.
 */
class DashboardApiController extends Controller
{
    const DAYS = 14;
    const LIST = 6;

    public function __invoke()
    {
        $admin = Auth::guard('admin')->user();
        $isSuper = $admin->hasRole('super-admin');
        $can = fn (string $perm) => $isSuper || $admin->hasAnyPermission([$perm]);
        $jdf = new Jdf();

        $data = [
            'today' => $jdf->jdate('l j F Y', time()),
            'users' => ['total' => User::count(), 'today' => User::whereDate('created_at', Carbon::today())->count()],
        ];

        if ($can('order')) {
            $data += $this->orders($jdf);
        }
        if ($can('tickets-list')) {
            $data['tickets'] = $this->tickets($jdf);
        }
        if ($can('moghayerat')) {
            $data['mismatches'] = $this->mismatches($jdf);
        }
        if ($can('comments-list')) {
            $data['comments'] = $this->comments($jdf);
        }

        return response()->json($data);
    }

    private function orders(Jdf $jdf): array
    {
        $from = Carbon::today()->subDays(self::DAYS - 1);
        $daily = Order::where('status', 'complete')->where('created_at', '>=', $from)
            ->selectRaw('DATE(created_at) d, count(*) c, coalesce(sum(price), 0) s')
            ->groupBy('d')->get()->keyBy('d');

        $days = [];
        for ($i = 0; $i < self::DAYS; $i++) {
            $day = $from->copy()->addDays($i);
            $row = $daily[$day->toDateString()] ?? null;
            $ts = $day->timestamp + 12 * 3600;
            $days[] = [
                'date' => $jdf->jdate('n/j', $ts),
                'weekday' => $jdf->jdate('l', $ts),
                'orders' => (int) ($row->c ?? 0),
                'sales' => (int) ($row->s ?? 0),
            ];
        }

        $byType = Order::where('status', 'complete')->where('created_at', '>=', Carbon::today()->subDays(29))
            ->selectRaw('type, count(*) c, coalesce(sum(price), 0) s')
            ->groupBy('type')->orderByDesc('s')->get()
            ->map(fn ($r) => ['type' => (string) $r->type, 'orders' => (int) $r->c, 'sales' => (int) $r->s])->values();

        $queueCounts = Order::whereIn('status', OrderApiController::NEEDS_ACTION)
            ->select('status', DB::raw('count(*) c'))->groupBy('status')->pluck('c', 'status');
        $oldest = Order::whereIn('status', OrderApiController::NEEDS_ACTION)
            ->with(['User:id,name,mobile', 'OrderItem:id,order_id,detail'])
            ->orderBy('created_at')->orderBy('id')->limit(self::LIST)->get();

        return [
            'days' => $days,
            'byType' => $byType,
            'queue' => [
                'total' => (int) $queueCounts->sum(),
                'counts' => collect(OrderApiController::NEEDS_ACTION)->mapWithKeys(fn ($s) => [$s => (int) ($queueCounts[$s] ?? 0)]),
                'waitingToPay' => Order::where('status', 'waiting to pay')->whereDate('created_at', Carbon::today())->count(),
                'oldest' => $oldest->map(fn (Order $o) => [
                    'id' => $o->id,
                    'type' => (string) $o->type,
                    'service' => $this->summary($o),
                    'price' => (int) $o->price,
                    'status' => $o->status,
                    'createdAt' => $this->date($jdf, $o->created_at),
                    'createdTs' => $o->created_at ? strtotime((string) $o->created_at) : null,
                    'customer' => $o->User ? ['id' => $o->User->id, 'name' => $o->User->name, 'mobile' => $o->User->mobile] : null,
                ])->values(),
            ],
        ];
    }

    private function tickets(Jdf $jdf): array
    {
        $open = Ticket::whereIn('status', ['User-answer', 'User-Ticket', 'progressing']);
        $items = (clone $open)->with('User:id,name,mobile')->orderByDesc('updated_at')->limit(self::LIST)->get();

        return [
            'total' => (clone $open)->count(),
            'items' => $items->map(fn ($t) => [
                'id' => $t->id,
                'subject' => $t->subject,
                'status' => $t->status,
                'unread' => (bool) $t->new_for_admin,
                'customer' => $t->User ? ($t->User->name ?: $t->User->mobile) : null,
                'updatedAt' => $this->date($jdf, $t->updated_at),
                'updatedTs' => $t->updated_at ? strtotime((string) $t->updated_at) : null,
            ])->values(),
            'link' => route('admin.react-app', 'tickets'),
            'itemLink' => route('admin.react-app', 'tickets') . '/',
        ];
    }

    private function mismatches(Jdf $jdf): array
    {
        $items = Payment::where('status', -3)->with('User:id,name,mobile')->orderByDesc('id')->limit(self::LIST)->get();

        return [
            'total' => Payment::where('status', -3)->count(),
            'items' => $items->map(fn (Payment $p) => [
                'id' => $p->id,
                'price' => (int) $p->price,
                'customer' => $p->User ? ($p->User->name ?: $p->User->mobile) : null,
                'userId' => $p->user_id,
                'createdAt' => $this->date($jdf, $p->created_at),
                'createdTs' => $p->created_at ? strtotime((string) $p->created_at) : null,
            ])->values(),
            'link' => route('admin.react-app', 'transactions'), // the review queue; items open /transactions/{id}
        ];
    }

    private function comments(Jdf $jdf): array
    {
        // Older rows mark a comment awaiting review with 0, newer ones with "Pending".
        $pending = Comment::where('parent_id', 0)->whereIn('status', ['0', 'Pending']);
        $items = (clone $pending)->orderByDesc('id')->limit(self::LIST)->get();

        return [
            'total' => (clone $pending)->count(),
            'items' => $items->map(fn (Comment $c) => [
                'id' => $c->id,
                'name' => $c->name,
                'text' => mb_strimwidth(trim(strip_tags((string) $c->text)), 0, 160, '…'),
                'createdAt' => $this->date($jdf, $c->created_at),
                'createdTs' => $c->created_at ? strtotime((string) $c->created_at) : null,
            ])->values(),
            'link' => route('admin.react-app', 'comments'),
        ];
    }

    private function summary(Order $order): ?string
    {
        $service = trim((string) $order->service, " -");
        if ($service !== '') {
            return $service;
        }
        $detail = json_decode((string) optional($order->OrderItem->first())->detail, true);
        return is_array($detail) ? ($detail['plan']['plan_name'] ?? null) : null;
    }

    private function date(Jdf $jdf, $value): ?string
    {
        return $value ? $jdf->jdate('Y/m/d H:i', strtotime((string) $value)) : null;
    }
}
