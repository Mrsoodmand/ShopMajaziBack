<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

// products.addons: an account's add-on questions (App\Services\AccountOrders::addons), JSON. Null = none.
return new class extends Migration
{
    public function up(): void
    {
        if (!Schema::hasColumn('products', 'addons')) {
            Schema::table('products', fn (Blueprint $t) => $t->text('addons')->nullable());
        }
    }

    public function down(): void
    {
        if (Schema::hasColumn('products', 'addons')) {
            Schema::table('products', fn (Blueprint $t) => $t->dropColumn('addons'));
        }
    }
};
