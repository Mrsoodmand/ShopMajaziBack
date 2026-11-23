<?php

namespace App\Providers;

use Illuminate\Support\ServiceProvider;

class AppServiceProvider extends ServiceProvider
{
    /**
     * Register any application services.
     *
     * @return void
     */
    public function register()
    {
        //
    }

    /**
     * Bootstrap any application services.
     *
     * @return void
     */
    public function boot()
    {
        // «ثبت سفارش تازه» SMS (admin › پیامک): once, when an order first becomes paid (gateway or wallet).
        // Sends nothing until the pattern has a template id.
        \App\Models\Order::saved(function (\App\Models\Order $Order) {
            $Paid = \App\Services\Sms::paidStatuses();
            if (!in_array($Order->pay_status, $Paid, true)) return;
            if (!$Order->wasRecentlyCreated && (!$Order->wasChanged('pay_status') || in_array($Order->getOriginal('pay_status'), $Paid, true))) return;
            app(\App\Services\Sms::class)->notifyUser($Order->user_id, 'new_order', ['NUM' => $Order->id]);
        });
    }
}
