<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

/*
 * Account requests (App\Services\AccountRequests): a signed-in customer asks for a subscription the site doesn't
 * sell (e.g. qq.com) by filling in the form the admin set up (account_request_fields). Support answers with a quote —
 * an ordinary custom order (type 9, App\Services\OrderCharges) the customer pays in /account — or rejects it with a
 * note. settings › account_request keeps {open}.
 */
return new class extends Migration
{
    public function up(): void
    {
        Schema::create('account_request_fields', function (Blueprint $table) {
            $table->id();
            $table->string('key', 50)->unique();      // answers are stored under this key
            $table->string('title');
            $table->string('title_en')->nullable();
            $table->string('type', 20);                // text|email|phone|textarea|number|select|yesno|date|file
            $table->boolean('required')->default(true);
            $table->boolean('enabled')->default(true);
            $table->boolean('builtin')->default(false); // site_name / site_url: can be retitled, not deleted
            $table->string('hint')->nullable();
            $table->string('hint_en')->nullable();
            $table->json('options')->nullable();
            $table->json('accept')->nullable();
            $table->unsignedTinyInteger('max_mb')->nullable();
            $table->unsignedInteger('sort')->default(0);
            $table->timestamps();
        });

        Schema::create('account_requests', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('user_id')->index();
            $table->string('status', 20)->default('pending')->index(); // pending|quoted|rejected|cancelled
            $table->string('site_name');
            $table->string('site_url')->nullable();
            $table->json('answers');                   // snapshot: [{key, title, type, value, file?, mime?, size?}]
            $table->unsignedBigInteger('order_id')->nullable(); // the quote: a type-9 order
            $table->text('admin_note')->nullable();    // shown to the customer with the quote or the rejection
            $table->unsignedBigInteger('reviewed_by')->nullable();
            $table->timestamp('reviewed_at')->nullable();
            $table->timestamps();
        });

        $now = now();
        // site_name and site_url are built in (retitled or made optional, never deleted); the other two are a start.
        $fields = [
            ['site_name', 'نام سایت یا سرویس', 'Site or service name', 'text', true, 'مثلا QQ یا Notion'],
            ['site_url', 'آدرس سایت', 'Website address', 'text', false, 'مثلا qq.com'],
            ['plan', 'پلن یا مدت اشتراک', 'Plan or length', 'text', false, 'مثلا پرمیوم یک‌ماهه'],
            ['details', 'توضیحات', 'Details', 'textarea', false, 'هر چیزی که بهتر است بدانیم'],
        ];
        DB::table('account_request_fields')->insert(array_map(fn ($f, $i) => [
            'key' => $f[0], 'title' => $f[1], 'title_en' => $f[2], 'type' => $f[3], 'required' => $f[4], 'hint' => $f[5],
            'enabled' => true, 'builtin' => in_array($f[0], ['site_name', 'site_url'], true), 'sort' => $i + 1,
            'created_at' => $now, 'updated_at' => $now,
        ], $fields, array_keys($fields)));

        DB::table('settings')->insert(['name' => 'account_request', 'key' => json_encode(['open' => true]), 'created_at' => $now, 'updated_at' => $now]);
    }

    public function down(): void
    {
        Schema::dropIfExists('account_requests');
        Schema::dropIfExists('account_request_fields');
        DB::table('settings')->where('name', 'account_request')->delete();
    }
};
