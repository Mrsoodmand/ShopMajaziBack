<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Admin\Concerns\AdminApi;
use App\Http\Controllers\Controller;
use App\Models\Category;
use App\Models\Ip;
use App\Models\Order;
use App\Models\OrderItem;
use App\Models\Product;
use App\Services\IpServerPanel;
use App\User;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\Rule;

/**
 * Shared static IPs for the React admin, stored as the old admin did:
 *  - a location (country) is a category of type pu-ip-sabet (status 1 active);
 *  - its plans are products of the same type (detail {day, volume, dis, percent, image});
 *  - its servers are `ips` rows of type pu-ip-sabet (detail = 3x-ui panel login, status 1 active, 0 off, 2 full, -1 removed);
 *  - the VLESS clients on a server are `ips` rows of type pu-ip-sabet-user (parent_id = server):
 *    status 0 free, 1 sold and running, -1 expired, -2 ended.
 * Paid orders (type 5) waiting for a free client get one when clients are added (same rule as the old admin).
 */
class IpApiController extends Controller
{
    use AdminApi;

    const TYPE = 'pu-ip-sabet';
    const CLIENT = 'pu-ip-sabet-user';
    const PER_PAGE = 50;
    const CLIENT_STATUS = ['0' => 'free', '1' => 'active', '-1' => 'expired', '-2' => 'ended'];
    const SERVER_STATUS = ['1' => 'active', '0' => 'off', '2' => 'full', '-1' => 'removed'];

    /** The tables this screen works on. The Telegram bot's static IPs (BotIpApiController) swap in its own hegza_* tables. */
    protected string $locations = Category::class;
    protected string $plans = Product::class;
    protected string $ips = Ip::class;
    protected array $clientStatus = self::CLIENT_STATUS;
    /** Paid shop orders of this type get a free client when clients are added; null where another system hands clients out. */
    protected ?int $orderType = 5;
    /** Locations shown on the website need a flag image and SEO text; the bot's are only seen in Telegram. */
    protected bool $webPage = true;

    public function __construct(private IpServerPanel $panel)
    {
    }

    /* ---------- Locations ---------- */

    public function index()
    {
        $locations = $this->locations::where('type', self::TYPE)->orderBy('id')->get();
        $clients = $this->ips::where('type', self::CLIENT)->whereIn('category_id', $locations->pluck('id'))
            ->select('category_id', 'status', DB::raw('count(*) c'))->groupBy('category_id', 'status')->get()->groupBy('category_id');
        $servers = $this->ips::where('type', self::TYPE)->whereIn('category_id', $locations->pluck('id'))
            ->select('category_id', DB::raw('count(*) c'))->groupBy('category_id')->pluck('c', 'category_id');
        $plans = $this->plans::where('type', self::TYPE)->whereIn('category_id', $locations->pluck('id'))
            ->select('category_id', DB::raw('count(*) c'), DB::raw('min(price + 0) low'))->groupBy('category_id')->get()->keyBy('category_id');

        return response()->json([
            'waitingOrders' => $this->orderType ? Order::where('type', $this->orderType)->where('status', 'pending')->count() : 0,
            'locations' => $locations->map(function ($c) use ($clients, $servers, $plans) {
                $byStatus = ($clients[$c->id] ?? collect())->pluck('c', 'status');
                return [
                    'id' => $c->id,
                    'name' => (string) $c->name,
                    'enName' => (string) $c->enName,
                    'slug' => (string) $c->slug,
                    'image' => $this->asset($c->image),
                    'live' => (int) $c->status === 1,
                    'plans' => (int) ($plans[$c->id]->c ?? 0),
                    'fromPrice' => isset($plans[$c->id]) ? (int) $plans[$c->id]->low : null,
                    'servers' => (int) ($servers[$c->id] ?? 0),
                    'free' => (int) ($byStatus[0] ?? 0),
                    'active' => (int) ($byStatus[1] ?? 0),
                ];
            })->values(),
        ]);
    }

    public function show($id)
    {
        $c = $this->locations::where('type', self::TYPE)->find($id);
        if (!$c) {
            return $this->missing('این کشور پیدا نشد.');
        }
        return response()->json(['location' => $this->location($c), 'servers' => $this->servers($c)]);
    }

    public function save(Request $request, $id = null)
    {
        $c = $id ? $this->locations::where('type', self::TYPE)->find($id) : new $this->locations();
        if (!$c) {
            return $this->missing('این کشور پیدا نشد.');
        }
        $data = $request->validate([
            'name' => 'required|string|max:255',
            'enName' => 'required|string|max:255',
            'slug' => ['required', 'string', 'max:190', 'regex:/^[a-z0-9]+(-[a-z0-9]+)*$/',
                Rule::unique((new $this->locations())->getTable(), 'slug')->where('type', self::TYPE)->ignore($c->id)],
            'live' => 'boolean',
            'image' => [$this->webPage ? 'required' : 'nullable', 'string', 'max:500'],
            'metaTitle' => [$this->webPage ? 'required' : 'nullable', 'string', 'max:255'],
            'metaDesc' => [$this->webPage ? 'required' : 'nullable', 'string', 'max:1000'],
            'plans' => 'array',
            'plans.*.id' => 'nullable|integer',
            'plans.*.name' => 'required|string|max:255',
            'plans.*.price' => 'required|integer|min:0',
            'plans.*.days' => 'required|integer|min:1|max:3650',
            'plans.*.volume' => 'required|integer|min:0',
            'plans.*.salePrice' => 'nullable|integer|min:0',
            'plans.*.percent' => 'nullable|integer|min:0|max:100',
            'plans.*.image' => 'nullable|string|max:500',
        ], $this->messages() + ['image.required' => 'تصویر (پرچم) کشور را انتخاب کنید.']);

        DB::transaction(function () use ($c, $data) {
            $c->forceFill([
                'type' => self::TYPE,
                'name' => $data['name'],
                'enName' => $data['enName'],
                'slug' => $data['slug'],
                'status' => ($data['live'] ?? true) ? 1 : 0,
                'image' => $this->stored((string) ($data['image'] ?? '')),
                'meta_title' => $data['metaTitle'] ?? null,
                'meta_desc' => $data['metaDesc'] ?? null,
            ])->save();

            $keep = [];
            foreach ($data['plans'] ?? [] as $row) {
                $plan = !empty($row['id']) ? $this->plans::where('type', self::TYPE)->where('category_id', $c->id)->find($row['id']) : null;
                $plan ??= new $this->plans();
                $plan->forceFill([
                    'type' => self::TYPE,
                    'category_id' => $c->id,
                    'faName' => $row['name'],
                    'price' => (string) $row['price'],
                    'detail' => json_encode([
                        'day' => (string) $row['days'],
                        'volume' => (string) $row['volume'],
                        'dis' => (string) ($row['salePrice'] ?? 0),
                        'percent' => (string) ($row['percent'] ?? 0),
                        'image' => $this->stored((string) ($row['image'] ?? '')),
                    ], JSON_UNESCAPED_UNICODE),
                ])->save();
                $keep[] = $plan->id;
            }
            // A plan someone already bought stays (their client row points at it); only unsold plans go.
            $sold = $this->ips::where('category_id', $c->id)->whereNotNull('product_id')->pluck('product_id')->unique();
            $this->plans::where('type', self::TYPE)->where('category_id', $c->id)->whereNotIn('id', $keep)->whereNotIn('id', $sold)->delete();
        });
        $delivered = $this->fulfillWaitingOrders($c);

        return response()->json([
            'success' => ($id ? 'تغییرات ذخیره شد.' : 'کشور ساخته شد.') . ($delivered ? " $delivered سفارش منتظر تحویل شد." : ''),
            'id' => $c->id,
        ]);
    }

    /* ---------- Servers ---------- */

    public function saveServer(Request $request, $locationId, $id = null)
    {
        $c = $this->locations::where('type', self::TYPE)->find($locationId);
        $server = $id ? $this->ips::where('type', self::TYPE)->where('category_id', $locationId)->find($id) : new $this->ips();
        if (!$c || !$server) {
            return $this->missing('این سرور پیدا نشد.');
        }
        $data = $request->validate([
            'name' => ['required', 'string', 'max:50', 'regex:/^[A-Za-z0-9_-]+$/'],
            'address' => 'required|string|max:255',
            'ip' => 'nullable|string|max:255',
            'panelUrl' => ['required', 'string', 'max:255', 'regex:~^https?://~i'],
            'panelPort' => 'required|integer|min:1|max:65535',
            'username' => 'required|string|max:255',
            'password' => [$id ? 'nullable' : 'required', 'string', 'max:255'],
            'inbound' => 'required|integer|min:1',
            'inboundPort' => 'required|integer|min:1|max:65535',
            'status' => ['required', Rule::in(array_values(self::SERVER_STATUS))],
        ], $this->messages() + [
            'name.regex' => 'فقط حروف انگلیسی، عدد، - و _ (نام کلاینت‌ها از آن ساخته می‌شود، مثل de1).',
            'panelUrl.regex' => 'آدرس را با http:// یا https:// شروع کنید.',
        ]);

        $old = $this->decode($server->detail);
        $detail = [
            'ip_address' => $data['address'],
            'ip_domain' => $data['ip'] ?? '',
            'ip_port' => (string) $data['panelPort'],
            'ip_user' => $data['username'],
            'ip_password' => ($data['password'] ?? '') !== '' ? $data['password'] : ($old['ip_password'] ?? ''),
            'ip_status' => (string) array_search($data['status'], self::SERVER_STATUS, true),
            'ip_inbound' => (string) $data['inbound'],
            'ip_limit' => '0',
            'ip_api_domain' => rtrim($data['panelUrl'], '/'),
            'ip_name' => $data['name'],
            'port_inbound' => (string) $data['inboundPort'],
            'session' => $old['session'] ?? null,
        ];
        // An active server must accept our login; a switched-off one is saved as it is.
        if ($data['status'] === 'active') {
            $session = $this->panel->login($detail);
            if (!$session) {
                return response()->json(['message' => 'ورود ناموفق', 'errors' => ['panelUrl' => ['ورود به پنل سرور انجام نشد. آدرس، پورت، نام کاربری و رمز را بررسی کنید.']]], 422);
            }
            $detail['session'] = $session;
        }
        $server->forceFill([
            'type' => self::TYPE,
            'category_id' => $c->id,
            'detail' => json_encode($detail, JSON_UNESCAPED_UNICODE),
            'status' => (int) $detail['ip_status'],
            'limit' => 0,
        ])->save();

        return response()->json(['success' => $id ? 'سرور ذخیره شد.' : 'سرور اضافه شد.', 'id' => $server->id]);
    }

    /** Makes `count` new clients on the server's inbound (named like the old admin: name-N), then serves waiting orders. */
    public function addClients(Request $request, $id)
    {
        $data = $request->validate(['count' => 'required|integer|min:1|max:50'], $this->messages());
        $server = $this->ips::where('type', self::TYPE)->find($id);
        if (!$server) {
            return $this->missing('این سرور پیدا نشد.');
        }
        $detail = $this->decode($server->detail);
        $session = $this->panel->login($detail);
        if (!$session) {
            return $this->refuse('ورود به پنل سرور انجام نشد. اطلاعات سرور را بررسی کنید.');
        }
        $detail['session'] = $session;
        $server->detail = json_encode($detail, JSON_UNESCAPED_UNICODE);
        $server->save();

        $made = 0;
        $next = $this->ips::where('type', self::CLIENT)->count() + 1;
        for ($i = 0; $i < $data['count']; $i++, $next++) {
            $name = ($detail['ip_name'] ?? 'ip') . '-' . $next;
            $client = $this->panel->addClient($detail, $session, $name);
            if (!$client) {
                break;
            }
            $row = new $this->ips();
            $row->forceFill([
                'type' => self::CLIENT,
                'status' => 0,
                'parent_id' => $server->id,
                'category_id' => $server->category_id,
                'UID' => $client['uid'],
                'detail' => json_encode(['user_port' => $detail['ip_port'] ?? '', 'user_remark' => $name, 'user_code' => $client['code']], JSON_UNESCAPED_UNICODE),
                'limit' => 0,
            ])->save();
            $made++;
        }
        $delivered = $made ? $this->fulfillWaitingOrders($this->locations::find($server->category_id)) : 0;
        if (!$made) {
            return $this->refuse('پنل سرور کلاینت جدید نساخت. دوباره تلاش کنید یا پنل را بررسی کنید.');
        }
        $msg = "$made کلاینت ساخته شد" . ($made < $data['count'] ? ' (بقیه را پنل نساخت)' : '') . '.';

        return response()->json(['success' => $msg . ($delivered ? " $delivered سفارش منتظر تحویل شد." : ''), 'made' => $made, 'delivered' => $delivered]);
    }

    /* ---------- Clients ---------- */

    public function clients(Request $request, $id)
    {
        $status = $request->input('status', 'all');
        $search = trim((string) $request->input('q', ''));
        $base = $this->ips::where('type', self::CLIENT)->where('category_id', $id);
        if ($request->filled('server')) {
            $base->where('parent_id', $request->integer('server'));
        }
        if ($search !== '') {
            $base->where(function ($q) use ($search) {
                $q->where('detail', 'LIKE', "%$search%");
                if (ctype_digit($search)) {
                    $q->orWhere('order_id', (int) $search);
                }
            });
        }
        $counts = ['all' => (clone $base)->count()];
        foreach ($this->clientStatus as $stored => $key) {
            $counts[$key] = (clone $base)->where('status', $stored)->count();
        }
        $list = clone $base;
        if (($code = array_search($status, $this->clientStatus, true)) !== false) {
            $list->where('status', $code);
        }
        $page = $list->orderByDesc('id')->paginate(self::PER_PAGE);
        $users = $this->buyers($page->getCollection()->pluck('user_id')->filter()->unique()->values()->all());
        $plans = $this->plans::whereIn('id', $page->getCollection()->pluck('product_id')->filter())->pluck('faName', 'id');

        return response()->json($this->pageMeta($page) + [
            'counts' => $counts,
            'clients' => $page->getCollection()->map(function ($ip) use ($users, $plans) {
                $d = $this->decode($ip->detail);
                return [
                    'id' => $ip->id,
                    'name' => (string) ($d['user_remark'] ?? ''),
                    'code' => (string) ($d['user_code'] ?? ''),
                    'status' => $this->clientStatus[(string) $ip->status] ?? 'ended',
                    'serverId' => $ip->parent_id ? (int) $ip->parent_id : null,
                    'orderId' => $ip->order_id ? (int) $ip->order_id : null,
                    'plan' => $plans[$ip->product_id] ?? null,
                    'volume' => $ip->volume !== null ? (int) $ip->volume : null,
                    'expiresAt' => $this->jdate($ip->expire_date, 'Y/m/d'),
                    'user' => $users[$ip->user_id] ?? null,
                ];
            })->values(),
        ]);
    }

    /** Removes a free (never sold) client from the list. The 3x-ui panel keeps it until removed there. */
    public function deleteClient($id)
    {
        $ip = $this->ips::where('type', self::CLIENT)->find($id);
        if (!$ip) {
            return $this->missing('این کلاینت پیدا نشد.');
        }
        if ((int) $ip->status !== 0 || $ip->order_id) {
            return $this->refuse('فقط کلاینت آزاد (فروخته‌نشده) حذف می‌شود.');
        }
        $ip->delete();
        return response()->json(['success' => 'کلاینت از فهرست حذف شد. آن را در پنل سرور هم حذف کنید.']);
    }

    public function upload(Request $request)
    {
        return $this->storeImage($request, 'ip', svg: true);
    }

    /* ---------- Helpers ---------- */

    /** Who bought each client, keyed by user id: {id, name, mobile}. */
    protected function buyers(array $ids): array
    {
        return User::whereIn('id', $ids)->get(['id', 'name', 'mobile'])
            ->mapWithKeys(fn ($u) => [$u->id => ['id' => $u->id, 'name' => $u->name, 'mobile' => $u->mobile]])->all();
    }

    private function location($c): array
    {
        return [
            'id' => $c->id,
            'name' => (string) $c->name,
            'enName' => (string) $c->enName,
            'slug' => (string) $c->slug,
            'live' => (int) $c->status === 1,
            'image' => $this->asset($c->image) ?? '',
            'metaTitle' => (string) $c->meta_title,
            'metaDesc' => (string) $c->meta_desc,
            'plans' => $this->plans::where('type', self::TYPE)->where('category_id', $c->id)->orderBy('id')->get()->map(function ($p) {
                $d = $this->decode($p->detail);
                return [
                    'id' => $p->id,
                    'name' => (string) $p->faName,
                    'price' => (string) (int) $p->price,
                    'days' => (string) ($d['day'] ?? ''),
                    'volume' => (string) ($d['volume'] ?? ''),
                    'salePrice' => (string) ($d['dis'] ?? ''),
                    'percent' => (string) ($d['percent'] ?? ''),
                    'image' => $this->asset($d['image'] ?? null) ?? '',
                ];
            })->values(),
        ];
    }

    /** Saves an image path the way the old admin did: site-relative without the leading slash ("image/svg/de.svg"). */
    private function stored(string $path): string
    {
        $path = GenerateImageUrl($path);
        return preg_match('~^(https?:)?//~i', $path) ? $path : ltrim($path, '/');
    }

    private function servers($c): array
    {
        $servers = $this->ips::where('type', self::TYPE)->where('category_id', $c->id)->orderBy('id')->get();
        $counts = $this->ips::where('type', self::CLIENT)->whereIn('parent_id', $servers->pluck('id'))
            ->select('parent_id', 'status', DB::raw('count(*) c'))->groupBy('parent_id', 'status')->get()->groupBy('parent_id');

        return $servers->map(function ($s) use ($counts) {
            $d = $this->decode($s->detail);
            $by = ($counts[$s->id] ?? collect())->pluck('c', 'status');
            return [
                'id' => $s->id,
                'name' => (string) ($d['ip_name'] ?? ''),
                'address' => (string) ($d['ip_address'] ?? ''),
                'ip' => (string) ($d['ip_domain'] ?? ''),
                'panelUrl' => (string) ($d['ip_api_domain'] ?? ''),
                'panelPort' => (string) ($d['ip_port'] ?? ''),
                'username' => (string) ($d['ip_user'] ?? ''),
                'hasPassword' => ($d['ip_password'] ?? '') !== '',
                'inbound' => (string) ($d['ip_inbound'] ?? ''),
                'inboundPort' => (string) ($d['port_inbound'] ?? ''),
                'status' => self::SERVER_STATUS[(string) $s->status] ?? 'off',
                'free' => (int) ($by[0] ?? 0),
                'active' => (int) ($by[1] ?? 0),
                'total' => (int) $by->sum(),
            ];
        })->values()->all();
    }

    /** Stored images are relative ("image/svg/de.svg") or full links; the admin shows them from the site root. */
    private function asset(?string $path): ?string
    {
        if (!$path) {
            return null;
        }
        return preg_match('~^(https?:)?//|^/~', $path) ? $path : '/' . $path;
    }

    /**
     * Gives free clients of this location to paid orders (type 5, pending) for one of its plans, oldest first:
     * expiry = now + plan days, volume = plan volume + the extra volume bought (order.Volume-Gb).
     */
    private function fulfillWaitingOrders($c): int
    {
        if (!$c || !$this->orderType) {
            return 0;
        }
        $plans = $this->plans::where('type', self::TYPE)->where('category_id', $c->id)->get()->keyBy('id');
        $done = 0;
        foreach (Order::where('type', $this->orderType)->where('status', 'pending')->orderBy('id')->get() as $order) {
            $item = OrderItem::where('order_id', $order->id)->first();
            if (!$item || !isset($plans[$item->product_id])) {
                continue;
            }
            $given = DB::transaction(function () use ($order, $item, $plans, $c) {
                $free = $this->ips::where('type', self::CLIENT)->where('category_id', $c->id)->where('status', 0)->whereNull('order_id')
                    ->orderBy('id')->lockForUpdate()->first();
                if (!$free) {
                    return false;
                }
                $plan = $this->decode($plans[$item->product_id]->detail);
                $extra = (int) __('order.Volume-Gb.' . $item->detail);
                $free->forceFill([
                    'order_id' => $order->id,
                    'user_id' => $order->user_id,
                    'status' => 1,
                    'volume' => (int) ($plan['volume'] ?? 0) + $extra,
                    'expire_date' => Carbon::now()->addDays((int) ($plan['day'] ?? 30)),
                    'product_id' => $item->product_id,
                ])->save();
                $order->status = 'complete';
                $order->save();
                return true;
            });
            if (!$given) {
                break; // no free client left here
            }
            $done++;
        }
        return $done;
    }
}
