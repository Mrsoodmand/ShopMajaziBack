<?php

namespace App\Services\Payments;

use Illuminate\Http\Request;

/**
 * One bank gateway (Zarinpal, Zibal …). GatewayManager picks which one a payment uses; amounts are in Rial.
 * A new gateway is a class implementing this, registered in GatewayManager::GATEWAYS.
 */
interface Gateway
{
    /** Registers a payment session: the address to send the buyer to, or the reason it failed. */
    public function start(string $merchant, int $rial, string $callbackUrl, string $description, array $extra): StartResult;

    /** The gateway's reference for this payment in the callback request (Authority, trackId …), or null if not ours. */
    public function referenceFrom(Request $request): ?string;

    /** Whether the callback says the buyer paid (before verifying). */
    public function callbackPaid(Request $request): bool;

    /** Confirms the payment with the gateway, checking the amount. */
    public function verify(string $merchant, string $reference, int $rial): VerifyResult;
}
