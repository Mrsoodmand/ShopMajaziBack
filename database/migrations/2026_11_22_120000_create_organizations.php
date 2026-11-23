<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

/*
 * Organisation accounts (App\Services\Organizations). A customer asks to turn their personal account into an
 * organisation account by filling in the form the admin set up (organization_fields); an admin approves or rejects
 * the request (organization_requests). Approved users get users.type = organization and their own percent per
 * product family (users.org_discounts, {services: 10, verification: 5, …}; a family not listed gets nothing).
 * settings › organization keeps {open, defaults: {family: percent}} — the percents an approval starts from.
 * Orders bought with that discount carry orders.discount_code = organization and discount_price = the Toman taken off.
 */
return new class extends Migration
{
    public function up(): void
    {
        Schema::table('users', function (Blueprint $table) {
            $table->json('org_discounts')->nullable()->after('type');
        });

        Schema::create('organization_fields', function (Blueprint $table) {
            $table->id();
            $table->string('key', 50)->unique();      // answers are stored under this key
            $table->string('title');
            $table->string('title_en')->nullable();
            $table->string('type', 20);                // text|email|phone|textarea|number|select|yesno|date|file
            $table->boolean('required')->default(true);
            $table->boolean('enabled')->default(true);
            $table->boolean('builtin')->default(false); // the five company fields: can be switched off, not deleted
            $table->string('hint')->nullable();
            $table->string('hint_en')->nullable();
            $table->json('options')->nullable();       // select choices
            $table->json('accept')->nullable();        // file: image|pdf
            $table->unsignedTinyInteger('max_mb')->nullable();
            $table->unsignedInteger('sort')->default(0);
            $table->timestamps();
        });

        Schema::create('organization_requests', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('user_id')->index();
            $table->string('status', 20)->default('pending'); // pending|approved|rejected|cancelled
            $table->string('company_name')->nullable();
            $table->string('company_email')->nullable();
            $table->string('company_phone', 30)->nullable();
            $table->text('company_address')->nullable();
            $table->string('tax_code', 30)->nullable();
            $table->json('answers');                   // snapshot: [{key, title, type, value, file?, mime?, size?}]
            $table->text('admin_note')->nullable();    // shown to the customer on reject / cancel
            $table->unsignedBigInteger('reviewed_by')->nullable();
            $table->timestamp('reviewed_at')->nullable();
            $table->timestamps();
        });

        $now = now();
        $fields = [
            ['company_name', 'نام شرکت', 'Company name', 'text', 1],
            ['company_email', 'ایمیل شرکت', 'Company email', 'email', 2],
            ['company_phone', 'تلفن شرکت', 'Company phone', 'phone', 3],
            ['company_address', 'آدرس شرکت', 'Company address', 'textarea', 4],
            ['tax_code', 'کد اقتصادی / شناسه ملی', 'Tax code', 'text', 5],
        ];
        DB::table('organization_fields')->insert(array_map(fn ($f) => [
            'key' => $f[0], 'title' => $f[1], 'title_en' => $f[2], 'type' => $f[3], 'required' => true, 'enabled' => true,
            'builtin' => true, 'sort' => $f[4], 'created_at' => $now, 'updated_at' => $now,
        ], $fields));

        DB::table('settings')->insert([
            'name' => 'organization',
            'key' => json_encode(['open' => true, 'defaults' => new \stdClass()]),
            'created_at' => $now, 'updated_at' => $now,
        ]);
    }

    public function down(): void
    {
        Schema::dropIfExists('organization_requests');
        Schema::dropIfExists('organization_fields');
        Schema::table('users', fn (Blueprint $table) => $table->dropColumn('org_discounts'));
        DB::table('settings')->where('name', 'organization')->delete();
    }
};
