<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Admin\Concerns\AdminApi;
use App\Http\Controllers\Controller;
use App\Models\HegzaCountry;
use App\Models\HegzaHistory;
use App\Models\HegzaIp;
use App\Models\HegzaOrder;
use App\Models\HegzaPayment;
use App\Models\HegzaProduct;
use App\Models\HegzaUser;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Validation\Rule;

/**
 * Static IP orders placed through the Telegram bot (hegza_orders).
 * Status as the bot writes it: 0 waiting for payment, 1 running, 2 completed, -2 suspended (not renewed in time),
 * -1 ended or cancelled. Changing it here changes only the order record, as the old admin did; the client on the
 * server panel is left as it is. Each change is logged in hegza_history (type change-order-status).
 */
class BotOrderApiController extends Controller
{
    use AdminApi;

    const PER_PAGE = 40;
    const STATUS = ['0' => 'waiting', '1' => 'active', '2' => 'done', '-2' => 'suspended', '-1' => 'ended'];

    public function index(Request $request)
    {
        $q = trim((string) $request->input('q', ''));
        $base = HegzaOrder::query();
        if ($q !== '') {
            $term = ltrim($q, '@');
            $users = HegzaUser::where('username', 'LIKE', "%$term%")->orWhere('user_id', $term)->pluck('id');
            $base->where(function ($w) use ($term, $users) {
                $w->whereIn('user_id', $users);
                if (ctype_digit($term)) {
                    $w->orWhere('id', (int) $term);
                }
            });
        }
        if ($request->filled('user')) {
            $base->where('user_id', $request->integer('user'));
        }
        $counts = ['all' => (clone $base)->count()];
        foreach (self::STATUS as $code => $key) {
            $counts[$key] = (clone $base)->where('status', $code)->count();
        }
        $list = clone $base;
        if (($code = array_search($request->input('status', 'all'), self::STATUS, true)) !== false) {
            $list->where('status', $code);
        }
        $page = $list->orderByDesc('id')->paginate(self::PER_PAGE);
        $users = HegzaUser::whereIn('id', $page->getCollection()->pluck('user_id'))->get(['id', 'username', 'user_id', 'name'])->keyBy('id');

        return response()->json($this->pageMeta($page) + [
            'counts' => $counts,
            'orders' => $page->getCollection()->map(fn (HegzaOrder $o) => self::summary($o) + [
                'user' => ($u = $users[$o->user_id] ?? null) ? self::user($u) : null,
            ])->values(),
        ]);
    }

    public function show($id)
    {
        $o = HegzaOrder::find($id);
        if (!$o) {
            return $this->missing('این سفارش ربات پیدا نشد.');
        }
        $u = HegzaUser::find($o->user_id);
        $ip = HegzaIp::where('type', 'pu-ip-sabet-user')->where('order_id', $o->id)->orderByDesc('id')->first();
        $server = $ip?->parent_id ? HegzaIp::find($ip->parent_id) : null;
        $d = $this->decode($ip?->detail);

        return response()->json([
            'order' => self::summary($o) + [
                'country' => ($c = HegzaCountry::find($o->country_id)) ? (string) $c->name : null,
                'plan' => ($p = HegzaProduct::find($o->service_id)) ? (string) $p->faName : null,
                'volume' => (int) $o->total_bandwidth,
                'used' => (int) $o->bandwidth,
                'expiresAt' => self::date($o->expire_at),
                'note' => (string) $o->help,
                'user' => $u ? self::user($u) : null,
            ],
            'client' => $ip ? [
                'id' => $ip->id,
                'name' => (string) ($d['user_remark'] ?? ''),
                'code' => (string) ($d['user_code'] ?? ''),
                'server' => $server ? (string) ($this->decode($server->detail)['ip_name'] ?? '') : null,
                'locationId' => $ip->category_id ? (int) $ip->category_id : null,
                'volume' => $ip->volume !== null ? (int) $ip->volume : null,
                'expiresAt' => self::date($ip->expire_date),
            ] : null,
            'payments' => HegzaPayment::where('order_id', $o->id)->orderByDesc('id')->get()->map(fn ($p) => BotUserApiController::payment($p))->values(),
            'history' => HegzaHistory::where('post_id', $o->id)->where('type', 'change-order-status')->orderByDesc('id')->get()->map(fn ($h) => [
                'id' => $h->id,
                'text' => (string) ($this->decode($h->detail)['msg'] ?? ''),
                'at' => $this->jdate($h->created_at),
            ])->values(),
        ]);
    }

    public function setStatus(Request $request, $id)
    {
        $o = HegzaOrder::find($id);
        if (!$o) {
            return $this->missing('این سفارش ربات پیدا نشد.');
        }
        $data = $request->validate(['status' => ['required', Rule::in(array_values(self::STATUS))]], $this->messages());
        $code = (int) array_search($data['status'], self::STATUS, true);
        $old = (int) $o->status;
        if ($old === $code) {
            return response()->json(['success' => 'وضعیت همان بود.']);
        }
        $o->status = $code;
        $o->save();

        $h = new HegzaHistory();
        $h->type = 'change-order-status';
        $h->post_id = $o->id;
        $h->user_id = $o->user_id;
        $h->detail = json_encode(['msg' => "تغییر وضعیت سفارش از $old به $code"]);
        $h->admin_id = Auth::guard('admin')->id();
        $h->save();

        return response()->json(['success' => 'وضعیت سفارش عوض شد.']);
    }

    public static function summary(HegzaOrder $o): array
    {
        return [
            'id' => $o->id,
            'title' => (string) $o->name,
            'status' => self::STATUS[(string) $o->status] ?? 'waiting',
            'createdAt' => BotUserApiController::when($o->created_at),
        ];
    }

    private static function user(HegzaUser $u): array
    {
        return ['id' => $u->id, 'username' => (string) $u->username, 'telegramId' => (string) $u->user_id, 'name' => BotUserApiController::text($u->name)];
    }

    private static function date($value): ?string
    {
        return BotUserApiController::when($value, 'Y/m/d');
    }
}
