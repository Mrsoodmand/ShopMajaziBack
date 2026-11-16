<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

// Money an order still asks the customer for (App\Services\OrderCharges):
//   kind "order" = the price of an order support made for the customer (admin › سفارش جدید) that the customer pays;
//   kind "extra" = an amount added to any order later (tax, VAT, a price difference…), with its reason.
// Paying one writes an ordinary type-1 payment on the order (gate Wallet), so the transactions list, the customer's
// statement and refunds see it like any other. Card payments top the wallet up first (a type-3 payment).
// Also: orders.night_call (may support call 21:00–07:00? null = not asked) and orders.admin_id (made by support).
return new class extends Migration
{
    public function up(): void
    {
        if (!Schema::hasTable('order_charges')) {
            Schema::create('order_charges', function (Blueprint $t) {
                $t->id();
                $t->unsignedBigInteger('order_id')->index();
                $t->unsignedBigInteger('user_id')->index();
                $t->string('kind', 10); // order | extra
                $t->string('title', 190); // what it is for, shown to the customer
                $t->unsignedInteger('price'); // Toman
                $t->string('status', 12)->default('unpaid'); // unpaid | paid | cancelled
                $t->unsignedBigInteger('payment_id')->nullable(); // the type-1 payment that paid it
                $t->unsignedBigInteger('topup_id')->nullable()->index(); // card: the type-3 top-up started for it
                $t->unsignedBigInteger('admin_id')->nullable();
                $t->timestamp('paid_at')->nullable();
                $t->timestamps();
            });
        }
        Schema::table('orders', function (Blueprint $t) {
            if (!Schema::hasColumn('orders', 'night_call')) {
                $t->boolean('night_call')->nullable();
            }
            if (!Schema::hasColumn('orders', 'admin_id')) {
                $t->unsignedBigInteger('admin_id')->nullable();
            }
        });
    }

    public function down(): void
    {
        Schema::table('orders', function (Blueprint $t) {
            foreach (['night_call', 'admin_id'] as $c) {
                if (Schema::hasColumn('orders', $c)) {
                    $t->dropColumn($c);
                }
            }
        });
        Schema::dropIfExists('order_charges');
    }
};
