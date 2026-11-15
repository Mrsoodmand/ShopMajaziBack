<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

// Admin › ارزها و سود: each currency's market rate is either automatic (AriaPay, every 30 minutes) or typed in by an
// admin (manual). currency.sell stays the rate every price reads; feed_rate keeps AriaPay's latest value to compare
// with while a currency is manual. Every row starts automatic, so nothing changes until an admin switches one.
return new class extends Migration
{
    public function up(): void
    {
        Schema::table('currency', function (Blueprint $t) {
            if (!Schema::hasColumn('currency', 'rate_mode')) {
                $t->string('rate_mode', 10)->default('auto');
            }
            if (!Schema::hasColumn('currency', 'rate_mode_at')) {
                $t->timestamp('rate_mode_at')->nullable();
            }
            if (!Schema::hasColumn('currency', 'feed_rate')) {
                $t->unsignedBigInteger('feed_rate')->nullable();
            }
        });
    }

    public function down(): void
    {
        Schema::table('currency', function (Blueprint $t) {
            foreach (['rate_mode', 'rate_mode_at', 'feed_rate'] as $c) {
                if (Schema::hasColumn('currency', $c)) {
                    $t->dropColumn($c);
                }
            }
        });
    }
};
