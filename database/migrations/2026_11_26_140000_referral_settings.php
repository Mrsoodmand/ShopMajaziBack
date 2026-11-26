<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

/*
 * Referral (معرفی دوستان, App\Services\Referrals).
 *
 * - users.referral_percents: this referrer's own commission percents {family: percent}; a family not listed uses
 *   settings › referral.
 * - settings › referral starts switched off, with the percents the old fixed code used (10% numbers, Apple ID,
 *   static IP, Google Voice; 5% social services, subscriptions, verification), so nothing is paid until an admin
 *   turns it on in admin › معرفی دوستان.
 * - User #10 always earned 5% in the old code (helper CheckRef): kept as their own percents.
 */
return new class extends Migration
{
    public function up(): void
    {
        Schema::table('users', function (Blueprint $table) {
            $table->json('referral_percents')->nullable();
        });

        if (!DB::table('settings')->where('name', 'referral')->exists()) {
            $now = now();
            DB::table('settings')->insert([
                'name' => 'referral',
                'key' => json_encode([
                    'enabled' => false,
                    'percents' => ['numbers' => 10, 'services' => 5, 'apple_id' => 10, 'static_ip' => 10, 'google_voice' => 10, 'subscriptions' => 5, 'verification' => 5],
                ]),
                'created_at' => $now,
                'updated_at' => $now,
            ]);
        }

        if (DB::table('users')->where('id', 10)->exists()) {
            DB::table('users')->where('id', 10)->update(['referral_percents' => json_encode(
                ['numbers' => 5, 'services' => 5, 'apple_id' => 5, 'static_ip' => 5, 'google_voice' => 5, 'subscriptions' => 5, 'verification' => 5]
            )]);
        }
    }

    public function down(): void
    {
        DB::table('settings')->where('name', 'referral')->delete();
        Schema::table('users', fn (Blueprint $table) => $table->dropColumn('referral_percents'));
    }
};
