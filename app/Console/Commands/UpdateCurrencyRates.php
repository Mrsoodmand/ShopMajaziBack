<?php

namespace App\Console\Commands;

use App\Models\Currency;
use App\Services\CurrencyRates;
use Illuminate\Console\Command;
use Throwable;

/**
 * php artisan currency:update [--dry-run] [--force]
 * Pulls the dollar, euro and ruble rates from AriaPay into the `currency` table (see App\Services\CurrencyRates).
 * Scheduled in App\Console\Kernel; --dry-run shows what would change, including the derived "PayPal dollar" (#20)
 * that subscription and service prices use.
 */
class UpdateCurrencyRates extends Command
{
    protected $signature = 'currency:update {--dry-run : Show the changes without saving} {--force : Accept changes over the safety limit}';

    protected $description = 'Update currency rates from AriaPay';

    public function handle(CurrencyRates $rates): int
    {
        try {
            $feed = $rates->fetch();
        } catch (Throwable $e) {
            $this->error('Rates not updated: ' . $e->getMessage());
            report($e);
            return self::FAILURE;
        }

        $dry = (bool) $this->option('dry-run');
        $paypalBefore = $this->paypalDollar();
        $lines = $rates->apply($feed, $dry, (bool) $this->option('force'));

        $this->table(['currency', 'row', 'now (Toman)', 'feed (Toman)', 'change', 'note'], array_map(fn ($l) => [
            strtoupper($l['code']), '#' . $l['id'], number_format((int) $l['old']), $l['new'] ? number_format($l['new']) : '-',
            $l['change'] === null ? '-' : sprintf('%+.1f%%', $l['change']), $l['skipped'] ?? ($dry ? 'dry run' : 'saved'),
        ], $lines));

        // What the derived "PayPal dollar" (subscriptions, services) becomes with the new dollar.
        $usd = collect($lines)->firstWhere('code', 'usd');
        if ($paypalBefore && $usd && $usd['new'] && $usd['old'] && !$usd['skipped']) {
            $after = (int) round($paypalBefore * $usd['new'] / $usd['old']);
            $this->line(sprintf('PayPal dollar (#20, subscriptions and services): %s → %s Toman', number_format($paypalBefore), number_format($after)));
        }

        $changed = collect($lines)->filter(fn ($l) => !$l['skipped'] && $l['new'] !== $l['old']);
        if (!$dry && $changed->isNotEmpty() && function_exists('SendTelegramMessage')) {
            SendTelegramMessage("قیمت ارزها به‌روز شد (AriaPay)\n" . $changed->map(fn ($l) => strtoupper($l['code']) . ' = ' . number_format($l['new']) . ' تومان')->implode("\n") . "\n#بروزرسانی_ارزها");
        }
        $skipped = collect($lines)->filter(fn ($l) => $l['skipped'] === 'change over ' . CurrencyRates::MAX_CHANGE . '% (use --force)');
        return $skipped->isNotEmpty() ? self::FAILURE : self::SUCCESS;
    }

    private function paypalDollar(): ?int
    {
        $row = Currency::find(20);
        return $row && function_exists('CalculatorSellPrice') ? (int) round(CalculatorSellPrice($row)) : null;
    }
}
