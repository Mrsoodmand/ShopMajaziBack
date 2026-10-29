<?php

namespace App\Http\Controllers\Admin;

use App\Admin;
use App\Http\Controllers\Controller;
use App\lib\Jdf;
use App\Models\History;
use App\Models\Order;
use App\Models\OrderChat;
use App\Models\OrderItem;
use App\Models\Payment;
use App\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

/**
 * JSON reads for the React orders screens (resources/admin/pages/orders).
 * Writes still go through OrderController (change status, refund, chat), so SMS, history and
 * wallet rules stay in one place.
 */
class OrderApiController extends Controller
{
    /** Statuses an admin has to act on; the list opens on these. */
    const NEEDS_ACTION = ['pending', 'progressing', 'suspended'];

    const PER_PAGE = 30;

    public function index(Request $request)
    {
        $view = $request->input('view', 'action');
        $type = $request->input('type');
        $search = trim((string) $request->input('q', ''));

        $base = Order::query();
        if ($search !== '') {
            $base->where(function ($q) use ($search) {
                if (ctype_digit($search)) {
                    $q->where('id', (int) $search);
                }
                $userIds = User::where('mobile', 'LIKE', '%' . $search . '%')->limit(500)->pluck('id');
                if ($userIds->isNotEmpty()) {
                    $q->orWhereIn('user_id', $userIds);
                }
                if (!ctype_digit($search)) {
                    $q->orWhere('service', 'LIKE', '%' . $search . '%');
                }
            });
        }
        if ($type) {
            $base->where('type', $type);
        }

        $counts = (clone $base)->select('status', DB::raw('count(*) as c'))->groupBy('status')->pluck('c', 'status');
        $viewCounts = [
            'action' => collect(self::NEEDS_ACTION)->sum(fn ($s) => $counts[$s] ?? 0),
            'waiting' => $counts['waiting to pay'] ?? 0,
            'complete' => $counts['complete'] ?? 0,
            'failed' => ($counts['failed'] ?? 0) + ($counts['back money to wallet'] ?? 0),
            'all' => $counts->sum(),
        ];

        $list = (clone $base)->with(['User:id,name,mobile', 'OrderItem:id,order_id,detail']);
        match ($view) {
            'action' => $list->whereIn('status', self::NEEDS_ACTION),
            'waiting' => $list->where('status', 'waiting to pay'),
            'complete' => $list->where('status', 'complete'),
            'failed' => $list->whereIn('status', ['failed', 'back money to wallet']),
            default => null,
        };
        // The queue is worked oldest first; every other view is newest first.
        if ($view === 'action') {
            $list->orderBy('created_at')->orderBy('id');
        } else {
            $list->orderByDesc('id');
        }
        $page = $list->paginate(self::PER_PAGE);

        $unread = OrderChat::whereIn('order_id', $page->pluck('id'))
            ->where('type', 'user')->where('new', true)
            ->select('order_id', DB::raw('count(*) as c'))->groupBy('order_id')->pluck('c', 'order_id');

        $jdf = new Jdf();

        return response()->json([
            'counts' => $viewCounts,
            'page' => $page->currentPage(),
            'lastPage' => $page->lastPage(),
            'total' => $page->total(),
            'orders' => $page->getCollection()->map(fn (Order $o) => [
                'id' => $o->id,
                'type' => (string) $o->type,
                'service' => $this->summary($o),
                'price' => (int) $o->price,
                'status' => $o->status,
                'payStatus' => $o->pay_status,
                'createdAt' => $this->date($jdf, $o->created_at),
                'createdTs' => $o->created_at ? strtotime((string) $o->created_at) : null,
                'customer' => $o->User ? ['id' => $o->User->id, 'name' => $o->User->name, 'mobile' => $o->User->mobile] : null,
                'unread' => (int) ($unread[$o->id] ?? 0),
            ])->values(),
        ]);
    }

    public function show($id)
    {
        $order = Order::with('User')->find($id);
        if (!$order) {
            return response()->json(['error' => ['سفارش پیدا نشد.']], 404);
        }
        $jdf = new Jdf();

        $payments = Payment::where('order_id', $order->id)->orderBy('id')->get();
        // A refund is a type-2 payment whose `error` column holds the refunded payment's id.
        $refundedIds = $payments->where('type', 2)->pluck('error')->map(fn ($v) => (string) $v)->all();
        $mainPayment = $order->pay_id ? Payment::find($order->pay_id) : null;

        $history = History::where('type', 'order')->where('post_id', $order->id)->orderByDesc('id')->get();
        $admins = Admin::whereIn('id', $history->pluck('admin_id')->merge(
            OrderChat::where('order_id', $order->id)->pluck('admin_id'))->filter()->unique())
            ->pluck('full_name', 'id');

        $chats = OrderChat::where('order_id', $order->id)->orderBy('id')->get();
        $unread = $chats->where('type', 'user')->where('new', true)->count();
        // Opening the order counts as reading the customer's messages.
        if ($unread) {
            OrderChat::where('order_id', $order->id)->where('type', 'user')->where('new', true)->update(['new' => false]);
        }

        $user = $order->User;

        return response()->json([
            'id' => $order->id,
            'type' => (string) $order->type,
            'service' => $this->summary($order),
            'price' => (int) $order->price,
            'discountCode' => $order->discount_code,
            'discountPrice' => $order->discount_price ? (int) $order->discount_price : null,
            'status' => $order->status,
            'payStatus' => $order->pay_status,
            'walletRefunded' => (int) $order->wallet_status === 1,
            'expireDate' => $order->expire_date ? $this->date($jdf, $order->expire_date) : null,
            'createdAt' => $this->date($jdf, $order->created_at),
            'legacyUrl' => route('admin.order single', $order->id),
            'customer' => $user ? [
                'id' => $user->id,
                'name' => $user->name,
                'mobile' => $user->mobile,
                'email' => $user->email,
                'wallet' => (int) $user->wallet,
                'url' => route('admin.user single', $user->id),
            ] : null,
            'payment' => $mainPayment ? $this->payment($jdf, $mainPayment, $refundedIds) : null,
            'payments' => $payments->map(fn ($p) => $this->payment($jdf, $p, $refundedIds))->values(),
            'items' => OrderItem::where('order_id', $order->id)->get()->map(fn ($i) => $this->item($order, $i))->values(),
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
            'chats' => $chats->map(fn ($c) => [
                'id' => $c->id,
                'from' => $c->type === 'admin' ? 'admin' : 'customer',
                'text' => $c->text,
                'by' => $c->type === 'admin' ? ($admins[$c->admin_id] ?? null) : null,
                'at' => $this->date($jdf, $c->created_at),
                'unread' => $c->type === 'user' && (bool) $c->new,
            ])->values(),
        ]);
    }

    /** One line naming what was bought: the order service, else the first item plan name. */
    private function summary(Order $order): ?string
    {
        $service = trim((string) $order->service, " -");
        if ($service !== '') {
            return $service;
        }
        $detail = json_decode((string) optional($order->OrderItem->first())->detail, true);
        return is_array($detail) ? ($detail['plan']['plan_name'] ?? null) : null;
    }

    private function payment(Jdf $jdf, Payment $p, array $refundedIds): array
    {
        return [
            'id' => $p->id,
            'type' => (string) $p->type,
            'status' => $p->status,
            'gate' => $p->gate,
            'price' => (int) $p->price,
            'refId' => $p->refid,
            'card' => $p->card,
            'note' => $p->help,
            'refunded' => in_array((string) $p->id, $refundedIds, true),
            'at' => $this->date($jdf, $p->created_at),
        ];
    }

    /** Turns an order item into labelled rows; each product type stores its detail differently. */
    private function item(Order $order, OrderItem $item): array
    {
        $detail = json_decode((string) $item->detail, true);
        $rows = [];
        $title = null;

        switch ((string) $order->type) {
            case '1': // virtual number
                $p = $item->VirtualNumber;
                $title = $p ? trim($p->faName . ' · ' . ($p->countryFaName ?: $p->countryName)) : null;
                $rows[] = ['label' => 'شماره', 'value' => $detail['number'] ?? null, 'copy' => true, 'ltr' => true];
                $rows[] = ['label' => 'کد پیامک', 'value' => $detail['sms'] ?? null, 'copy' => true, 'ltr' => true];
                $rows[] = ['label' => 'شناسه شماره', 'value' => isset($detail['numberId']) ? (string) $detail['numberId'] : null, 'ltr' => true];
                $server = $p ? (\App\Services\VirtualNumberOffers::PROVIDERS[$p->type] ?? null) : null;
                $rows[] = ['label' => 'سرور', 'value' => $server ? "سرور $server · " . \App\Services\VirtualNumberOffers::LABELS[$p->type] : null];
                $numberStatus = $detail['status'] ?? null;
                $statusKey = 'order.status.' . $numberStatus;
                $rows[] = ['label' => 'وضعیت شماره', 'value' => !$numberStatus ? null
                    : (\Illuminate\Support\Facades\Lang::has($statusKey) ? trim(__($statusKey)) : "نامشخص ($numberStatus)")];
                break;
            case '2': // virtual services
            case '7': // foreign accounts
                $title = $detail['plan']['plan_name'] ?? null;
                foreach ((array) $detail as $key => $field) {
                    if ($key === 'plan' || !is_array($field)) {
                        continue;
                    }
                    $value = $field['filed_value'] ?? null;
                    $rows[] = ['label' => $field['filed_type'] ?? $field['filed_name'] ?? 'فیلد', 'value' => is_scalar($value) ? (string) $value : null, 'copy' => true, 'ltr' => true];
                }
                break;
            case '5': // static IP
                $gb = __('order.Volume-Gb.' . $item->detail);
                $rows[] = ['label' => 'حجم', 'value' => is_numeric($gb) && (int) $gb > 0 ? $gb . ' گیگ' : null];
                break;
            default:
                if (is_array($detail)) {
                    foreach ($detail as $key => $value) {
                        if (is_scalar($value)) {
                            $rows[] = ['label' => (string) $key, 'value' => (string) $value, 'ltr' => true];
                        }
                    }
                }
        }

        return [
            'id' => $item->id,
            'title' => $title,
            'price' => (int) $item->price,
            'rows' => $rows,
        ];
    }

    private function date(Jdf $jdf, $value): ?string
    {
        if (!$value) {
            return null;
        }
        return $jdf->jdate('Y/m/d H:i', strtotime((string) $value));
    }
}
