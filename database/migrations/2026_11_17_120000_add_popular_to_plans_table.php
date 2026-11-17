<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/*
 * The plan the storefront marks «محبوب‌ترین» (most popular) on a subscription's page. One per product at most;
 * the admin picks it in admin › اکانت‌های خارجی › پلن‌ها.
 */
return new class extends Migration
{
    public function up(): void
    {
        Schema::table('plans', function (Blueprint $table) {
            $table->boolean('popular')->default(false)->after('months')->comment('نشان «محبوب‌ترین» در سایت');
        });
    }

    public function down(): void
    {
        Schema::table('plans', function (Blueprint $table) {
            $table->dropColumn('popular');
        });
    }
};
