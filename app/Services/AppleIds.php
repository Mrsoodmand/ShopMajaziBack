<?php

namespace App\Services;

use App\lib\PhpEncrypt;
use App\Models\AppleId;
use App\Models\Order;
use App\Models\OrderItem;
use App\Models\Pages;
use App\Models\Payment;
use Carbon\Carbon;
use Illuminate\Support\Facades\DB;

/*
 * US Apple IDs for the Next.js storefront. Two kinds, as on the old site (v2\OrderController::SubmitAppleId):
 *  - ready (order type 3): taken from the stock in `apple_id` (status 0) the moment the order is paid;
 *  - custom (order type 4): made by support with the buyer's name, email and birthday (admin › orders).
 * Prices are the ones set in admin › Apple ID (Pages #3). Paying reuses Payment\WalletController and the bank
 * callback (Payment\CallBackController), so delivery, profit and the Telegram notice stay in one place.
 */
class AppleIds
{
    public const READY = 3;
    public const CUSTOM = 4;
    public const MAX_QTY = 10;              // per order, as on the old page
    public const PACKS = [30, 50, 100, 200]; // partner packs, same price per Apple ID

    /** @return array{ready:int, custom:int} Toman */
    public function prices(): array
    {
        $Detail = json_decode((string)optional(Pages::find(3))->detail, true) ?: [];
        return ['ready' => (int)($Detail['normal_price'] ?? 0), 'custom' => (int)($Detail['custom_price'] ?? 0)];
    }

    /* Ready Apple IDs not sold yet. */
    public function stock(): int
    {
        return AppleId::where('status', 0)->count();
    }

    public function validQuantity(int $Qty): bool
    {
        return ($Qty >= 1 && $Qty <= self::MAX_QTY) || in_array($Qty, self::PACKS, true);
    }

    /*
     * An unpaid order with its item and payment, shaped exactly like the old site's (so admin, the user panel and
     * the payment code read it as before). `$Details` is the custom form; null for ready.
     * @return array{0: Order, 1: Payment}
     */
    public function createOrder($User, int $Type, int $Qty, int $Price, ?array $Details): array
    {
        return DB::transaction(function () use ($User, $Type, $Qty, $Price, $Details) {
            $Order = new Order();
            $Order->type = $Type;
            $Order->status = 'waiting to pay';
            $Order->pay_status = 'waiting to pay';
            $Order->price = $Price;
            $Order->user_id = $User->id;
            $Order->expire_date = Carbon::now()->addMinutes(60);
            $Order->save();

            $Item = new OrderItem();
            $Item->order_id = $Order->id;
            $Item->product_id = null;
            $Item->user_id = null;
            $Item->type = 3; // both kinds use item type 3 on the old site
            $Item->price = $Price;
            $Item->detail = $Type === self::READY ? (string)$Qty : json_encode($Details, JSON_UNESCAPED_UNICODE);
            $Item->save();

            $Payment = new Payment();
            $Payment->order_id = $Order->id;
            $Payment->user_id = $User->id;
            $Payment->type = 1;
            $Payment->status = 'waiting to pay';
            $Payment->gate = 'Bank';
            $Payment->creator = 'System';
            $Payment->price = $Price;
            $Payment->save();

            $Order->pay_id = $Payment->id;
            $Order->save();

            return [$Order, $Payment];
        });
    }

    /*
     * The logins delivered for an order so far (a ready order short of stock gets the rest when admin restocks;
     * a custom one gets its single login when support finishes it).
     * @return array<int, array{apple_id:string, password:string, birthday:string, answers:string[]}>
     */
    public function accounts(Order $Order): array
    {
        return AppleId::where('order_id', $Order->id)->where('status', 1)->orderBy('id')->get()->map(function ($Row) {
            $Data = json_decode((string)PhpEncrypt::decrypt($Row->serial, $Row->code), true) ?: [];
            return [
                'apple_id' => (string)($Data['appleid'] ?? $Row->apple_id),
                'password' => (string)($Data['password'] ?? ''),
                'birthday' => (string)($Data['brithday'] ?? ''),
                'answers' => array_values(array_filter([(string)($Data['q1'] ?? ''), (string)($Data['q2'] ?? ''), (string)($Data['q3'] ?? '')], 'strlen')),
            ];
        })->all();
    }
}
