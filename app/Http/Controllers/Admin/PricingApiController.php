<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Currency;
use App\Models\Virtual_Products;
use App\Services\Pricing;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Artisan;
use Illuminate\Support\Facades\DB;

/**
 * Admin › ارزها و سود (resources/admin/pages/pricing): market rates with the profit added on top of each, and the
 * profit per virtual-number operator. See App\Services\Pricing for how prices use them.
 */
class PricingApiController extends Controller
{
    // The currencies the shop prices with (updated from AriaPay, App\Services\CurrencyRates) and Tether (manual).
    private const CURRENCIES = [
        Pricing::DOLLAR => ['code' => 'USD', 'auto' => true, 'usedFor' => 'اشتراک‌ها و خدمات مجازی (و هزینه شماره‌های SMS-PVA)'],
        Pricing::RUBLE => ['code' => 'RUB', 'auto' => true, 'usedFor' => 'هزینه شماره‌های SMS-Man و SMS-Activate'],
        4 => ['code' => 'EUR', 'auto' => true, 'usedFor' => null],
        26 => ['code' => 'USDT', 'auto' => false, 'usedFor' => null],
    ];

    public function index(): JsonResponse
    {
        return response()->json($this->state());
    }

    // A manual rate further than this (percent) from the current one needs `confirm`, so a typo can't reprice the shop.
    private const CONFIRM_OVER = 40;

    /**
     * {currencies: {id: profit}, operators: {key: profit}, rates: {id: {mode: auto|manual, rate}}, confirm} — saves and
     * reprices the numbers. A currency switched back to automatic gets AriaPay's rate right away.
     */
    public function save(Request $request): JsonResponse
    {
        $data = $request->validate([
            'rates' => 'array',
            'rates.*.mode' => 'required|in:auto,manual',
            'rates.*.rate' => 'nullable|integer|min:1|max:1000000000',
            'confirm' => 'boolean',
            'currencies' => 'array',
            'currencies.*' => 'required|numeric|min:-50|max:1000',
            'operators' => 'array',
            'operators.*' => 'required|numeric|min:-50|max:1000',
        ], [
            'currencies.*.required' => 'درصد سود را بنویسید (۰ یعنی بدون سود).',
            'currencies.*.numeric' => 'درصد سود باید عدد باشد.',
            'currencies.*.min' => 'درصد سود نمی‌تواند کمتر از -۵۰ باشد.',
            'currencies.*.max' => 'درصد سود نمی‌تواند بیشتر از ۱۰۰۰ باشد.',
            'operators.*.required' => 'درصد سود را بنویسید (۰ یعنی بدون سود).',
            'operators.*.numeric' => 'درصد سود باید عدد باشد.',
            'operators.*.min' => 'درصد سود نمی‌تواند کمتر از -۵۰ باشد.',
            'operators.*.max' => 'درصد سود نمی‌تواند بیشتر از ۱۰۰۰ باشد.',
            'rates.*.rate.integer' => 'نرخ را به تومان و بدون اعشار بنویسید.',
            'rates.*.rate.min' => 'نرخ باید بیشتر از صفر باشد.',
        ]);

        // Manual rates: present, and not far from the current one unless confirmed.
        $rows = Currency::whereIn('id', array_keys(self::CURRENCIES))->get()->keyBy('id');
        $rates = [];
        $errors = [];
        foreach ($data['rates'] ?? [] as $id => $r) {
            $id = (int) $id;
            if (!isset(self::CURRENCIES[$id]) || !isset($rows[$id])) {
                continue;
            }
            $mode = self::CURRENCIES[$id]['auto'] ? $r['mode'] : 'manual'; // Tether has no feed
            $rate = $r['rate'] ?? null;
            if ($mode === 'manual') {
                if (!$rate) {
                    $errors["rates.$id.rate"] = ['نرخ دستی را بنویسید.'];
                    continue;
                }
                $old = (int) $rows[$id]->sell;
                $change = $old > 0 ? abs($rate - $old) / $old * 100 : 0;
                if ($rate !== $old && $change > self::CONFIRM_OVER && empty($data['confirm'])) {
                    $errors["rates.$id.rate"] = [sprintf('این نرخ %s٪ با نرخ فعلی فرق دارد؛ برای ذخیره، آن را تأیید کنید.', number_format($change))];
                    continue;
                }
            }
            $rates[$id] = ['mode' => $mode, 'rate' => $mode === 'manual' ? (int) $rate : null];
        }
        if ($errors) {
            return response()->json(['message' => 'نرخ‌ها ذخیره نشد.', 'errors' => $errors], 422);
        }

        $notes = [];
        $fetch = false;
        DB::transaction(function () use ($data, $rates, $rows, &$notes, &$fetch) {
            foreach ($rates as $id => $r) {
                $row = $rows[$id];
                $was = $row->rate_mode ?? 'auto';
                $name = $row->faName ?: self::CURRENCIES[$id]['code'];
                if ($r['mode'] === 'manual') {
                    $changed = (int) $row->sell !== $r['rate'] || $was !== 'manual';
                    if ($changed) {
                        Currency::whereKey($id)->update(['sell' => $r['rate'], 'buy' => $r['rate'], 'rate_mode' => 'manual', 'rate_mode_at' => $was !== 'manual' ? now() : ($row->rate_mode_at ?? now())]);
                        $notes[] = "$name: دستی ← " . number_format($r['rate']) . ' تومان';
                    }
                } elseif ($was !== 'auto') {
                    Currency::whereKey($id)->update(['rate_mode' => 'auto', 'rate_mode_at' => now()]);
                    $notes[] = "$name: خودکار (AriaPay)";
                    $fetch = true;
                }
            }
            foreach ($data['currencies'] ?? [] as $id => $profit) {
                if (isset(self::CURRENCIES[(int) $id])) {
                    Currency::whereKey((int) $id)->update(['profit_percent' => $profit]);
                }
            }
            foreach ($data['operators'] ?? [] as $key => $profit) {
                if (isset(Pricing::OPERATORS[$key])) {
                    DB::table('number_operator_profits')->updateOrInsert(['operator' => $key], ['profit_percent' => $profit, 'updated_at' => now()]);
                }
            }
        });
        if ($notes) {
            $who = optional(auth('admin')->user())->full_name ?? 'admin';
            \Illuminate\Support\Facades\Log::info('currency rate mode changed', ['by' => $who, 'changes' => $notes]);
            if (function_exists('SendTelegramMessage')) {
                SendTelegramMessage("تغییر نرخ ارز در پنل ($who)\n" . implode("\n", $notes) . "\n#نرخ_ارز");
            }
        }
        // Back on automatic: take AriaPay's rate now (it also reprices). If the feed fails, the last rate stays.
        $fetchFailed = false;
        if ($fetch) {
            $fetchFailed = Artisan::call('currency:update') !== 0;
        }
        $changed = Pricing::repriceNumbers(); // also forgets the old values
        $message = $fetchFailed ? 'ذخیره شد، اما نرخ AriaPay دریافت نشد؛ تا به‌روزرسانی بعدی، آخرین نرخ می‌ماند.' : 'ذخیره شد و قیمت‌ها به‌روز شد.';
        return response()->json(['success' => $message, 'repriced' => array_sum($changed)] + $this->state());
    }

    /** Fetches the market rates from AriaPay now (the schedule does it every 30 minutes). */
    public function refresh(): JsonResponse
    {
        $code = Artisan::call('currency:update');
        Pricing::flush();
        $state = $this->state();
        return $code === 0
            ? response()->json(['success' => 'نرخ‌ها از AriaPay به‌روز شد.'] + $state)
            : response()->json(['message' => 'نرخ‌ها به‌روز نشد. اگر نرخی بیش از ۴۰٪ تغییر کرده، باید از سرور با --force اعمال شود.', 'output' => trim(Artisan::output())] + $state, 422);
    }

    private function state(): array
    {
        Pricing::flush();
        $rows = Currency::whereIn('id', array_keys(self::CURRENCIES))->get()->keyBy('id');
        $currencies = collect(self::CURRENCIES)->map(function ($meta, $id) use ($rows) {
            $c = $rows[$id] ?? null;
            $market = (float) optional($c)->sell;
            $profit = (float) optional($c)->profit_percent;
            return [
                'id' => $id,
                'name' => (string) (optional($c)->faName ?: $meta['code']),
                'code' => $meta['code'],
                'auto' => $meta['auto'],
                // manual: an admin typed the rate (AriaPay leaves it alone); feed = AriaPay's latest, to compare.
                'mode' => $meta['auto'] ? (optional($c)->rate_mode ?: 'auto') : 'manual',
                'modeAt' => optional($c)->rate_mode_at ? \Carbon\Carbon::parse($c->rate_mode_at)->toIso8601String() : null,
                'feed' => optional($c)->feed_rate ? (int) $c->feed_rate : null,
                'usedFor' => $meta['usedFor'],
                'market' => (int) round($market),
                'profit' => $profit,
                'selling' => (int) round($market * (1 + $profit / 100)),
                'updatedAt' => optional(optional($c)->updated_at)->toIso8601String(),
            ];
        })->values();

        $counts = Virtual_Products::select('type', DB::raw('sum(status = 1 and toman_price > 0) as live'))->groupBy('type')->pluck('live', 'type');
        $profits = Pricing::operatorProfits();
        $operators = collect(Pricing::OPERATORS)->map(fn ($o, $key) => [
            'key' => $key,
            'label' => $o['label'],
            'currency' => self::CURRENCIES[$o['currency']]['code'],
            'market' => (int) round(Pricing::market($o['currency'])),
            'profit' => $profits[$key],
            'rate' => (int) round(Pricing::numberRate($key)),
            'live' => (int) ($counts[$key] ?? 0),
        ])->values();

        return ['currencies' => $currencies, 'operators' => $operators];
    }
}
