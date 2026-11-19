<?php

namespace App\Services;

use App\Http\Controllers\Api\StorefrontCommentController;
use App\Models\Comment;
use App\Models\Order;
use App\Models\Pages;
use App\Models\Product;

/**
 * Comments from buyers, asked for on a delivered order's page (Api\StorefrontPanelController::review).
 *
 * A buyer may write one comment per delivered order. It goes to the same `comments` queue as the public form
 * (status Pending, moderated in the admin), on the page of what was bought, with the buyer's account, mobile and the
 * order (comments.order_id), so it can be shown as from a verified buyer.
 *
 * What was bought decides where the comment goes, matching the storefront's keys (Front/src/lib/comments.ts):
 * numbers, Apple IDs, static IPs, Google Voice and Telegram Stars have one page each; subscriptions and Telegram
 * Premium (type 7) are each their own product. Social services and support's custom orders have no comment page,
 * so they are not asked.
 */
class OrderReviews
{
    /** ['page', 4] / ['service', 3293] for a delivered order whose page takes comments; null otherwise. */
    public function target(Order $Order, ?int $ProductId, ?Product $Product): ?array
    {
        if ($Order->status !== 'complete') {
            return null;
        }
        $Page = match ((int)$Order->type) {
            1 => 'virtual-number',
            3, 4 => 'apple-id',
            5 => 'static-ip',
            6 => 'google-voice',
            2 => $Product && TelegramStars::isStars($Product) ? 'telegram-stars' : null,
            default => null,
        };
        if ($Page) {
            $Id = StorefrontCommentController::PAGES[$Page] ?? null;
            return $Id && Pages::whereKey($Id)->exists() ? ['page', $Id] : null;
        }
        if ((int)$Order->type === 7 && $ProductId) {
            $Product ??= Product::find($ProductId);
            return $Product && (int)$Product->status === 1 ? ['service', (int)$Product->id] : null;
        }
        return null;
    }

    /** The ids (as keys) of the given orders that already have a buyer comment. */
    public function reviewed(iterable $OrderIds): array
    {
        $Ids = collect($OrderIds)->filter()->values();
        return $Ids->isEmpty() ? [] : Comment::whereIn('order_id', $Ids)->where('parent_id', 0)->pluck('order_id')->flip()->all();
    }

    /** What the order page shows: null (not asked), ['state' => 'open'] or ['state' => 'sent']. */
    public function state(Order $Order, ?int $ProductId, ?Product $Product, array $Reviewed): ?array
    {
        if (isset($Reviewed[$Order->id])) {
            return ['state' => 'sent'];
        }
        return $this->target($Order, $ProductId, $Product) ? ['state' => 'open'] : null;
    }

    /** Saves the buyer's comment as Pending. [ok, code, message]. */
    public function save($User, Order $Order, ?int $ProductId, ?Product $Product, string $Name, string $Text): array
    {
        $Target = $this->target($Order, $ProductId, $Product);
        if (!$Target) {
            return [false, 'not_open', 'برای این سفارش امکان ثبت دیدگاه نیست.'];
        }
        if (Comment::where('order_id', $Order->id)->where('parent_id', 0)->exists()) {
            return [false, 'already', 'دیدگاه شما برای این سفارش قبلاً ثبت شده است.'];
        }
        [$Type, $Id] = $Target;
        $Comment = new Comment();
        $Comment->type = $Type;
        $Comment->post_id = (string)$Id;
        $Comment->parent_id = 0;
        $Comment->user_id = (string)$User->id;
        $Comment->mobile = (string)$User->mobile;
        $Comment->order_id = $Order->id;
        $Comment->name = strip_tags(trim($Name));
        $Comment->text = strip_tags(trim($Text));
        $Comment->status = 'Pending';
        $Comment->save();
        return [true, 'ok', 'ممنون! دیدگاه شما ثبت شد و پس از بررسی نمایش داده می‌شود.'];
    }
}
