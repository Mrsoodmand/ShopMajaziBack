<?php

namespace App\Services\Payments;

final class StartResult
{
    public function __construct(
        public readonly bool $ok,
        public readonly ?string $url = null,       // where to send the buyer
        public readonly ?string $reference = null, // stored on the payment (payments.authority)
        public readonly ?string $error = null,
    ) {
    }
}
