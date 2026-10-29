<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/*
 * Custom-quantity pricing for virtual services: the customer picks any count between min and max
 * (in steps of 100) and pays count / 100 * price_per_100. Products without price_per_100 keep using plans.
 */
class AddCountPricingToProductsTable extends Migration
{
    public function up()
    {
        Schema::table('products', function (Blueprint $table) {
            $table->unsignedInteger('price_per_100')->nullable()->comment('تومان به ازای هر ۱۰۰ عدد');
            $table->unsignedInteger('min_count')->default(100);
            $table->unsignedInteger('max_count')->default(10000);
        });
    }

    public function down()
    {
        Schema::table('products', function (Blueprint $table) {
            $table->dropColumn(['price_per_100', 'min_count', 'max_count']);
        });
    }
}
