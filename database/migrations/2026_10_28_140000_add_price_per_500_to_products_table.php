<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/*
 * Any-amount pricing for virtual services in dollars: the customer picks any count between min_count and max_count
 * in steps of 500 and pays (count ÷ 500) × the toman price of 500 (price_per_500 dollars at the services rate,
 * currency #20). Set in React admin › Virtual services › service › Price. Replaces price_per_100 (toman per 100),
 * which no service uses; the storefront still reads it for services the old admin set up that way.
 */
return new class extends Migration
{
    public function up(): void
    {
        Schema::table('products', function (Blueprint $table) {
            $table->decimal('price_per_500', 12, 4)->nullable()->after('price_per_100')->comment('دلار به ازای هر ۵۰۰ عدد');
        });
    }

    public function down(): void
    {
        Schema::table('products', function (Blueprint $table) {
            $table->dropColumn('price_per_500');
        });
    }
};
