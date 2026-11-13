<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

// `pages` rows for the storefront pages that had no row on the old site, so their customer comments have something
// to belong to (comments.type = page, post_id = these ids; see Api\StorefrontCommentController::PAGES).
return new class extends Migration
{
    private array $rows = [
        8 => 'اجاره شماره مجازی',
        9 => 'تلگرام استارز',
    ];

    public function up(): void
    {
        foreach ($this->rows as $id => $title) {
            if (!DB::table('pages')->where('id', $id)->exists()) {
                DB::table('pages')->insert([
                    'id' => $id, 'title' => $title, 'long_detail' => '', 'related_blogs' => '',
                    'created_at' => now(), 'updated_at' => now(),
                ]);
            }
        }
    }

    public function down(): void
    {
        DB::table('pages')->whereIn('id', array_keys($this->rows))->whereNotExists(
            fn ($q) => $q->select(DB::raw(1))->from('comments')->where('type', 'page')->whereColumn('comments.post_id', 'pages.id')
        )->delete();
    }
};
