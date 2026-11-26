<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/*
 * - users.org_note: support's own note about an organisation (admin only, the customer never sees it).
 * - external_cards: the shop's own cards support pays outside services with (admin › کارت‌های خارجی, super-admin).
 *   Number and expiry are encrypted (App\Models\ExternalCard casts); last4 stays plain for pickers. No CVV is kept.
 * - orders.external_card_id: which of those cards paid for an account order (type 7), chosen in admin › order.
 */
return new class extends Migration
{
    public function up(): void
    {
        Schema::table('users', function (Blueprint $table) {
            $table->text('org_note')->nullable()->after('org_vat');
        });
        Schema::create('external_cards', function (Blueprint $table) {
            $table->id();
            $table->string('title', 100);
            $table->text('number');            // encrypted
            $table->text('expiry');            // encrypted, as typed (08/28, 1405/06)
            $table->string('last4', 4);
            $table->boolean('active')->default(true);
            $table->string('note', 255)->nullable();
            $table->timestamps();
        });
        Schema::table('orders', function (Blueprint $table) {
            $table->unsignedBigInteger('external_card_id')->nullable()->index();
        });
    }

    public function down(): void
    {
        Schema::table('orders', fn (Blueprint $table) => $table->dropColumn('external_card_id'));
        Schema::dropIfExists('external_cards');
        Schema::table('users', fn (Blueprint $table) => $table->dropColumn('org_note'));
    }
};
