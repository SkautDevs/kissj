<?php

declare(strict_types=1);

namespace kissj\Participant\Admin;

class StatisticUserValueObject
{
    public function __construct(
        public readonly int $openCount,
        public readonly int $closedCount,
        public readonly int $approvedCount,
        public readonly int $paidCount,
    ) {
    }
}
