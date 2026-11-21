<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

// plans.profit_percent: one plan's own profit (App\Services\Pricing::planRate). Null = the product's profit, else
// the dollar's — so adding the column changes no price.
return new class extends Migration
{
    public function up(): void
    {
        if (!Schema::hasColumn('plans', 'profit_percent')) {
            Schema::table('plans', fn (Blueprint $t) => $t->decimal('profit_percent', 7, 2)->nullable()->after('discount'));
        }
    }

    public function down(): void
    {
        if (Schema::hasColumn('plans', 'profit_percent')) {
            Schema::table('plans', fn (Blueprint $t) => $t->dropColumn('profit_percent'));
        }
    }
};
