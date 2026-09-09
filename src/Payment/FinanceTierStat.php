<?php

declare(strict_types=1);

namespace kissj\Payment;

class FinanceTierStat
{
    public int $paidCount = 0;
    public int $waitingCount = 0;

    public function __construct(
        public readonly FinanceTierInterface $tier,
    ) {
    }
}
