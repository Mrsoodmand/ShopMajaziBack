<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

/*
 * Official invoices (فاکتور رسمی) for organisation accounts (App\Services\Organizations).
 *
 * - The form gets a built-in yes/no question "official_invoice". Yes: the tax code becomes required and every
 *   purchase of that company carries VAT; no: the tax code is optional and nothing is added.
 * - users.org_vat: this company's VAT percent (null = no official invoice, no VAT). An approval starts from
 *   settings › organization › vat (default 12).
 * - orders.vat_percent / vat_price: the VAT one order carried (Toman, already inside orders.price), for the invoice.
 */
return new class extends Migration
{
    public function up(): void
    {
        Schema::table('users', function (Blueprint $table) {
            $table->decimal('org_vat', 5, 2)->nullable()->after('org_discounts');
        });
        Schema::table('organization_requests', function (Blueprint $table) {
            $table->boolean('official_invoice')->nullable()->after('tax_code');
        });
        Schema::table('orders', function (Blueprint $table) {
            $table->decimal('vat_percent', 5, 2)->nullable();
            $table->unsignedBigInteger('vat_price')->nullable();
        });

        $now = now();
        if (!DB::table('organization_fields')->where('key', 'official_invoice')->exists()) {
            DB::table('organization_fields')->insert([
                'key' => 'official_invoice', 'title' => 'آیا فاکتور رسمی نیاز دارید؟', 'title_en' => 'Do you need an official invoice?',
                'type' => 'yesno', 'required' => true, 'enabled' => true, 'builtin' => true, 'sort' => 0,
                'created_at' => $now, 'updated_at' => $now,
            ]);
        }
    }

    public function down(): void
    {
        DB::table('organization_fields')->where('key', 'official_invoice')->delete();
        Schema::table('orders', fn (Blueprint $table) => $table->dropColumn(['vat_percent', 'vat_price']));
        Schema::table('organization_requests', fn (Blueprint $table) => $table->dropColumn('official_invoice'));
        Schema::table('users', fn (Blueprint $table) => $table->dropColumn('org_vat'));
    }
};
