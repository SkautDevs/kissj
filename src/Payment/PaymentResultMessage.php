<?php

declare(strict_types=1);

namespace kissj\Payment;

readonly class PaymentResultMessage
{
    /**
     * @param array<string, string> $translationParams
     */
    public function __construct(
        public PaymentMessageSeverity $severity,
        public string $translationKey,
        public array $translationParams = [],
    ) {
    }
}
