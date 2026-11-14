<?php

namespace App\Services;

use App\Models\Currency;
use App\Models\Product;
use App\Models\Virtual_Products;
use Illuminate\Support\Facades\DB;

/**
 * Every selling price in the shop is a market rate plus a profit percentage. The market rates come from AriaPay
 * (App\Services\CurrencyRates → currency.sell); the profits are set in the admin (ارزها و سود, the account editor,
 * virtual numbers › settings). Only one profit applies to a price, the most specific one:
 *
 *   subscriptions, social services:  dollar price × market dollar × (1 + product profit ?? dollar profit)
 *   virtual numbers:                 provider cost × operator's market rate × (1 + operator profit)
 *
 * Numbers keep a stored price (virtual__products.toman_price) for the catalog; repriceNumbers() rewrites it from
 * the provider cost (dollar_price) whenever a rate or a profit changes, and chargePrice() charges the same amount.
 */
class Pricing
{
    public const DOLLAR = 1;
    public const RUBLE = 34;

    // Operators that sell numbers: the currency their cost (virtual__products.dollar_price) is quoted in, and the
    // profit used until one is saved (the multipliers the code had before: ×2 and ×2.5).
    public const OPERATORS = [
        'pva' => ['label' => 'SMS-PVA', 'currency' => self::DOLLAR, 'default' => 100],
        'man' => ['label' => 'SMS-Man', 'currency' => self::RUBLE, 'default' => 150],
        'active' => ['label' => 'SMS-Activate', 'currency' => self::RUBLE, 'default' => 150],
    ];

    private static array $currencies = [];
    private static ?array $operatorProfits = null;

    /** Forget the values read in this request (after saving new rates or profits). */
    public static function flush(): void
    {
        self::$currencies = [];
        self::$operatorProfits = null;
    }

    private static function currency(int $id): ?Currency
    {
        return self::$currencies[$id] ??= Currency::find($id);
    }

    /** Market rate in Toman (what AriaPay sends). */
    public static function market(int $currencyId): float
    {
        return (float) optional(self::currency($currencyId))->sell;
    }

    /** The default profit (percent) for prices in this currency. */
    public static function currencyProfit(int $currencyId): float
    {
        return (float) optional(self::currency($currencyId))->profit_percent;
    }

    /** Market rate plus the currency's profit. */
    public static function sellingRate(int $currencyId): float
    {
        return self::market($currencyId) * (1 + self::currencyProfit($currencyId) / 100);
    }

    /** Toman per dollar for subscriptions and social services without a profit of their own. */
    public static function servicesRate(): float
    {
        return self::sellingRate(self::DOLLAR);
    }

    /** Toman per dollar for one product: its own profit when it has one, else the dollar's. */
    public static function productRate(?Product $product): float
    {
        if ($product && $product->profit_percent !== null && $product->profit_percent !== '') {
            return self::market(self::DOLLAR) * (1 + (float) $product->profit_percent / 100);
        }
        return self::servicesRate();
    }

    /** Profit (percent) per operator. */
    public static function operatorProfits(): array
    {
        if (self::$operatorProfits === null) {
            $saved = DB::table('number_operator_profits')->pluck('profit_percent', 'operator')->map(fn ($p) => (float) $p)->all();
            self::$operatorProfits = collect(self::OPERATORS)->map(fn ($o, $key) => $saved[$key] ?? (float) $o['default'])->all();
        }
        return self::$operatorProfits;
    }

    /** Toman per unit of an operator's cost (dollar for PVA, ruble for SMS-Man …), profit included. */
    public static function numberRate(string $operator): float
    {
        $op = self::OPERATORS[$operator] ?? null;
        if (!$op) {
            return 0.0;
        }
        return self::market($op['currency']) * (1 + (self::operatorProfits()[$operator] ?? $op['default']) / 100);
    }

    /** Stored (unrounded) Toman price of a number from its operator's cost. */
    public static function numberPrice(string $operator, float $cost): float
    {
        return self::numberRate($operator) * $cost;
    }

    /** What a number costs the customer: the stored formula, rounded the way the site shows prices. */
    public static function chargePrice(Virtual_Products $product): int
    {
        $price = self::numberPrice((string) $product->type, (float) $product->dollar_price);
        return (int) str_replace(',', '', CovertVirtualNumberPrice(number_format($price)));
    }

    /**
     * Rewrites every number's stored price from its cost with the current rates and profits (one UPDATE per
     * operator). Returns the rows changed per operator.
     */
    public static function repriceNumbers(): array
    {
        self::flush();
        $changed = [];
        foreach (array_keys(self::OPERATORS) as $operator) {
            $rate = self::numberRate($operator);
            if ($rate <= 0) {
                continue;
            }
            $changed[$operator] = Virtual_Products::where('type', $operator)->where('dollar_price', '>', 0)
                ->whereRaw('abs(coalesce(toman_price, 0) - dollar_price * ?) >= 0.5', [$rate])
                ->update(['toman_price' => DB::raw('dollar_price * ' . sprintf('%.6F', $rate)), 'updated_at' => now()]);
        }
        return $changed;
    }
}
