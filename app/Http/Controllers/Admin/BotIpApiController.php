<?php

namespace App\Http\Controllers\Admin;

use App\Models\HegzaCountry;
use App\Models\HegzaIp;
use App\Models\HegzaProduct;
use App\Models\HegzaUser;

/**
 * The Telegram bot's static IPs: the same screens as the shop's (IpApiController), on the bot's own tables
 * (hegza_country, hegza_products, hegza_ip). The bot hands out free clients itself, so nothing is delivered from here.
 * Client status as the bot writes it: 0 free, 1 sold and running, -2 suspended (not renewed), -1 ended,
 * -4 found on the panel but not made here.
 */
class BotIpApiController extends IpApiController
{
    protected string $locations = HegzaCountry::class;
    protected string $plans = HegzaProduct::class;
    protected string $ips = HegzaIp::class;
    protected array $clientStatus = ['0' => 'free', '1' => 'active', '-2' => 'suspended', '-1' => 'ended', '-4' => 'imported'];
    protected ?int $orderType = null;
    protected bool $webPage = false;

    /** Bot buyers are Telegram users: the name is their username, the "mobile" their Telegram id. */
    protected function buyers(array $ids): array
    {
        return HegzaUser::whereIn('id', $ids)->get(['id', 'username', 'user_id'])
            ->mapWithKeys(fn ($u) => [$u->id => ['id' => $u->id, 'name' => $u->username ? '@' . $u->username : null, 'mobile' => (string) $u->user_id]])->all();
    }
}
