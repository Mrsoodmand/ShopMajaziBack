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

    /** {currencies: {id: profit}, operators: {key: profit}} — saves and reprices the numbers. */
    public function save(Request $request): JsonResponse
    {
        $data = $request->validate([
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
        ]);
        DB::transaction(function () use ($data) {
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
        $changed = Pricing::repriceNumbers(); // also forgets the old values
        return response()->json(['success' => 'سود ذخیره شد و قیمت‌ها به‌روز شد.', 'repriced' => array_sum($changed)] + $this->state());
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
