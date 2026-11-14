<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

// Payment gateways the admin manages (admin › درگاه‌های پرداخت, App\Services\Payments\GatewayManager): on/off, the
// order they are tried in, the merchant ID (encrypted with APP_KEY) and test mode. payments.gateway records which
// one a payment went through.
// Starting state keeps today's behaviour: Zarinpal on (its merchant ID still comes from .env until one is saved
// here), Zibal added but off until its merchant ID is entered.
return new class extends Migration
{
    public function up(): void
    {
        if (!Schema::hasTable('payment_gateways')) {
            Schema::create('payment_gateways', function (Blueprint $t) {
                $t->string('key', 30)->primary();
                $t->boolean('enabled')->default(false);
                $t->unsignedSmallInteger('sort')->default(0);
                $t->text('merchant')->nullable(); // encrypted
                $t->boolean('sandbox')->default(false);
                $t->timestamp('tested_at')->nullable();
                $t->string('test_result', 255)->nullable();
                $t->timestamps();
            });
        }
        if (!Schema::hasColumn('payments', 'gateway')) {
            Schema::table('payments', fn (Blueprint $t) => $t->string('gateway', 30)->nullable()->after('gate'));
        }
        foreach ([['zibal', false, 1], ['zarinpal', true, 2]] as [$key, $enabled, $sort]) {
            DB::table('payment_gateways')->insertOrIgnore([
                'key' => $key, 'enabled' => $enabled, 'sort' => $sort, 'created_at' => now(), 'updated_at' => now(),
            ]);
        }
    }

    public function down(): void
    {
        Schema::dropIfExists('payment_gateways');
        if (Schema::hasColumn('payments', 'gateway')) {
            Schema::table('payments', fn (Blueprint $t) => $t->dropColumn('gateway'));
        }
    }
};
