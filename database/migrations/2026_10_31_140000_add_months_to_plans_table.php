<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/*
 * How many months a subscription plan (plans.type = accounts) runs, so the storefront can show the price per month
 * and compare plans. Empty = read from the plan name ("اشتراک 3 ماهه", "یکساله"), see App\Services\AccountOrders.
 */
return new class extends Migration
{
    public function up(): void
    {
        Schema::table('plans', function (Blueprint $table) {
            $table->unsignedSmallInteger('months')->nullable()->after('count')->comment('مدت اشتراک به ماه؛ خالی = از نام پلن');
        });
    }

    public function down(): void
    {
        Schema::table('plans', function (Blueprint $table) {
            $table->dropColumn('months');
        });
    }
};
