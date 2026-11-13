<?php

namespace App\Services;

use App\Models\Currency;
use App\Models\Order;
use App\Models\OrderItem;
use App\Models\Payment;
use App\Models\Plan;
use App\Models\Product;
use Carbon\Carbon;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\DB;

/*
 * Foreign accounts and subscriptions (ChatGPT, Spotify, TradingView…) for the Next.js storefront (/accounts).
 * Products of type "accounts" with their plans (plans.type = accounts), set up in React admin › accounts.
 *
 * Buying makes the same order as the old site's v2\OrderController::submitExternalAccount: order type 7 with one
 * item holding the buyer's answers to the product's fields and the plan, plus a payment waiting for wallet or bank.
 * Paying reuses Payment\WalletController (as the old site did, through its type-2 "virtual service" branch) and
 * CallBackController, and the order then waits in admin › orders until support delivers it.
 *
 * Prices: plan price in dollars × the services rate (admin › currencies #20), rounded like every price the site
 * shows; a plan's `discount` is its sale price (charged instead of `price`, as the old site did). Products set to
 * toman (detail.price_type = 0) are charged as written.
 */
class AccountOrders
{
    public const TYPE = 7;

    // Storefront categories (the tabs on /accounts). Empty = "other".
    public const CATEGORIES = ['ai', 'video', 'music', 'design', 'work', 'trading', 'social', 'other'];
    // own: we activate it on the buyer's account and only need its email / username; login: we need the buyer's
    // login to activate it; ready: we deliver a ready account.
    public const ACTIVATIONS = ['own', 'login', 'ready'];

    private const SECRET = '/pass|رمز|کلمه\s*(ی\s*)?عبور/iu';

    // The services rate: dollars → toman, with the margin set in admin › currencies (#20).
    public function rate(): float
    {
        $Currency = Currency::find(20);
        return $Currency ? (float)CalculatorSellPrice($Currency) : 0.0;
    }

    /*
     * Toman to charge for a plan and the price before its discount (null without one).
     * @return array{0: int, 1: ?int}
     */
    public static function planPrices(Plan $Plan, array $Info, float $Rate): array
    {
        $Toman = (string)($Info['price_type'] ?? '1') === '0';
        $Convert = fn(float $n) => $n <= 0 ? 0 : self::round($Toman ? $n : $n * $Rate);
        $Base = (float)$Plan->price;
        $Sale = (float)$Plan->discount;
        if ($Sale > 0) {
            $Price = $Convert($Sale);
            $Was = $Convert($Base);
            return [$Price, $Was > $Price ? $Was : null];
        }
        return [$Convert($Base), null];
    }

    private static function round(float $Toman): int
    {
        return (int)str_replace(',', '', CovertVirtualNumberPrice(number_format($Toman)));
    }

    /* How long a plan runs: the admin's months, else read from its name ("اشتراک 3 ماهه", "یکساله", "1 year"). */
    public static function months(Plan $Plan): ?int
    {
        if ((int)$Plan->months > 0) {
            return (int)$Plan->months;
        }
        $Name = strtr(trim((string)$Plan->name . ' ' . (string)$Plan->en_name), ['۰' => '0', '۱' => '1', '۲' => '2', '۳' => '3', '۴' => '4', '۵' => '5', '۶' => '6', '۷' => '7', '۸' => '8', '۹' => '9']);
        $Words = ['دوازده' => 12, 'یازده' => 11, 'هیجده' => 18, 'هجده' => 18, 'یک' => 1, 'دو' => 2, 'سه' => 3, 'چهار' => 4, 'پنج' => 5, 'شش' => 6, 'شیش' => 6, 'هفت' => 7, 'هشت' => 8, 'نه' => 9, 'ده' => 10];
        $Word = implode('|', array_keys($Words));
        if (preg_match('/(\d{1,2})\s*-?\s*(ماه|month)/iu', $Name, $m)) {
            return (int)$m[1] ?: null;
        }
        if (preg_match("/(?<![\p{L}])($Word)\s*ماه/u", $Name, $m)) {
            return $Words[$m[1]];
        }
        if (preg_match('/(\d{1,2})\s*-?\s*(سال|year)/iu', $Name, $m)) {
            return (int)$m[1] * 12 ?: null;
        }
        if (preg_match("/(?<![\p{L}])($Word)\s*سال/u", $Name, $m)) {
            return $Words[$m[1]] * 12;
        }
        if (preg_match('/سالانه|yearly|annual/iu', $Name)) {
            return 12;
        }
        if (preg_match('/ماهانه|monthly/iu', $Name)) {
            return 1;
        }
        return null;
    }

    // "خرید اشتراک اسپاتیفای" → "اسپاتیفای": the old admin names products like page titles.
    public static function shortName(Product $Product, array $Info): string
    {
        $Name = trim((string)($Info['name'] ?? $Product->faName));
        $Short = trim((string)preg_replace('/^(خرید\s+)?((اشتراک|اکانت)(\s+های)?\s+)?/u', '', $Name));
        return $Short !== '' ? $Short : $Name;
    }

    /*
     * One product as the storefront shows it. Products without a live plan with a price can't be bought: null.
     * `$Full` adds the page's own text and SEO.
     */
    public function product(Product $Product, Collection $Plans, float $Rate, bool $Full = false): ?array
    {
        $Info = $this->decode($Product->detail);
        $Short = self::shortName($Product, $Info);
        $Fields = $this->fields($Product);
        $PlanRows = [];
        foreach ($Plans as $Plan) {
            [$Price, $Was] = self::planPrices($Plan, $Info, $Rate);
            if ($Price <= 0) {
                continue;
            }
            $PlanRows[] = [
                'id' => $Plan->id,
                'name' => $this->planName((string)$Plan->name, $Short),
                'en_name' => trim((string)$Plan->en_name) ?: null,
                // Latin words in the Persian name ("اکانت سه ماهه Pro Plus" → "Pro Plus"), for the English site.
                'tier' => preg_match_all('/[A-Za-z][A-Za-z0-9+]*/', (string)$Plan->name, $m) ? ucwords(strtolower(implode(' ', $m[0]))) : null,
                'months' => self::months($Plan),
                'price' => $Price,
                'was' => $Was,
            ];
        }
        if (!$PlanRows) {
            return null;
        }

        $Notes = collect(is_array($n = $this->decode($Product->notes)) ? $n : [])
            ->map(fn($value, $key) => ['label' => trim((string)$key), 'value' => trim((string)$value)])
            // Placeholder rows the old admin saved ("a": "a", "عنوان": "Col") aren't notes.
            ->filter(fn($note) => mb_strlen($note['label']) > 1 && mb_strlen($note['value']) > 1 && !preg_match('/^(col|عنوان)/iu', $note['label'] . $note['value']))
            ->values();
        $Delivery = $Notes->first(fn($note) => preg_match('/زمان|مدت|تحویل|فعال\s*سازی/u', $note['label']));

        $Data = [
            'id' => $Product->id,
            'slug' => (string)$Product->slug,
            'name' => $Short,
            'en_name' => trim((string)($Info['en_name'] ?? '')) ?: null,
            'tagline' => trim((string)($Info['tagline'] ?? '')) ?: null,
            'tagline_en' => trim((string)($Info['tagline_en'] ?? '')) ?: null,
            'category' => in_array($Info['category'] ?? null, self::CATEGORIES, true) ? $Info['category'] : 'other',
            'color' => preg_match('/^#[0-9a-f]{6}$/i', (string)($Info['color'] ?? '')) ? strtolower($Info['color']) : null,
            'image' => $this->assetPath($Info['image_gallery'] ?? null),
            'activation' => in_array($Info['activation'] ?? null, self::ACTIVATIONS, true) ? $Info['activation'] : $this->guessActivation($Fields),
            'delivery' => trim((string)($Info['delivery'] ?? '')) ?: ($Delivery['value'] ?? null),
            'delivery_en' => trim((string)($Info['delivery_en'] ?? '')) ?: null,
            'popular' => !empty($Info['popular']),
            'plans' => $PlanRows,
            'fields' => array_map(fn($f) => [
                'name' => $f['filed_name'],
                'title' => trim((string)$f['title']),
                'type' => $f['type'] === 'select_box' ? 'select' : $this->fieldKind($f),
                'options' => $f['type'] === 'select_box' ? $this->options($f) : [],
            ], $Fields),
            'notes' => $Notes->reject(fn($note) => $note === $Delivery)->values(),
        ];
        if ($Full) {
            $Data += [
                'short_detail' => $Info['short_detail'] ?? null,
                'content' => $Info['long_detail'] ?? null,
                'seo' => ['meta_title' => $Info['meta_title'] ?? null, 'meta_desc' => $Info['meta_desc'] ?? null],
            ];
        }
        return $Data;
    }

    // Live plans per product, in the admin's order.
    public function plansFor(Collection $Products): Collection
    {
        return Plan::whereIn('product_id', $Products->pluck('id'))->where('type', 'accounts')->where('status', 1)
            ->orderByRaw('sort IS NULL')->orderBy('sort')->orderBy('id')->get()->groupBy('product_id');
    }

    /*
     * What the buyer asked for, checked against the product as the admin set it up:
     * {product_id, plan_id, fields: {name: value}, price}. `price` is the total the page showed.
     * @return array{0: ?array, 1: ?array} [spec, error]; error = [message, code, status, data]
     */
    public function spec(array $Input): array
    {
        $Product = Product::where('id', intval($Input['product_id'] ?? 0))->where('type', 'accounts')->where('status', 1)->first();
        if (is_null($Product)) {
            return [null, ['این اشتراک در حال حاضر در دسترس نیست.', 'unavailable', 404, []]];
        }
        $Plan = Plan::where('product_id', $Product->id)->where('type', 'accounts')->where('id', intval($Input['plan_id'] ?? 0))->where('status', 1)->first();
        if (is_null($Plan)) {
            return [null, ['این پلن دیگر ارائه نمی شود. لطفا پلن دیگری انتخاب کنید.', 'plan_invalid', 422, []]];
        }
        $Info = $this->decode($Product->detail);
        [$Price] = self::planPrices($Plan, $Info, $this->rate());
        if ($Price <= 0) {
            return [null, ['این اشتراک در حال حاضر در دسترس نیست.', 'unavailable', 404, []]];
        }

        // Answers to the product's own fields. Names and titles come from the product, never from the request.
        $Given = is_array($Input['fields'] ?? null) ? $Input['fields'] : [];
        $Answers = [];
        foreach ($this->fields($Product) as $Field) {
            $Value = trim((string)($Given[$Field['filed_name']] ?? ''));
            $Title = trim((string)$Field['title']);
            if ($Field['type'] === 'select_box') {
                $Ok = in_array($Value, $this->options($Field), true);
            } else {
                $Ok = mb_strlen($Value) >= 3 && mb_strlen($Value) <= 150
                    && ($this->fieldKind($Field) !== 'email' || filter_var($Value, FILTER_VALIDATE_EMAIL));
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
        $Detail['plan'] = ['plan_id' => $Plan->id, 'plan_name' => $Plan->name];
        return [['product' => $Product, 'plan' => $Plan, 'price' => $Price, 'detail' => $Detail], null];
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
            $Order->product_id = $Spec['product']->id;
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

    // An order as the storefront's order view shows it. Passwords the buyer typed are not sent back.
    public function data(Order $Order): array
    {
        $Item = OrderItem::where('order_id', $Order->id)->first();
        $Detail = $Item ? (json_decode((string)$Item->detail, true) ?: []) : [];
        $Product = $Item ? Product::find($Item->product_id) : null;
        $Info = $Product ? $this->decode($Product->detail) : [];
        $PlanId = $Detail['plan']['plan_id'] ?? null;
        $Plan = $PlanId ? Plan::find($PlanId) : null;
        $PlanName = (string)($Detail['plan']['plan_name'] ?? '');
        unset($Detail['plan']);

        return [
            'id' => $Order->id,
            'status' => match ($Order->status) {
                'waiting to pay' => 'unpaid',
                'pending' => 'queued',            // paid; waiting for support
                'progressing' => 'in_progress',
                'complete' => 'done',
                'back money to wallet' => 'refunded',
                'suspended' => 'paused',
                default => 'cancelled',
            },
            'product' => $Product ? [
                'id' => $Product->id,
                'slug' => (string)$Product->slug,
                'name' => self::shortName($Product, $Info),
                'en_name' => trim((string)($Info['en_name'] ?? '')) ?: null,
                'image' => $this->assetPath($Info['image_gallery'] ?? null),
                'color' => $Info['color'] ?? null,
                'activation' => in_array($Info['activation'] ?? null, self::ACTIVATIONS, true) ? $Info['activation'] : $this->guessActivation($this->fields($Product)),
            ] : null,
            'plan' => [
                'id' => $PlanId ? (int)$PlanId : null,
                'name' => $Product ? $this->planName($PlanName, self::shortName($Product, $Info)) : $PlanName,
                'en_name' => $Plan ? (trim((string)$Plan->en_name) ?: null) : null,
                'months' => $Plan ? self::months($Plan) : null,
            ],
            'fields' => collect($Detail)->filter(fn($f) => is_array($f) && isset($f['filed_value']))
                ->map(fn($f) => [
                    'title' => (string)($f['filed_type'] ?? ''),
                    'value' => preg_match(self::SECRET, ($f['filed_name'] ?? '') . ' ' . ($f['filed_type'] ?? '')) ? null : (string)$f['filed_value'],
                ])->values(),
            'price' => (int)$Order->price,
            'paid_with' => ($Order->pay_id && Payment::where('id', $Order->pay_id)->value('gate') === 'Bank') ? 'card' : 'wallet',
            'created_at' => Carbon::parse($Order->created_at)->toIso8601String(),
        ];
    }

    // "اشتراک 6 ماهه تلگرام پریمیوم" → "6 ماهه": without the "اشتراک / اکانت" prefix and the product's own name.
    private function planName(string $Name, string $Product): string
    {
        $Clean = trim((string)preg_replace('/^(اشتراک|اکانت)\s+/u', '', trim($Name)));
        if ($Product !== '' && mb_strlen($Clean) > mb_strlen($Product)) {
            $Clean = trim(str_replace($Product, '', $Clean));
        }
        return trim((string)preg_replace('/\s{2,}/u', ' ', $Clean)) ?: trim($Name);
    }

    // text | email | password, from the field's name and title.
    private function fieldKind(array $Field): string
    {
        $Title = (string)($Field['title'] ?? '');
        $Text = ($Field['filed_name'] ?? '') . ' ' . $Title;
        if (preg_match(self::SECRET, $Text)) {
            return 'password';
        }
        // TradingView's field is named "email" but asks for a username: the title wins.
        if (preg_match('/نام\s*کاربری|یوزر|user\s*name|آیدی|\bid\b/iu', $Title)) {
            return 'text';
        }
        return preg_match('/mail|ایمیل/iu', $Text) ? 'email' : 'text';
    }

    // Asks for a password → we sign in to activate it; asks for anything else → activated on the buyer's account;
    // asks nothing → a ready account.
    private function guessActivation(array $Fields): string
    {
        $Kinds = array_map(fn($f) => $f['type'] === 'select_box' ? 'select' : $this->fieldKind($f), $Fields);
        if (in_array('password', $Kinds, true)) {
            return 'login';
        }
        return array_diff($Kinds, ['select']) ? 'own' : 'ready';
    }

    private function options(array $Field): array
    {
        return array_values(array_filter(array_map('trim', explode(',', (string)($Field['select_value'] ?? '')))));
    }

    private function fields(Product $Product): array
    {
        $Fields = json_decode((string)$Product->filds, true);
        return array_values(array_filter(is_array($Fields) ? $Fields : [], fn($f) => is_array($f) && !empty($f['filed_name'])));
    }

    private function decode($Value): array
    {
        $Data = is_array($Value) ? $Value : json_decode((string)$Value, true);
        return is_array($Data) ? $Data : [];
    }

    // Relative paths load through the storefront's own domain (the Next.js proxy).
    private function assetPath($Path): ?string
    {
        if (empty($Path)) {
            return null;
        }
        return preg_match('#^https?://#', $Path) ? $Path : '/' . ltrim($Path, '/');
    }
}
