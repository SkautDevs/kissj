<?php

declare(strict_types=1);

namespace kissj\Participant\Admin;

readonly class StatisticUserValueObject
{
    public function __construct(
        public int $openCount,
        public int $closedCount,
        public int $approvedCount,
        public int $paidCount,
    ) {
    }
}
