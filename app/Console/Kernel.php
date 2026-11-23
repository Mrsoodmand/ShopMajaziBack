<?php

namespace App\Console;

use Illuminate\Console\Scheduling\Schedule;
use Illuminate\Foundation\Console\Kernel as ConsoleKernel;

class Kernel extends ConsoleKernel
{
    /**
     * Define the application's command schedule.
     *
     * @param  \Illuminate\Console\Scheduling\Schedule  $schedule
     * @return void
     */
    protected function schedule(Schedule $schedule)
    {
        // Exchange rates from AriaPay into the `currency` table (App\Services\CurrencyRates).
        $schedule->command('currency:update')->everyThirtyMinutes()->withoutOverlapping();
        // Paid Google Voice pre-orders, filled from Numberland when a number is available (App\Services\GoogleVoiceNumbers).
        $schedule->command('gv:fill-preorders')->everyFiveMinutes()->withoutOverlapping();
        // Buyers' ID photos / documents on verification orders, deleted some days after the order is finished.
        $schedule->command('verification:prune-files')->dailyAt('04:30')->withoutOverlapping();
        // Bulk SMS sends that stopped half-way (App\Jobs\SendSmsCampaign).
        $schedule->command('sms:resume-campaigns')->everyMinute()->withoutOverlapping();
    }

    /**
     * Register the commands for the application.
     *
     * @return void
     */
    protected function commands()
    {
        $this->load(__DIR__.'/Commands');

        require base_path('routes/console.php');
    }
}
