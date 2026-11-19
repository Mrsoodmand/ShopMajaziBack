<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

// The order a comment was written from (App\Services\OrderReviews): buyers are asked for a comment on their delivered
// order's page, and such comments are shown as from a verified buyer. Null for comments from the public form.
return new class extends Migration
{
    public function up(): void
    {
        if (!Schema::hasColumn('comments', 'order_id')) {
            Schema::table('comments', function (Blueprint $table) {
                $table->unsignedBigInteger('order_id')->nullable()->after('user_id')->index();
            });
        }
    }

    public function down(): void
    {
        if (Schema::hasColumn('comments', 'order_id')) {
            Schema::table('comments', function (Blueprint $table) {
                $table->dropIndex(['order_id']);
                $table->dropColumn('order_id');
            });
        }
    }
};
