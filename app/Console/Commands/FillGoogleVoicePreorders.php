<?php

namespace App\Console\Commands;

use App\Services\GoogleVoiceNumbers;
use Illuminate\Console\Command;

/**
 * php artisan gv:fill-preorders
 * Buys a Numberland number for each paid Google Voice pre-order, oldest first (App\Services\GoogleVoiceNumbers::
 * fillPreorders). Scheduled every 5 minutes in App\Console\Kernel; does nothing while admin › Google Voice has
 * "fill pre-orders automatically" off.
 */
class FillGoogleVoicePreorders extends Command
{
    protected $signature = 'gv:fill-preorders';

    protected $description = 'Fill paid Google Voice pre-orders from Numberland';

    public function handle(GoogleVoiceNumbers $numbers): int
    {
        if (!$numbers->settings()['preorder_autofill']) {
            $this->info('Automatic filling is off (admin › Google Voice).');
            return self::SUCCESS;
        }
        $results = $numbers->fillPreorders();
        foreach ($results as $order => $result) {
            $this->line("#$order: $result");
        }
        $this->info(count($results) ? 'Done.' : 'No pre-order to fill.');
        return self::SUCCESS;
    }
}
