<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

// Profit percentages on top of market rates (App\Services\Pricing):
//  - currency.profit_percent: the default profit for prices in that currency (dollar → subscriptions, services).
//  - products.profit_percent: a product's own profit instead of the dollar's (null = use the dollar's).
//  - number_operator_profits: the profit on each virtual-number operator's cost (PVA, SMS-Man …).
// Starting values keep today's prices: the dollar takes the markup of the old "PayPal dollar" row (#20, +10%)
// that subscriptions and services used, and the operators the multipliers that were in the code (PVA ×2 = +100%,
// SMS-Man and SMS-Activate ×2.5 = +150%).
return new class extends Migration
{
    public function up(): void
    {
        if (!Schema::hasColumn('currency', 'profit_percent')) {
            Schema::table('currency', fn (Blueprint $t) => $t->decimal('profit_percent', 7, 2)->default(0)->after('buy'));
        }
        if (!Schema::hasColumn('products', 'profit_percent')) {
            Schema::table('products', fn (Blueprint $t) => $t->decimal('profit_percent', 7, 2)->nullable());
        }
        if (!Schema::hasTable('number_operator_profits')) {
            Schema::create('number_operator_profits', function (Blueprint $t) {
                $t->string('operator', 20)->primary();
                $t->decimal('profit_percent', 7, 2);
                $t->timestamps();
            });
        }

        // Dollar: the markup #20 added (percent, on top), else 10.
        $paypal = DB::table('currency')->where('id', 20)->first();
        $dollarProfit = ($paypal && $paypal->sell_cal === 'percent' && (string) $paypal->sell_action === '1' && is_numeric($paypal->sell))
            ? (float) $paypal->sell : 10.0;
        DB::table('currency')->where('id', 1)->where('profit_percent', 0)->update(['profit_percent' => $dollarProfit]);

        foreach (['pva' => 100, 'man' => 150, 'active' => 150] as $operator => $profit) {
            DB::table('number_operator_profits')->insertOrIgnore([
                'operator' => $operator, 'profit_percent' => $profit, 'created_at' => now(), 'updated_at' => now(),
            ]);
        }
    }

    public function down(): void
    {
        Schema::dropIfExists('number_operator_profits');
        if (Schema::hasColumn('products', 'profit_percent')) {
            Schema::table('products', fn (Blueprint $t) => $t->dropColumn('profit_percent'));
        }
        if (Schema::hasColumn('currency', 'profit_percent')) {
            Schema::table('currency', fn (Blueprint $t) => $t->dropColumn('profit_percent'));
        }
    }
};
