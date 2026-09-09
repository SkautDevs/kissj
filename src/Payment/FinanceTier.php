<?php

declare(strict_types=1);

namespace kissj\Payment;

readonly class FinanceTier implements FinanceTierInterface
{
    public function __construct(
        public int $price,
    ) {
    }

    public function matches(Payment $payment): bool
    {
        return (float)$this->price === (float)$payment->price;
    }

    public function isUnknown(): bool
    {
        return false;
    }
}
