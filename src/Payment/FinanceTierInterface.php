<?php

declare(strict_types=1);

namespace kissj\Payment;

interface FinanceTierInterface
{
    public function matches(Payment $payment): bool;

    public function isUnknown(): bool;
}
