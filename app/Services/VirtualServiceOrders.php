<?php

namespace App\Services;

use App\Models\Category;
use App\Models\Currency;
use App\Models\Order;
use App\Models\OrderItem;
use App\Models\Payment;
use App\Models\Plan;
use App\Models\Product;
use Carbon\Carbon;
use Illuminate\Support\Facades\DB;

/*
 * Social media services (members, views, likes…) bought on the Next.js storefront. Same orders as the old site's
 * v2\OrderController::SubmitVirtualService: order type 2 with one item holding the buyer's answers to the service's
 * fields and the plan (or the chosen count), plus a payment waiting for wallet or bank. Paying reuses
 * Payment\WalletController / CallBackController (status → pending, Telegram notice), and the order then waits in
 * admin › orders until support delivers it.
 */
class VirtualServiceOrders
{
    public const TYPE = 2;

    // The services rate: dollars → toman, with the margin set in admin › currencies (#20).
    public function rate(): float
    {
        $Currency = Currency::find(20);
        return $Currency ? (float)CalculatorSellPrice($Currency) : 0.0;
    }

    // Toman for `count` of a service priced in dollars per 1,000, rounded like every price the site shows.
    public static function dollarPrice($DollarsPer1000, int $Count, float $Rate): int
    {
        $Toman = floatval($DollarsPer1000 / 1000) * $Count * $Rate;
        return (int)str_replace(',', '', CovertVirtualNumberPrice(number_format($Toman)));
    }

    public const STEP = 500;

    /*
     * Any-amount pricing, or null for a service sold in packages (plans):
     * {step, price: toman per step, min, max}. The customer picks a multiple of `step` between min and max and
     * pays (count ÷ step) × price, so the page and the charge always agree.
     *  - price_per_500: dollars per 500 (React admin), converted at the services rate;
     *  - price_per_100: toman per 100, set by the old admin pages (no service uses it now).
     */
    public static function countPricing(Product $Product, float $Rate): ?array
    {
        if ((float)$Product->price_per_500 > 0) {
            $Step = self::STEP;
            $Price = $Rate > 0 ? self::dollarPrice((float)$Product->price_per_500 * 1000 / $Step, $Step, $Rate) : 0;
        } elseif ((int)$Product->price_per_100 > 0) {
            $Step = 100;
            $Price = (int)$Product->price_per_100;
        } else {
            return null;
        }
        [$Min, $Max] = self::countRange($Product->min_count, $Product->max_count, $Step);
        return ['step' => $Step, 'price' => $Price, 'min' => $Min, 'max' => $Max];
    }

    // min / max as multiples of the step (old rows hold 100 / 10,000).
    public static function countRange($Min, $Max, int $Step = self::STEP): array
    {
        $Min = max($Step, (int)ceil(((int)$Min ?: $Step) / $Step) * $Step);
        $Max = max($Min, intdiv((int)$Max ?: 10000, $Step) * $Step);
        return [$Min, $Max];
    }

    /*
     * What the buyer asked for, checked against the service as the admin set it up:
     * {service_id, plan_id} or {service_id, count}, `fields` {name: value} and the `price` the page showed.
     * @return array{0: ?array, 1: ?array} [spec, error]; error = [message, code, status, data]
     */
    public function spec(array $Input): array
    {
        $Product = Product::where('id', intval($Input['service_id'] ?? 0))->where('type', 'service')->where('status', 1)->first();
        $Platform = $Product ? Category::where('id', $Product->country_id)->where('type', 'product')->first() : null;
        if (is_null($Platform) || (int)$Platform->status === 1) {
            return [null, ['این سرویس در حال حاضر در دسترس نیست.', 'service_unavailable', 404, []]];
        }

        $Plan = null;
        if ($Pricing = self::countPricing($Product, $this->rate())) {
            ['step' => $Step, 'min' => $Min, 'max' => $Max] = $Pricing;
            $Count = intval($Input['count'] ?? 0);
            if ($Count < $Min || $Count > $Max || $Count % $Step !== 0) {
                return [null, ['تعداد باید بین ' . number_format($Min) . ' و ' . number_format($Max) . ' و مضربی از ' . number_format($Step) . ' باشد.', 'count_invalid', 422, ['min' => $Min, 'max' => $Max, 'step' => $Step]]];
            }
            $Price = intdiv($Count, $Step) * $Pricing['price'];
        } else {
            $Plan = Plan::where('product_id', $Product->id)->where('id', intval($Input['plan_id'] ?? 0))->where('status', 1)->first();
            if (is_null($Plan)) {
                return [null, ['این بسته دیگر ارائه نمی شود. لطفا بسته دیگری انتخاب کنید.', 'plan_invalid', 422, []]];
            }
            $Count = (int)$Plan->count;
            $Price = self::dollarPrice($Product->price, $Count, $this->rate());
        }
        if ($Price <= 0) {
            return [null, ['این سرویس در حال حاضر در دسترس نیست.', 'service_unavailable', 404, []]];
        }

        // Answers to the service's own fields. Names and titles come from the service, never from the request.
        $Given = is_array($Input['fields'] ?? null) ? $Input['fields'] : [];
        $Answers = [];
        foreach ($this->fields($Product) as $Field) {
            $Value = trim((string)($Given[$Field['filed_name']] ?? ''));
            $Title = trim((string)$Field['title']);
            if ($Field['type'] === 'select_box') {
                $Options = array_values(array_filter(array_map('trim', explode(',', (string)$Field['select_value']))));
                $Ok = in_array($Value, $Options, true);
            } else {
                $Ok = mb_strlen($Value) >= 3 && mb_strlen($Value) <= 150;
            }
            if (!$Ok) {
                return [null, ["لطفا $Title را درست وارد کنید.", 'fields_invalid', 422, ['field' => $Field['filed_name']]]];
            }
            $Answers[] = ['filed_name' => $Field['filed_name'], 'filed_type' => $Title, 'filed_value' => $Value];
        }

        if (isset($Input['price']) && $Input['price'] !== '' && intval($Input['price']) !== $Price) {
            return [null, ['قیمت تغییر کرده است.', 'price_changed', 409, ['price' => $Price]]];
        }

        $Detail = $Answers;
        $Detail['plan'] = [
            'plan_id' => $Plan?->id,
            'plan_name' => $Plan ? $Plan->name : number_format($Count) . ' عدد',
            'count' => $Count,
        ];
        return [['product' => $Product, 'platform' => $Platform, 'count' => $Count, 'price' => $Price, 'detail' => $Detail], null];
    }

    /*
     * An unpaid order with its item and payment, shaped like the old site's so admin, the user panel and the
     * payment code read it as before.
     * @return array{0: Order, 1: Payment}
     */
    public function createOrder($User, array $Spec): array
    {
        return DB::transaction(function () use ($User, $Spec) {
            $Order = new Order();
            $Order->type = self::TYPE;
            $Order->status = 'waiting to pay';
            $Order->pay_status = 'waiting to pay';
            $Order->price = $Spec['price'];
            $Order->user_id = $User->id;
            $Order->expire_date = Carbon::now()->addMinutes(60);
            $Order->save();

            $Item = new OrderItem();
            $Item->order_id = $Order->id;
            $Item->product_id = $Spec['product']->id;
            $Item->user_id = null;
            $Item->type = self::TYPE;
            $Item->price = $Spec['price'];
            $Item->detail = json_encode($Spec['detail'], JSON_UNESCAPED_UNICODE);
            $Item->save();

            $Payment = new Payment();
            $Payment->order_id = $Order->id;
            $Payment->user_id = $User->id;
            $Payment->type = 1;
            $Payment->status = 'waiting to pay';
            $Payment->gate = 'Bank';
            $Payment->creator = 'System';
            $Payment->price = $Spec['price'];
            $Payment->save();

            $Order->pay_id = $Payment->id;
            $Order->save();

            return [$Order, $Payment];
        });
    }

    // An order as the storefront's order view shows it.
    public function data(Order $Order): array
    {
        $Item = OrderItem::where('order_id', $Order->id)->first();
        $Detail = $Item ? (json_decode((string)$Item->detail, true) ?: []) : [];
        $Product = $Item ? Product::find($Item->product_id) : null;
        $Platform = $Product ? Category::find($Product->country_id) : null;
        $Info = $Product ? (json_decode((string)$Product->detail, true) ?: []) : [];
        $Plan = $Detail['plan'] ?? [];
        unset($Detail['plan']);

        return [
            'id' => $Order->id,
            'status' => match ($Order->status) {
                'waiting to pay' => 'unpaid',
                'pending' => 'queued',            // paid; waiting for support to start it
                'progressing' => 'in_progress',
                'complete' => 'done',
                'back money to wallet' => 'refunded',
                'suspended' => 'paused',
                default => 'cancelled',
            },
            'service' => $Product ? [
                'id' => $Product->id,
                'slug' => $Product->slug,
                'name' => $Info['name'] ?? $Product->faName,
                'en_name' => $Info['enName'] ?? null,
            ] : null,
            'platform' => $Platform ? ['slug' => $Platform->slug, 'name' => $Platform->name, 'en_name' => $Platform->enName] : null,
            'count' => isset($Plan['count']) ? (int)$Plan['count'] : null,
            'plan_name' => $Plan['plan_name'] ?? null,
            'fields' => collect($Detail)->filter(fn($f) => is_array($f) && isset($f['filed_value']))
                ->map(fn($f) => ['title' => (string)($f['filed_type'] ?? ''), 'value' => (string)$f['filed_value']])->values(),
            'price' => (int)$Order->price,
            'paid_with' => ($Order->pay_id && Payment::where('id', $Order->pay_id)->value('gate') === 'Bank') ? 'card' : 'wallet',
            'created_at' => Carbon::parse($Order->created_at)->toIso8601String(),
        ];
    }

    private function fields(Product $Product): array
    {
        $Fields = json_decode((string)$Product->filds, true);
        return array_values(array_filter(is_array($Fields) ? $Fields : [], fn($f) => is_array($f) && !empty($f['filed_name'])));
    }
}
