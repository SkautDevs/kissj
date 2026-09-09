<?php

declare(strict_types=1);

namespace kissj\Payment;

readonly class FinanceTierUnknown implements FinanceTierInterface
{
    public function matches(Payment $payment): bool
    {
        return false;
    }

    public function isUnknown(): bool
    {
        return true;
    }
}
