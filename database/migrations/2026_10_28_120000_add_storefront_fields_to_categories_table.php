<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/*
 * What the storefront's social media services list needs per category (platform), set in the React admin:
 * a brand colour for the logo tile, the admin's sort order, whether it is featured (shown first, larger), and the
 * one-line tagline under the name in Persian and English (short_detail stays the old site's SEO text).
 */
return new class extends Migration
{
    public function up(): void
    {
        Schema::table('categories', function (Blueprint $table) {
            $table->string('color', 20)->nullable()->comment('رنگ برند، مثل #0ea5e9');
            $table->integer('sort')->default(0);
            $table->boolean('featured')->default(false);
            $table->string('tagline')->nullable()->comment('یک خط زیر نام، فارسی');
            $table->string('tagline_en')->nullable()->comment('one line under the name, English');
        });
    }

    public function down(): void
    {
        Schema::table('categories', function (Blueprint $table) {
            $table->dropColumn(['color', 'sort', 'featured', 'tagline', 'tagline_en']);
        });
    }
};
