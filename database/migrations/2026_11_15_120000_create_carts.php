<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

// The storefront's shopping cart (Api\StorefrontCartController). A cart holds lines that are each one ordinary
// purchase (the same body the product's own buy endpoint takes); checking out makes one ordinary order per line,
// paid from the wallet, and links them with orders.checkout_id. Paying by card tops the wallet up with the total
// first (a type-3 payment) and then runs the same wallet checkout.
return new class extends Migration
{
    public function up(): void
    {
        if (!Schema::hasTable('carts')) {
            Schema::create('carts', function (Blueprint $t) {
                $t->id();
                $t->unsignedBigInteger('user_id')->nullable()->unique();
                $t->string('session_key', 64)->nullable()->unique(); // guests: a random key kept in the session
                $t->timestamps();
            });
        }
        if (!Schema::hasTable('cart_items')) {
            Schema::create('cart_items', function (Blueprint $t) {
                $t->id();
                $t->foreignId('cart_id')->constrained('carts')->cascadeOnDelete();
                $t->string('kind', 20); // service | subscription | apple_id | google_voice
                $t->text('payload'); // encrypted JSON: may hold an Apple ID password
                $t->json('label'); // {title, detail} as the page showed them, for display only
                $t->unsignedInteger('price'); // last quoted, Toman
                $t->timestamps();
            });
        }
        if (!Schema::hasTable('cart_checkouts')) {
            Schema::create('cart_checkouts', function (Blueprint $t) {
                $t->id();
                $t->unsignedBigInteger('user_id')->index();
                $t->string('method', 10); // wallet | card
                $t->unsignedInteger('total');
                $t->unsignedBigInteger('payment_id')->nullable()->index(); // card: the top-up payment
                $t->string('status', 20); // waiting_to_pay | done | partial | failed | cancelled
                $t->text('items'); // encrypted JSON: the lines, then each line's order and result
                $t->timestamps();
            });
        }
        if (!Schema::hasColumn('orders', 'checkout_id')) {
            Schema::table('orders', fn (Blueprint $t) => $t->unsignedBigInteger('checkout_id')->nullable()->index());
        }
    }

    public function down(): void
    {
        if (Schema::hasColumn('orders', 'checkout_id')) {
            Schema::table('orders', fn (Blueprint $t) => $t->dropColumn('checkout_id'));
        }
        Schema::dropIfExists('cart_checkouts');
        Schema::dropIfExists('cart_items');
        Schema::dropIfExists('carts');
    }
};
