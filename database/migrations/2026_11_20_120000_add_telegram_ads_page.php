<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

// A `pages` row for the Telegram Ads page, found by slug, so its customer comments have something to belong to
// (comments.type = page, post_id = its id; see Api\StorefrontCommentController::SLUG_PAGES). admin › SEO uses the
// same row and may already have made it when the page's SEO was saved.
return new class extends Migration
{
    public function up(): void
    {
        if (!DB::table('pages')->where('slug', 'telegram-ads')->exists()) {
            DB::table('pages')->insert([
                'title' => 'تبلیغات تلگرام', 'slug' => 'telegram-ads', 'long_detail' => '', 'related_blogs' => '',
                'created_at' => now(), 'updated_at' => now(),
            ]);
        }
    }

    public function down(): void
    {
        // Kept: admin › SEO and comments may use the row.
    }
};
