<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/*
 * The storefront blog filters posts by topic (App\Services\BlogTopics). Set in the React admin's post editor;
 * empty means "guess it from the slug and title".
 */
return new class extends Migration
{
    public function up(): void
    {
        Schema::table('blogs', function (Blueprint $table) {
            $table->string('topic', 20)->nullable()->after('type')->comment('guides|numbers|apple|telegram|social|news، خالی = حدس خودکار');
        });
    }

    public function down(): void
    {
        Schema::table('blogs', function (Blueprint $table) {
            $table->dropColumn('topic');
        });
    }
};
