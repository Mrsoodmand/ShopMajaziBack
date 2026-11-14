<?php

namespace App\Services\Payments;

final class VerifyResult
{
    public function __construct(
        public readonly bool $ok,                    // paid and confirmed now
        public readonly bool $alreadyVerified = false, // paid, confirmed by an earlier callback
        public readonly ?string $refId = null,
        public readonly ?string $card = null,        // masked card number
        public readonly ?int $fee = null,
        public readonly ?string $code = null,
        public readonly ?string $message = null,
    ) {
    }

    /** The shape the fulfillment code (Payment\CallBackController) reads: Zarinpal v4's verify answer. */
    public function legacyResponse(): array
    {
        return ['data' => ['code' => $this->ok ? 100 : 101, 'ref_id' => $this->refId, 'card_pan' => $this->card, 'fee' => $this->fee]];
    }
}
