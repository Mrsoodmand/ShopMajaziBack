<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/*
 * Bulk SMS sends from admin › پیامک (App\Services\Sms, App\Jobs\SendSmsCampaign). The numbers are stored with the
 * send so a send that stopped half-way (server restart, timeout) picks up at `next_offset` (sms:resume-campaigns).
 */
return new class extends Migration
{
    public function up(): void
    {
        Schema::create('sms_campaigns', function (Blueprint $table) {
            $table->id();
            $table->unsignedInteger('admin_id')->nullable();
            $table->text('message');
            $table->string('line', 30);
            $table->string('audience', 20);            // all|buyers|recent|custom
            $table->unsignedSmallInteger('audience_days')->nullable();
            $table->longText('mobiles');               // JSON list, normalised 9xxxxxxxxx
            $table->unsignedInteger('total');
            $table->unsignedInteger('next_offset')->default(0);
            $table->unsignedInteger('accepted')->default(0);
            $table->unsignedInteger('rejected')->default(0); // blacklisted or invalid numbers, or failed batches
            $table->decimal('cost', 12, 2)->default(0);
            $table->string('status', 20)->default('sending'); // sending|done|failed|cancelled
            $table->string('error')->nullable();
            $table->json('pack_ids')->nullable();
            $table->timestamp('finished_at')->nullable();
            $table->timestamps();
            $table->index(['status', 'updated_at']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('sms_campaigns');
    }
};
