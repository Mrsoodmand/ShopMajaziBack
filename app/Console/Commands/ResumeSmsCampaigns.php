<?php

namespace App\Console\Commands;

use App\Jobs\SendSmsCampaign;
use App\Models\SmsCampaign;
use Illuminate\Console\Command;

/* Picks up bulk SMS sends that stopped half-way (no progress for 3 minutes). See App\Jobs\SendSmsCampaign. */
class ResumeSmsCampaigns extends Command
{
    protected $signature = 'sms:resume-campaigns';
    protected $description = 'Continue bulk SMS sends that stopped before finishing';

    public function handle(): int
    {
        $Stuck = SmsCampaign::where('status', 'sending')->where('updated_at', '<', now()->subMinutes(3))->pluck('id');
        foreach ($Stuck as $Id) {
            $this->info("Resuming SMS campaign #$Id");
            SendSmsCampaign::dispatchSync($Id);
        }
        return self::SUCCESS;
    }
}
