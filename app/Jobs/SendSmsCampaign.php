<?php

namespace App\Jobs;

use App\Models\SmsCampaign;
use App\Services\Sms;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Support\Facades\Cache;

/*
 * Sends a bulk SMS in batches of 100 (Sms::BATCH), saving progress after each batch. Started after the admin's
 * request has been answered (dispatchAfterResponse, the queue here is `sync`), and picked up again by
 * sms:resume-campaigns if it stopped. A lock keeps two runs off the same send.
 */
class SendSmsCampaign implements ShouldQueue
{
    use Dispatchable, Queueable;

    public function __construct(public int $campaignId)
    {
    }

    public function handle(Sms $sms): void
    {
        $Lock = Cache::lock("sms-campaign:{$this->campaignId}", 600);
        if (!$Lock->get()) return;
        ignore_user_abort(true);
        @set_time_limit(0);
        try {
            $C = SmsCampaign::find($this->campaignId);
            $Failures = 0;
            while ($C && $C->status === 'sending' && $C->next_offset < $C->total) {
                $Batch = array_slice($C->mobiles, $C->next_offset, Sms::BATCH);
                $Res = $sms->sendBulk($C->line, $C->message, $Batch);
                if (!$Res['ok'] && (!empty($Res['fatal']) || ++$Failures >= 3)) {
                    // No credit, bad line or key, or three failures in a row: stop here; the rest is not sent.
                    $C->forceFill(['status' => 'failed', 'error' => $Res['error'], 'finished_at' => now()])->save();
                    return;
                }
                if (!$Res['ok']) {
                    sleep(2); // a passing error: try the same batch again
                    continue;
                }
                $Failures = 0;
                $C->forceFill([
                    'next_offset' => $C->next_offset + count($Batch),
                    'accepted' => $C->accepted + $Res['accepted'],
                    'rejected' => $C->rejected + $Res['rejected'],
                    'cost' => $C->cost + $Res['cost'],
                    'pack_ids' => array_values(array_filter(array_merge($C->pack_ids ?? [], [$Res['packId']]))),
                ])->save();
                $C->refresh(); // sees a cancel from the panel
            }
            if ($C && $C->status === 'sending') {
                $C->forceFill(['status' => 'done', 'finished_at' => now()])->save();
            }
        } finally {
            $Lock->release();
        }
    }
}
