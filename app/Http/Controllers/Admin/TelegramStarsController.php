<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Plan;
use App\Models\Product;
use App\Services\Pricing;
use App\Services\TelegramStars;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

/**
 * React admin › خدمات مجازی › تلگرام استارز: the dollar price per Star, the amounts a customer may type, the ready
 * plans and the on/off switch. Saved as the "telegram-stars" service (App\Services\TelegramStars), which is created on
 * the first save under the Telegram platform. Orders appear in admin › orders with the other services.
 */
class TelegramStarsController extends Controller
{
    public function show(TelegramStars $Stars)
    {
        $p = $Stars->product();
        $rate = Pricing::servicesRate();
        return response()->json([
            'rate' => $rate > 0 ? $rate : null,
            'platform' => (bool)$Stars->platform(),
            'stars' => $p ? [
                'live' => (int)$p->status === 1,
                'dollarPerStar' => (float)$p->price > 0 ? round((float)$p->price / 1000, 6) : null,
                'minCount' => (int)$p->min_count,
                'maxCount' => (int)$p->max_count,
                'plans' => Plan::where('product_id', $p->id)->orderBy('count')->get()->map(fn(Plan $pl) => [
                    'id' => $pl->id, 'name' => (string)$pl->name, 'count' => (int)$pl->count, 'live' => (int)$pl->status === 1,
                ])->values(),
            ] : null,
        ]);
    }

    public function save(Request $request, TelegramStars $Stars)
    {
        $data = $request->validate([
            'live' => 'boolean',
            'dollarPerStar' => 'required|numeric|gt:0|max:100',
            'minCount' => 'required|integer|min:1|max:10000000',
            'maxCount' => 'required|integer|gte:minCount|max:10000000',
            'plans' => 'array|max:30',
            'plans.*.id' => 'nullable|integer',
            'plans.*.name' => 'nullable|string|max:255',
            'plans.*.count' => 'required|integer|min:1|max:10000000',
            'plans.*.live' => 'boolean',
        ], [
            'dollarPerStar.required' => 'قیمت هر استار را وارد کنید.',
            'dollarPerStar.gt' => 'قیمت هر استار باید بیشتر از صفر باشد.',
            'dollarPerStar.numeric' => 'قیمت را به عدد وارد کنید، مثل 0.015',
            'minCount.required' => 'حداقل سفارش را وارد کنید.',
            'maxCount.required' => 'حداکثر سفارش را وارد کنید.',
            'maxCount.gte' => 'حداکثر باید از حداقل بیشتر باشد.',
            'plans.*.count.required' => 'تعداد استار پلن را وارد کنید.',
            'plans.*.count.min' => 'تعداد استار پلن باید دست‌کم ۱ باشد.',
        ]);
        $Platform = $Stars->platform();
        if (!$Platform) {
            return response()->json(['message' => 'پلتفرم تلگرام (نامک telegram) در خدمات مجازی پیدا نشد.'], 422);
        }

        $p = $Stars->product() ?? new Product();
        DB::transaction(function () use ($p, $data, $Platform) {
            $old = json_decode((string)$p->detail, true) ?: [];
            $p->forceFill([
                'type' => 'service',
                'slug' => TelegramStars::SLUG,
                'faName' => $p->faName ?: 'استارز تلگرام',
                'country_id' => $Platform->id,
                'status' => ($data['live'] ?? false) ? 1 : 0,
                // Services keep dollars per 1,000 in `price`.
                'price' => rtrim(rtrim(number_format((float)$data['dollarPerStar'] * 1000, 6, '.', ''), '0'), '.'),
                'price_per_500' => null,
                'price_per_100' => null,
                'min_count' => (int)$data['minCount'],
                'max_count' => (int)$data['maxCount'],
                'detail' => json_encode(array_merge(['name' => 'استارز تلگرام', 'enName' => 'Telegram Stars'], $old, ['stars' => true]), JSON_UNESCAPED_UNICODE),
                'filds' => json_encode([['type' => 'text', 'title' => 'یوزرنیم تلگرام', 'col' => '12', 'filed_name' => TelegramStars::FIELD, 'select_value' => 'null']], JSON_UNESCAPED_UNICODE),
                'notes' => $p->notes ?: '{}',
                'faq' => $p->faq ?: '[]',
            ])->save();

            $keep = [];
            foreach (collect($data['plans'] ?? [])->sortBy('count')->values() as $i => $row) {
                $plan = !empty($row['id']) ? Plan::where('product_id', $p->id)->find($row['id']) : null;
                $plan ??= new Plan();
                $plan->forceFill([
                    'product_id' => (string)$p->id,
                    'name' => trim((string)($row['name'] ?? '')) ?: number_format($row['count']) . ' استار',
                    'count' => $row['count'],
                    'status' => ($row['live'] ?? true) ? 1 : 0,
                    'sort' => $i,
                    'type' => $plan->type ?? 'social',
                ])->save();
                $keep[] = $plan->id;
            }
            Plan::where('product_id', $p->id)->whereNotIn('id', $keep)->delete();
        });

        return response()->json(['success' => 'تنظیمات استارز ذخیره شد.']);
    }
}
