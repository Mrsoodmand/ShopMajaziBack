<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

// Admin-chosen items for the storefront header menu and the home page's shop window: premium accounts
// (products.type = accounts, at most 6 in the menu) and social platforms (categories.type = product, at most 4).
// Read by Api\StorefrontController::prices; nothing chosen = the site's built-in lists.
return new class extends Migration
{
    public function up(): void
    {
        Schema::table('products', function (Blueprint $table) {
            $table->boolean('in_menu')->default(false)->after('status');
            $table->boolean('on_home')->default(false)->after('in_menu');
        });
        Schema::table('categories', function (Blueprint $table) {
            $table->boolean('in_menu')->default(false)->after('featured');
            $table->boolean('on_home')->default(false)->after('in_menu');
        });
    }

    public function down(): void
    {
        Schema::table('products', fn (Blueprint $table) => $table->dropColumn(['in_menu', 'on_home']));
        Schema::table('categories', fn (Blueprint $table) => $table->dropColumn(['in_menu', 'on_home']));
    }
};
