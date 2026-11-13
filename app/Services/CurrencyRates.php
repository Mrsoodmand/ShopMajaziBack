<?php

namespace App\Services;

use App\Models\Currency;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;
use RuntimeException;

/**
 * Live exchange rates from AriaPay (config services.ariapay: ARIAPAY_TOKEN in .env, never in git — the repo is
 * public) into the `currency` table, which every price in the shop is computed from:
 *   #1 Dollar  — virtual numbers (with #34), and the base of the derived rates below
 *   #4 Euro, #34 Ruble
 *   #20 "PayPal dollar" (dollar + its markup, see CalculatorSellPrice) — subscriptions and social services
 * Derived rows (type = the id they follow, with a percent or fixed markup) need no update: they read the base
 * row's price when a price is calculated.
 *
 * AriaPay quotes in Rial; the table holds Toman, so values are divided by 10.
 * Tether (#26) isn't offered by AriaPay and is left as it is.
 */
class CurrencyRates
{
    // AriaPay code => currency row id.
    public const ROWS = ['usd' => 1, 'eur' => 4, 'rub' => 34];

    // A new rate more than this far (in percent) from the stored one is refused unless forced: a glitch in the
    // feed must not reprice the whole shop. The first update after a long gap may need --force.
    public const MAX_CHANGE = 40;

    /** Toman rate per AriaPay code, from the API. Throws when the feed can't be read. */
    public function fetch(): array
    {
        $token = config('services.ariapay.token');
        if (!$token) {
            throw new RuntimeException('ARIAPAY_TOKEN is not set in .env');
        }
        $res = Http::timeout(20)->retry(2, 1000)->acceptJson()->get(config('services.ariapay.url'), ['token' => $token]);
        $body = $res->json();
        if (!$res->ok() || !is_array($body) || empty($body['success']) || !is_array($body['data'] ?? null)) {
            throw new RuntimeException('AriaPay answered HTTP ' . $res->status());
        }
        if (($body['base_currency'] ?? 'IRR') !== 'IRR') {
            throw new RuntimeException('AriaPay base currency is ' . $body['base_currency'] . ', expected IRR');
        }
        $rates = [];
        foreach ($body['data'] as $row) {
            $code = strtolower((string) ($row['code'] ?? ''));
            $rial = $row['calculated_value'] ?? $row['value'] ?? null;
            if (isset(self::ROWS[$code]) && ($row['active'] ?? true) && is_numeric($rial) && $rial > 0) {
                $rates[$code] = (int) round($rial / 10);
            }
        }
        return $rates;
    }

    /**
     * Writes the rates (unless $dryRun). Returns one line per currency: code, id, old, new, change %, and
     * 'skipped' with a reason when it wasn't written.
     */
    public function apply(array $rates, bool $dryRun = false, bool $force = false): array
    {
        $out = [];
        foreach (self::ROWS as $code => $id) {
            $row = Currency::find($id);
            $new = $rates[$code] ?? null;
            $old = $row ? (int) $row->sell : null;
            $change = $old && $new ? round(($new - $old) / $old * 100, 1) : null;
            $line = compact('code', 'id', 'old', 'new', 'change') + ['skipped' => null];
            if (!$row) {
                $line['skipped'] = 'no currency row';
            } elseif (!$new) {
                $line['skipped'] = 'not in the feed';
            } elseif (!$force && $change !== null && abs($change) > self::MAX_CHANGE) {
                $line['skipped'] = 'change over ' . self::MAX_CHANGE . '% (use --force)';
            } elseif (!$dryRun && $new !== $old) {
                $row->sell = $new;
                $row->buy = $new;
                $row->save();
            }
            $out[] = $line;
        }
        if (!$dryRun) {
            Log::info('currency rates updated', $out);
        }
        return $out;
    }
}
