<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Admin\Concerns\AdminApi;
use App\Http\Controllers\Controller;
use App\Models\Order;
use App\Models\OrderCharge;
use App\Models\Plan;
use App\Models\Product;
use App\Services\AccountOrders;
use App\Services\OrderCharges;
use App\Services\Pricing;
use App\Services\VirtualServiceOrders;
use App\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

/**
 * React admin › orders: make an order for a customer (catalog plans or free-text lines in Toman or dollars, paid now
 * or paid by the customer), and add or drop extra amounts (tax, VAT…) on any order. Money rules: App\Services\OrderCharges.
 */
class OrderMakeApiController extends Controller
{
    use AdminApi;

    /* Services and subscriptions with today's plan prices, and the dollar rate free-text dollar lines use. */
    public function catalog(Request $request)
    {
        $search = trim((string) $request->input('q', ''));
        $products = Product::whereIn('type', ['service', 'accounts'])->where('status', 1)
            ->when($search !== '', fn ($q) => $q->where('faName', 'LIKE', '%' . $search . '%'))
            ->orderBy('faName')->limit(40)->get();
        $plans = Plan::whereIn('product_id', $products->pluck('id'))->where('status', 1)->orderBy('id')->get()->groupBy('product_id');
        $services = new VirtualServiceOrders();

        return response()->json([
            'usdRate' => (int) round(Pricing::servicesRate()),
            'products' => $products->map(function (Product $p) use ($plans, $services) {
                $info = $this->decode($p->detail);
                $name = (string) ($info['name'] ?? $p->faName);
                if ($p->type === 'service') {
                    $count = VirtualServiceOrders::countPricing($p, $services->rate());
                    $list = ($plans[$p->id] ?? collect())->map(fn ($pl) => [
                        'id' => $pl->id, 'name' => (string) $pl->name,
                        'price' => VirtualServiceOrders::dollarPrice($p->price, (int) $pl->count, Pricing::planRate($pl, $p)),
                    ]);
                } else {
                    $count = null;
                    $list = ($plans[$p->id] ?? collect())->where('type', 'accounts')->map(fn ($pl) => [
                        'id' => $pl->id, 'name' => (string) $pl->name,
                        'price' => AccountOrders::planPrices($pl, $info, Pricing::planRate($pl, $p))[0],
                    ]);
                }
                return [
                    'id' => $p->id,
                    'name' => $name,
                    'kind' => $p->type === 'service' ? 'service' : 'subscription',
                    'plans' => $list->filter(fn ($x) => $x['price'] > 0)->values(),
                    'count' => $count, // {step, price per step, min, max} for any-amount services
                ];
            })->filter(fn ($p) => count($p['plans']) || $p['count'])->values(),
        ]);
    }

    /*
     * {user_id, paid: bool, notify: bool, note?, lines: [{product_id, plan_id} | {product_id, count} | {title, note?, currency: toman|usd, amount}]}
     */
    public function store(Request $request, OrderCharges $charges)
    {
        $user = User::find((int) $request->input('user_id'));
        if (!$user) {
            return $this->refuse('مشتری را انتخاب کنید.');
        }
        $raw = array_slice((array) $request->input('lines', []), 0, OrderCharges::MAX_LINES);
        if (!$raw) {
            return $this->refuse('حداقل یک ردیف به سفارش اضافه کنید.');
        }
        $lines = [];
        foreach ($raw as $n => $line) {
            [$built, $error] = $this->line((array) $line);
            if ($error) {
                return $this->refuse('ردیف ' . ($n + 1) . ': ' . $error);
            }
            $lines[] = $built;
        }
        $admin = Auth::guard('admin')->user();
        $paid = $request->boolean('paid');
        $order = $charges->make($user, $lines, $paid, (int) $admin->id, (string) ($admin->full_name ?? $admin->name ?? ''), mb_substr(trim(strip_tags((string) $request->input('note'))), 0, 300));
        if ($request->boolean('notify', true)) {
            OrderCharges::sms($order, $paid ? 'ثبت شد' : 'در انتظار پرداخت');
        }
        return response()->json(['success' => 'سفارش ثبت شد.', 'id' => $order->id]);
    }

    /* An extra amount on an order: {title, currency: toman|usd, amount, notify}. */
    public function charge(Request $request, $id, OrderCharges $charges)
    {
        $order = Order::find($id);
        if (!$order) {
            return $this->missing('سفارش پیدا نشد.');
        }
        if (in_array($order->status, ['failed', 'back money to wallet'], true)) {
            return $this->refuse('به سفارش لغوشده نمی‌توان پرداخت اضافه کرد.');
        }
        $title = mb_substr(trim(strip_tags((string) $request->input('title'))), 0, 190);
        if ($title === '') {
            return $this->refuse('بابت چه چیزی است؟ (مثلا مالیات بر ارزش افزوده)');
        }
        [$price, , , $error] = $this->money((string) $request->input('currency'), $request->input('amount'));
        if ($error) {
            return $this->refuse($error);
        }
        $charges->charge($order, $title, $price, (int) Auth::guard('admin')->id());
        if ($request->boolean('notify', true)) {
            OrderCharges::sms($order, 'نیاز به پرداخت ' . number_format($price) . ' تومان');
        }
        return response()->json(['success' => 'پرداخت اضافه ثبت شد و مشتری می‌تواند آن را پرداخت کند.']);
    }

    public function drop($id, OrderCharges $charges)
    {
        $charge = OrderCharge::find($id);
        if (!$charge || $charge->status !== 'unpaid') {
            return $this->refuse('فقط پرداخت‌های پرداخت‌نشده قابل حذف هستند.');
        }
        $charges->drop($charge, (int) Auth::guard('admin')->id());
        return response()->json(['success' => $charge->kind === 'order' ? 'سفارش لغو شد.' : 'پرداخت اضافه حذف شد.']);
    }

    /* ---------- Lines ---------- */

    /** One line priced: [line, error]. */
    private function line(array $line): array
    {
        if (!empty($line['product_id'])) {
            $product = Product::whereIn('type', ['service', 'accounts'])->where('status', 1)->find((int) $line['product_id']);
            if (!$product) {
                return [null, 'این محصول در دسترس نیست.'];
            }
            $info = $this->decode($product->detail);
            $name = (string) ($info['name'] ?? $product->faName);
            if (!empty($line['count']) && $product->type === 'service') {
                $pricing = VirtualServiceOrders::countPricing($product, Pricing::servicesRate());
                $count = (int) $line['count'];
                if (!$pricing || $count < $pricing['min'] || $count > $pricing['max'] || $count % $pricing['step']) {
                    return [null, 'تعداد مجاز نیست.'];
                }
                $price = intdiv($count, $pricing['step']) * $pricing['price'];
                $title = $name . ' — ' . number_format($count) . ' عدد';
            } else {
                $plan = Plan::where('product_id', $product->id)->where('status', 1)->find((int) ($line['plan_id'] ?? 0));
                if (!$plan) {
                    return [null, 'پلن را انتخاب کنید.'];
                }
                $price = $product->type === 'service'
                    ? VirtualServiceOrders::dollarPrice($product->price, (int) $plan->count, Pricing::planRate($plan, $product))
                    : AccountOrders::planPrices($plan, $info, Pricing::planRate($plan, $product))[0];
                $title = $name . ' — ' . $plan->name;
                $count = null;
            }
            if ($price <= 0) {
                return [null, 'قیمت این محصول مشخص نیست.'];
            }
            return [[
                'product_id' => $product->id, 'plan_id' => $plan->id ?? null, 'count' => $count,
                'title' => $title, 'note' => $this->note($line), 'currency' => 'toman', 'amount' => $price, 'price' => $price,
            ], null];
        }

        $title = mb_substr(trim(strip_tags((string) ($line['title'] ?? ''))), 0, 150);
        if ($title === '') {
            return [null, 'شرح خدمت را بنویسید.'];
        }
        [$price, $currency, $amount, $error] = $this->money((string) ($line['currency'] ?? ''), $line['amount'] ?? null);
        if ($error) {
            return [null, $error];
        }
        return [[
            'title' => $title, 'note' => $this->note($line), 'currency' => $currency, 'amount' => $amount, 'price' => $price,
            'rate' => $currency === 'usd' ? (int) round(Pricing::servicesRate()) : null,
        ], null];
    }

    /** [toman, currency, amount as typed, error]. Dollars use today's services rate (market dollar + its profit). */
    private function money(string $currency, $amount): array
    {
        $currency = $currency === 'usd' ? 'usd' : 'toman';
        $value = (float) str_replace([',', '٬', ' '], '', $this->westernDigits((string) $amount));
        if ($value <= 0) {
            return [0, $currency, 0, 'مبلغ را وارد کنید.'];
        }
        if ($currency === 'usd') {
            $rate = (int) round(Pricing::servicesRate()); // the rate the form shows and the order item keeps
            if ($rate <= 0) {
                return [0, $currency, $value, 'نرخ دلار در دسترس نیست.'];
            }
            $price = (int) (ceil($value * $rate / 1000) * 1000); // rounded up to a thousand Toman
        } else {
            $price = (int) round($value);
        }
        if ($price < 1000 || $price > OrderCharges::MAX_PRICE) {
            return [0, $currency, $value, 'مبلغ باید بین ۱٬۰۰۰ تا ۵۰۰٬۰۰۰٬۰۰۰ تومان باشد.'];
        }
        return [$price, $currency, round($value, 2), null];
    }

    private function note(array $line): ?string
    {
        $note = mb_substr(trim(strip_tags((string) ($line['note'] ?? ''))), 0, 500);
        return $note === '' ? null : $note;
    }
}
