<?php

namespace App\Console\Commands;

use App\Models\Order;
use App\Models\OrderItem;
use App\Services\VerificationOrders;
use Carbon\Carbon;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\Storage;

/*
 * Deletes the files buyers sent with verification orders (ID photos, documents) once they are no longer needed:
 * `keep_days` after the order was finished (done, refunded, cancelled, failed), or `unpaid_days` after an order that
 * was never paid (config/verification.php). Orders still being worked on keep theirs. The order keeps the file's
 * name and the date it was deleted, so admin shows "deleted" instead of a broken link.
 */
class PruneVerificationFiles extends Command
{
    protected $signature = 'verification:prune-files {--dry-run : List what would be deleted}';
    protected $description = 'Delete verification order files some days after the order is finished';

    // Still being worked on: files stay whatever their age.
    private const OPEN = ['pending', 'progressing', 'suspended'];

    public function handle(): int
    {
        $Disk = Storage::disk(config('verification.disk', 'local'));
        $Finished = Carbon::now()->subDays(max(1, (int)config('verification.keep_days', 30)));
        $Unpaid = Carbon::now()->subDays(max(1, (int)config('verification.unpaid_days', 2)));
        $Dry = (bool)$this->option('dry-run');
        $Count = 0;

        Order::where('type', VerificationOrders::TYPE)
            ->whereNotIn('status', self::OPEN)
            ->where(fn($q) => $q->where(fn($w) => $w->where('status', 'waiting to pay')->where('created_at', '<', $Unpaid))
                ->orWhere(fn($w) => $w->where('status', '!=', 'waiting to pay')->where('updated_at', '<', $Finished)))
            ->orderBy('id')
            ->chunkById(200, function ($Orders) use ($Disk, $Dry, &$Count) {
                foreach ($Orders as $Order) {
                    $Folder = VerificationOrders::folder($Order->id);
                    if (!$Disk->exists($Folder)) {
                        continue;
                    }
                    $Count++;
                    $this->line(($Dry ? '[dry] ' : '') . "order {$Order->id} ({$Order->status}): " . count($Disk->files($Folder)) . ' file(s)');
                    if ($Dry) {
                        continue;
                    }
                    $Disk->deleteDirectory($Folder);
                    foreach (OrderItem::where('order_id', $Order->id)->get() as $Item) {
                        $Detail = json_decode((string)$Item->detail, true);
                        if (!is_array($Detail)) {
                            continue;
                        }
                        foreach ($Detail as $Key => $Row) {
                            if (is_array($Row) && !empty($Row['file'])) {
                                unset($Detail[$Key]['file']);
                                $Detail[$Key]['pruned'] = Carbon::now()->toDateString();
                            }
                        }
                        $Item->detail = json_encode($Detail, JSON_UNESCAPED_UNICODE);
                        $Item->save();
                    }
                }
            });

        $this->info(($Dry ? 'Would delete' : 'Deleted') . " files of $Count order(s).");
        return self::SUCCESS;
    }
}
